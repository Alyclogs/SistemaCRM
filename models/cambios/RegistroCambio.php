<?php
require_once __DIR__ . "/../../config/database.php";

class RegistroCambioModel
{
    private $pdo;

    public function __construct()
    {
        try {
            $this->pdo = connectDatabase();
        } catch (PDOException $e) {
            die("Error al conectar en RegistroCambioModel: " . $e->getMessage());
        }
    }

    /**
     * Registrar un cambio específico
     */
    public function registrarCambio($idusuario, $idreferencia, $tipo, $accion, $campo = null, $anterior = null, $nuevo = null, $descripcion = null)
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
                $descripcion ?? $this->generarDescripcion($tipo, $accion, $campo, $anterior, $nuevo)
            ]);
        } catch (Exception $e) {
            throw new Exception("Error al registrar cambio: " . $e->getMessage());
        }
    }

    /**
     * Registrar automáticamente los cambios entre datos antiguos y nuevos
     */
    public function registrarCambiosAutomaticos($idusuario, $idreferencia, $tipo, $accion, $datosAnteriores, $datosNuevos)
    {
        foreach ($datosNuevos as $campo => $valorNuevo) {
            $valorAnterior = $datosAnteriores[$campo] ?? null;

            if (!$valorAnterior || $valorAnterior == $valorNuevo) {
                continue;
            }

            if (is_array($valorAnterior)) {
                $valorAnterior = json_encode($valorAnterior);
            }

            if (is_array($valorNuevo)) {
                $valorNuevo = json_encode($valorNuevo);
            }

            $descripcion = $this->generarDescripcion($tipo, $accion, $campo, $valorAnterior, $valorNuevo);

            $this->registrarCambio(
                $idusuario,
                $idreferencia,
                $tipo,
                $accion,
                $campo,
                $valorAnterior,
                $valorNuevo,
                $descripcion
            );
        }
    }

    /**
     * Genera una descripción genérica según la acción
     */
    private function generarDescripcion($tipo, $accion, $fecha, $campo = null, $anterior = null, $nuevo = null)
    {
        $tipo = ucfirst($tipo);
        $tipoAux = "";

        if ($tipo === "Cliente" || $tipo === "Proyecto" || $tipo === "Whatsapp" || $tipo === "Correo" || $tipo === "Archivo") {
            $tipoAux = "creado";
        }
        if ($tipo === "Actividad" ||  $tipo === "Nota" || $tipo === "Tarea") {
            $tipoAux = "creada";
        }

        switch ($accion) {
            case "creacion":
                return "{$tipo} {$tipoAux}: {$fecha}";
            case "eliminacion":
                return "{$tipo} eliminado: " . ($anterior ?? "Sin datos");
            case "actualizacion":
                return "{$campo}: {$anterior} → " . ($nuevo ?? 'Sin datos');
            default:
                return "{$tipo} modificado";
        }
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
