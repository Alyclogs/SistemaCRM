<?php
require_once __DIR__ . "/../clientes/ClienteModel.php";
require_once __DIR__ . "/../actividades/ActividadModel.php";
require_once __DIR__ . "/../proyectos/ProyectoModel.php";

class RegistroCambioModel
{
    private $pdo;
    private $clienteModel;
    private $actividadModel;
    private $proyectoModel;

    public function __construct($pdo, $clienteModel = null, $actividadModel = null, $proyectoModel = null)
    {
        $this->pdo = $pdo;
        $this->clienteModel   = $clienteModel   ?: new ClienteModel($pdo, $this);
        $this->actividadModel = $actividadModel ?: new ActividadModel($pdo, $this);
        $this->proyectoModel  = $proyectoModel  ?: new ProyectoModel($pdo, $this);
    }

    /**
     * Registrar un cambio específico
     */
    public function registrarCambio($idusuario, $idreferencia, $tipo, $accion, $campo = null, $anterior = null, $nuevo = null, $descripcion = null, $contexto = null)
    {
        try {
            if (is_array($anterior)) {
                $anterior = json_encode($anterior);
            }
            if (is_array($nuevo)) {
                $nuevo = json_encode($nuevo);
            }

            $sql = "INSERT INTO registro_cambios (idusuario, idreferencia, tipo, accion, campo, anterior, nuevo, descripcion, fecha) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                $idusuario,
                $idreferencia,
                $tipo,
                $accion,
                $campo,
                $anterior,
                $nuevo,
                $descripcion ?? $this->generarDescripcion($tipo, $accion, $campo, $anterior, $nuevo, $contexto)
            ]);
        } catch (Exception $e) {
            throw new Exception("Error al registrar cambio: " . $e->getMessage());
        }
    }

    /**
     * Registrar automáticamente los cambios entre datos antiguos y nuevos
     */
    public function registrarCambiosAutomaticos(
        $idusuario,
        $idreferencia,
        $tipo,
        $accion,
        $datosAnteriores,
        $datosNuevos,
        $contexto = null
    ) {
        // 1. Obtener contexto automáticamente si es creación o eliminación
        if (empty($contexto)) {
            $contexto = $this->resolverContextoEntidad($tipo, $idreferencia);
        }

        $normalizarHora = function ($hora) {
            if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $hora)) {
                return substr($hora, 0, 5); // "11:28:00" -> "11:28"
            }
            return $hora;
        };

        foreach ($datosNuevos as $campo => $valorNuevo) {
            $valorAnterior = $datosAnteriores[$campo] ?? null;

            if (in_array($campo, ['clientes', 'empresas', 'proyectos'])) {
                continue; // ahora se maneja exclusivamente con registrarAsignaciones()
            }

            if (in_array($campo, ['idempresa', 'idcliente'])) {
                continue; // se registran solo en registrarAsignaciones()
            }

            if (in_array($campo, ['hora_inicio', 'hora_fin', 'hora'])) {
                $valorAnterior = $normalizarHora($valorAnterior);
                $valorNuevo    = $normalizarHora($valorNuevo);
            }

            // 1. Manejar campo extra (JSON)
            if ($campo === 'extra' && is_array($valorAnterior) && is_array($valorNuevo)) {
                foreach ($valorNuevo as $subCampo => $nuevoValor) {
                    $viejoValor = $valorAnterior[$subCampo] ?? null;
                    if ($viejoValor != $nuevoValor) {
                        $descripcion = $this->generarDescripcion(
                            $tipo,
                            $accion,
                            $subCampo,
                            $viejoValor,
                            $nuevoValor,
                            $contexto
                        );
                        $this->registrarCambio(
                            $idusuario,
                            $idreferencia,
                            $tipo,
                            $accion,
                            $subCampo,
                            $viejoValor,
                            $nuevoValor,
                            $descripcion,
                            $contexto
                        );
                    }
                }
                continue;
            }

            // 2. Ignorar arrays complejos
            if (is_array($valorNuevo) || is_array($valorAnterior)) {
                continue;
            }

            // 3. Manejo de cambios normales
            if ($accion === 'actualizacion' && $valorAnterior == $valorNuevo) {
                continue;
            }

            $descripcion = $this->generarDescripcion(
                $tipo,
                $accion,
                $campo,
                $valorAnterior,
                $valorNuevo,
                $contexto
            );

            $this->registrarCambio(
                $idusuario,
                $idreferencia,
                $tipo,
                $accion,
                $campo,
                $valorAnterior,
                $valorNuevo,
                $descripcion,
                $contexto
            );
        }
    }

    public function registrarAsignaciones(
        $idusuario,
        $idreferencia,
        $tipo,
        $campoRelacion,
        $anteriores,
        $nuevos
    ) {
        $anterioresIds = array_column($anteriores ?? [], 'idreferencia');
        $nuevosIds     = array_column($nuevos ?? [], 'idreferencia');

        $agregados  = array_diff($nuevosIds, $anterioresIds);
        $eliminados = array_diff($anterioresIds, $nuevosIds);

        foreach ($agregados as $idAsig) {
            $nombre = $this->resolverNombreAsignacion($campoRelacion, $idAsig);
            $nombreRef = $this->resolverNombreAsignacion($tipo, $idreferencia);
            $descripcion = "Asignación de {$nombre} a {$tipo} {$nombreRef}";
            $this->registrarCambio($idusuario, $idreferencia, $tipo, 'asignacion', $campoRelacion, null, $idAsig, $descripcion);
        }

        foreach ($eliminados as $idAsig) {
            $nombre = $this->resolverNombreAsignacion($campoRelacion, $idAsig);
            $nombreRef = $this->resolverNombreAsignacion($tipo, $idreferencia);
            $descripcion = "Eliminación de {$nombre} de {$tipo} {$nombreRef}";
            $this->registrarCambio($idusuario, $idreferencia, $tipo, 'eliminacion', $campoRelacion, $idAsig, null, $descripcion);
        }
    }

    private function resolverContextoEntidad($tipo, $idreferencia)
    {
        // Mapeo explícito de tablas y claves primarias
        $mapa = [
            'actividad' => ['tabla' => 'actividades', 'pk' => 'idactividad'],
            'cliente'   => ['tabla' => 'clientes',    'pk' => 'idcliente'],
            'empresa'   => ['tabla' => 'empresas',    'pk' => 'idempresa'],
            'proyecto'  => ['tabla' => 'proyectos',   'pk' => 'idproyecto'],
            'tarea'     => ['tabla' => 'tareas',      'pk' => 'idtarea']
        ];

        if (!isset($mapa[$tipo])) {
            throw new Exception("No se encontró configuración de contexto para tipo: {$tipo}");
        }

        $tabla = $mapa[$tipo]['tabla'];
        $pk    = $mapa[$tipo]['pk'];

        $sql = "SELECT * FROM {$tabla} WHERE {$pk} = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idreferencia]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Resolver nombre de entidad asignada según tipo de relación
     */
    private function resolverNombreAsignacion($campo, $idreferencia)
    {
        switch ($campo) {
            case 'clientes':
                $cli = $this->clienteModel->obtenerCliente($idreferencia);
                return "cliente {$cli['nombres']} {$cli['apellidos']}";
            case 'empresas':
                $emp = $this->clienteModel->obtenerOrganizacion($idreferencia);
                return "empresa {$emp['razon_social']}";
            case 'proyectos':
                $proy = $this->proyectoModel->obtenerProyecto($idreferencia);
                return "proyecto {$proy['nombre']}";
            case 'actividades':
                $actividad = $this->actividadModel->obtenerActividad($idreferencia);
                return "actividad {$actividad['nombre']}";
            case 'actividad':
                $actividad = $this->actividadModel->obtenerActividad($idreferencia);
                return 'actividad "' . $actividad['nombre'] . '"';
            default:
                return "{$campo} {$idreferencia}";
        }
    }

    /**
     * Genera una descripción genérica según la acción
     */
    private function generarDescripcion($tipo, $accion, $campo, $valorAnterior, $valorNuevo, $contexto = [])
    {
        // Buscar descripción en diccionario_campos
        $sql = "SELECT descripcion FROM diccionario_campos WHERE tabla = ? AND campo = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$tipo . 's', $campo]); // Ojo: pluralizamos las tablas
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $campoLabel = $row ? $row['descripcion'] : ucfirst(str_replace("_", " ", $campo));
        $accionTexto = ucfirst($accion);

        $valorAnterior = ($valorAnterior === null || $valorAnterior === '') ? '(vacío)' : $valorAnterior;
        $valorNuevo    = ($valorNuevo === null || $valorNuevo === '') ? '(vacío)' : $valorNuevo;

        // CREACIÓN
        if ($accion === 'creacion') {
            $auxTipo = ($tipo === 'nota') ? 'creada' : 'creado';

            if ($tipo === 'cliente' && isset($contexto['nombres'], $contexto['apellidos'])) {
                return "Cliente creado: {$contexto['nombres']} {$contexto['apellidos']}.";
            }
            if ($tipo === 'empresa' && isset($contexto['razon_social'])) {
                return "Empresa creada: {$contexto['razon_social']}.";
            }
            if ($tipo === 'actividad' && isset($contexto['nombre'])) {
                return "Actividad creada: {$contexto['nombre']}.";
            }
            $tipo = ucfirst($tipo);
            return "{$tipo} {$auxTipo}: {$valorNuevo}";
        }

        // ELIMINACIÓN
        if ($accion === 'eliminacion') {
            return "{$accionTexto} {$tipo}: {$valorAnterior}";
        }

        // ACTUALIZACIÓN con contexto
        if ($accion === 'actualizacion') {
            if ($tipo === 'actividad' && isset($contexto['nombre'])) {
                return "Actualización {$campoLabel} de actividad \"{$contexto['nombre']}\": {$valorAnterior} -> {$valorNuevo}";
            }
            if ($tipo === 'cliente' && isset($contexto['nombres'], $contexto['apellidos'])) {
                return "Actualización {$campoLabel} de cliente {$contexto['nombres']} {$contexto['apellidos']}: {$valorAnterior} -> {$valorNuevo}";
            }
            if ($tipo === 'empresa' && isset($contexto['razon_social'])) {
                return "Actualización {$campoLabel} de empresa {$contexto['razon_social']}: {$valorAnterior} -> {$valorNuevo}";
            }
        }

        // GENÉRICO
        return "{$accionTexto} {$campoLabel} de {$tipo}: {$valorAnterior} -> {$valorNuevo}";
    }

    /**
     * Obtener historial por referencia
     */
    public function obtenerHistorial($idreferencia, $tipo)
    {
        try {
            $sql = "SELECT rc.*, CONCAT(u.nombres, ' ', u.apellidos) AS usuario 
                    FROM registro_cambios rc
                    INNER JOIN usuarios u ON u.idusuario = rc.idusuario
                    WHERE rc.idreferencia = ? AND rc.tipo = ?
                    ORDER BY rc.fecha DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$idreferencia, $tipo]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error al obtener historial de cambios: " . $e->getMessage());
        }
    }

    public function obtenerRegistroCambiosPorCliente($idreferencia, $tipoCliente)
    {
        try {
            $sql = "
            -- CLIENTES
            SELECT rc.*, CONCAT(u.nombres, ' ', u.apellidos) AS usuario,
                CONCAT(c.nombres, ' ', c.apellidos) AS nombre
            FROM registro_cambios rc
            INNER JOIN usuarios u ON u.idusuario = rc.idusuario
            LEFT JOIN clientes c ON rc.idreferencia = c.idcliente AND rc.tipo = 'cliente'
            WHERE rc.idreferencia = ? AND rc.tipo = ?

            UNION ALL

            -- EMPRESAS RELACIONADAS
            SELECT rc.*, CONCAT(u.nombres, ' ', u.apellidos) AS usuario,
                CONCAT(c.nombres, ' ', c.apellidos) AS nombre
            FROM registro_cambios rc
            INNER JOIN usuarios u ON u.idusuario = rc.idusuario
            LEFT JOIN clientes c ON rc.idreferencia = c.idcliente AND rc.tipo = 'empresa'
            LEFT JOIN empresas_clientes ec ON ec.idcliente = c.idcliente
            LEFT JOIN empresas e ON ec.idempresa = e.idempresa
            WHERE ec.idcliente = ? AND rc.tipo = ?

            UNION ALL

            -- ACTIVIDADES
            SELECT rc.*, CONCAT(u.nombres, ' ', u.apellidos) AS usuario,
            CASE 
                WHEN ac.tipo_cliente = 'cliente' THEN CONCAT(c.nombres, ' ', c.apellidos)
                WHEN ac.tipo_cliente = 'empresa' THEN e.razon_social
            END AS nombre
            FROM registro_cambios rc
            INNER JOIN usuarios u ON u.idusuario = rc.idusuario
            INNER JOIN actividades_clientes ac ON rc.idreferencia = ac.idactividad AND rc.tipo = 'actividad'
            LEFT JOIN clientes c ON ac.idreferencia = c.idcliente AND ac.tipo_cliente = 'cliente'
            LEFT JOIN empresas e ON ac.idreferencia = e.idempresa AND ac.tipo_cliente = 'empresa'
            WHERE ac.idreferencia = ? AND ac.tipo_cliente = ?
            
            UNION ALL
            
            -- PROYECTOS
            SELECT rc.*, CONCAT(u.nombres, ' ', u.apellidos) AS usuario,
            CASE 
                WHEN pc.tipo_cliente = 'cliente' THEN CONCAT(c.nombres, ' ', c.apellidos)
                WHEN pc.tipo_cliente = 'empresa' THEN e.razon_social
            END AS nombre
            FROM registro_cambios rc
            INNER JOIN usuarios u ON u.idusuario = rc.idusuario
            INNER JOIN clientes_proyectos pc ON rc.idreferencia = pc.idproyecto AND rc.tipo = 'proyecto'
            LEFT JOIN clientes c ON pc.idreferencia = c.idcliente AND pc.tipo_cliente = 'cliente'
            LEFT JOIN empresas e ON pc.idreferencia = e.idempresa AND pc.tipo_cliente = 'empresa'
            WHERE pc.idreferencia = ? AND pc.tipo_cliente = ?
            
            UNION ALL
            
            -- NOTAS
            SELECT rc.*, CONCAT(u.nombres, ' ', u.apellidos) AS usuario,
            CASE 
                WHEN n.tipo = 'cliente' THEN CONCAT(c.nombres, ' ', c.apellidos)
                WHEN n.tipo = 'empresa' THEN e.razon_social
            END AS nombre
            FROM registro_cambios rc
            INNER JOIN usuarios u ON u.idusuario = rc.idusuario
            INNER JOIN notas n ON rc.idreferencia = n.idnota AND rc.tipo = 'nota'
            LEFT JOIN clientes c ON n.idreferencia = c.idcliente AND n.tipo = 'cliente'
            LEFT JOIN empresas e ON n.idreferencia = e.idempresa AND n.tipo = 'empresa'
            WHERE n.idreferencia = ? AND n.tipo = ?
            
            UNION ALL
            
            -- WHATSAPP
            SELECT rc.*, CONCAT(u.nombres, ' ', u.apellidos) AS usuario,
            CASE 
                WHEN aw.tipo_cliente = 'cliente' THEN CONCAT(c.nombres, ' ', c.apellidos)
                WHEN aw.tipo_cliente = 'empresa' THEN e.razon_social
            END AS nombre
            FROM registro_cambios rc
            INNER JOIN usuarios u ON u.idusuario = rc.idusuario
            INNER JOIN act_whatsapp aw ON rc.idreferencia = aw.idwhatsapp AND rc.tipo = 'whatsapp'
            LEFT JOIN clientes c ON aw.idreferencia = c.idcliente AND aw.tipo_cliente = 'cliente'
            LEFT JOIN empresas e ON aw.idreferencia = e.idempresa AND aw.tipo_cliente = 'empresa'
            WHERE aw.idreferencia = ? AND aw.tipo_cliente = ?

            UNION ALL
            
            -- CORREOS
            SELECT rc.*, CONCAT(u.nombres, ' ', u.apellidos) AS usuario,
            CASE 
                WHEN crr.tipo_cliente = 'cliente' THEN CONCAT(c.nombres, ' ', c.apellidos)
                WHEN crr.tipo_cliente = 'empresa' THEN e.razon_social
            END AS nombre
            FROM registro_cambios rc
            INNER JOIN usuarios u ON u.idusuario = rc.idusuario
            INNER JOIN act_correos crr ON rc.idreferencia = crr.idcorreo AND rc.tipo = 'correo'
            LEFT JOIN clientes c ON crr.idreferencia = c.idcliente AND crr.tipo_cliente = 'cliente'
            LEFT JOIN empresas e ON crr.idreferencia = e.idempresa AND crr.tipo_cliente = 'empresa'
            WHERE crr.idreferencia = ? AND crr.tipo_cliente = ?

            UNION ALL
            
            -- ARCHIVOS
            SELECT rc.*, CONCAT(u.nombres, ' ', u.apellidos) AS usuario,
            CASE 
                WHEN a.tipo_cliente = 'cliente' THEN CONCAT(c.nombres, ' ', c.apellidos)
                WHEN a.tipo_cliente = 'empresa' THEN e.razon_social
            END AS nombre
            FROM registro_cambios rc
            INNER JOIN usuarios u ON u.idusuario = rc.idusuario
            INNER JOIN act_archivos a ON rc.idreferencia = a.idarchivo AND rc.tipo = 'archivo'
            LEFT JOIN clientes c ON a.idreferencia = c.idcliente AND a.tipo_cliente = 'cliente'
            LEFT JOIN empresas e ON a.idreferencia = e.idempresa AND a.tipo_cliente = 'empresa'
            WHERE a.idreferencia = ? AND a.tipo_cliente = ?
            
            ORDER BY fecha DESC
        ";

            $params = [
                $idreferencia,
                $tipoCliente, // clientes
                $idreferencia,
                $tipoCliente, // empresas
                $idreferencia,
                $tipoCliente, // actividades
                $idreferencia,
                $tipoCliente, // proyectos
                $idreferencia,
                $tipoCliente,  // notas
                $idreferencia,
                $tipoCliente,  // whatsapp
                $idreferencia,
                $tipoCliente,  // correos
                $idreferencia,
                $tipoCliente  // archivos
            ];

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error al obtener historial del cliente/empresa: " . $e->getMessage());
        }
    }
}
