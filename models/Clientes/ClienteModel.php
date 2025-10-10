<?php
require_once __DIR__ . "/../cambios/RegistroCambio.php";
require_once __DIR__ . "/../actividades/ActividadModel.php";
require_once __DIR__ . '/../ajustes/AjustesModel.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class ClienteModel
{
    private $pdo;
    private $registroCambioModel;

    public function __construct($pdo, $registroCambioModel = null)
    {
        $this->pdo = $pdo;
        $this->registroCambioModel = $registroCambioModel  ?: new RegistroCambioModel($this->pdo);
    }

    public function obtenerClientes($idusuario = null)
    {
        try {
            $sql = '';

            $sql = "SELECT c.*,
                    ec.estado,
                    e.idempresa,
                    e.razon_social AS empresa_nombre,
                    e.ruc AS empresa_ruc,
                    e.foto AS empresa_foto,
                    CONCAT(u.nombres, ' ', u.apellidos) AS usuario,
                    u.foto AS usuario_foto
                FROM clientes c
                INNER JOIN usuarios u ON c.idusuario = u.idusuario
                LEFT JOIN estados_clientes ec ON c.idestado = ec.idestado
                LEFT JOIN empresas_clientes emc ON c.idcliente = emc.idcliente
                LEFT JOIN empresas e ON e.idempresa = emc.idempresa";

            $params = [];

            if (!empty($idusuario)) {
                $sql .= " WHERE c.idusuario = ?";
                $params[] = $idusuario;
            }

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($clientes as &$cliente) {
                $cliente['proyectos'] = $this->obtenerProyectosPorCliente($cliente['idcliente'], "cliente");
                if (!empty($cliente['extra'])) {
                    $cliente['extra'] = json_decode($cliente['extra'], true);
                }
            }

            return $clientes;
        } catch (Exception $e) {
            throw new Exception("Error al obtener clientes: " . $e->getMessage());
        }
    }

    public function buscarClientes($filtro, $idusuario = null)
    {
        try {
            $sql = "SELECT c.*,
                    ec.estado,
                    e.idempresa,
                    e.razon_social AS empresa_nombre,
                    e.ruc AS empresa_ruc,
                    e.foto AS empresa_foto,
                    CONCAT(u.nombres, ' ', u.apellidos) AS usuario,
                    u.foto AS usuario_foto
                FROM clientes c
                INNER JOIN usuarios u ON c.idusuario = u.idusuario
                LEFT JOIN estados_clientes ec ON c.idestado = ec.idestado
                LEFT JOIN empresas_clientes emc ON c.idcliente = emc.idcliente
                LEFT JOIN empresas e ON e.idempresa = emc.idempresa
                WHERE (c.nombres LIKE ? OR c.apellidos LIKE ? OR c.num_doc LIKE ?)";

            $params = ["%$filtro%", "%$filtro%", "%$filtro%"];

            if (!empty($idusuario)) {
                $sql .= " AND c.idusuario = ?";
                $params[] = $idusuario;
            }

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($clientes as &$cliente) {
                $cliente['proyectos'] = $this->obtenerProyectosPorCliente($cliente['idcliente'], "cliente");
                if (!empty($cliente['extra'])) {
                    $cliente['extra'] = json_decode($cliente['extra'], true);
                }
            }

            return $clientes;
        } catch (Exception $e) {
            throw new Exception("Error al buscar clientes: " . $e->getMessage());
        }
    }

    public function obtenerEstadosClientes()
    {
        try {
            $sql = "SELECT * FROM estados_clientes";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error al obtener estados de clientes: " . $e->getMessage());
        }
    }

    public function obtenerClientesPorEstado($idestado)
    {
        try {
            $sql = "SELECT c.*,
                    ec.estado,
                    e.idempresa,
                    e.razon_social AS empresa_nombre,
                    e.ruc AS empresa_ruc,
                    e.foto AS empresa_foto,
                    CONCAT(u.nombres, ' ', u.apellidos) AS usuario,
                    u.foto AS usuario_foto
            FROM clientes c
            INNER JOIN usuarios u ON c.idusuario = u.idusuario
            LEFT JOIN estados_clientes ec ON c.idestado = ec.idestado
            LEFT JOIN empresas_clientes emc ON c.idcliente = emc.idcliente
            LEFT JOIN empresas e ON e.idempresa = emc.idempresa
            WHERE c.idestado = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$idestado]);
            $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($clientes as &$cliente) {
                $cliente['proyectos'] = $this->obtenerProyectosPorCliente($cliente['idcliente'], "cliente");
                if (!empty($cliente['extra'])) {
                    $cliente['extra'] = json_decode($cliente['extra'], true);
                }
            }

            return $clientes;
        } catch (Exception $e) {
            throw new Exception("Error al buscar clientes: " . $e->getMessage());
        }
    }

    public function obtenerCliente($id)
    {
        try {
            $sql = "SELECT c.*,
                    ec.estado,
                    e.idempresa,
                    e.razon_social AS empresa_nombre,
                    e.ruc AS empresa_ruc,
                    e.foto AS empresa_foto,
                    CONCAT(u.nombres, ' ', u.apellidos) AS usuario,
                    u.foto AS usuario_foto
            FROM clientes c
            INNER JOIN usuarios u ON c.idusuario = u.idusuario
            LEFT JOIN estados_clientes ec ON c.idestado = ec.idestado
            LEFT JOIN empresas_clientes emc ON c.idcliente = emc.idcliente
            LEFT JOIN empresas e ON e.idempresa = emc.idempresa
            WHERE c.idcliente = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
            $cliente['proyectos'] = $this->obtenerProyectosPorCliente($cliente['idcliente'], "cliente");
            if (!empty($cliente['extra'])) {
                $cliente['extra'] = json_decode($cliente['extra'], true);
            }

            return $cliente;
        } catch (Exception $e) {
            throw new Exception("Error al obtener cliente: " . $e->getMessage());
        }
    }

    public function obtenerOrganizaciones()
    {
        try {
            $sql = "SELECT e.*,
            CONCAT(u.nombres, ' ', u.apellidos) AS usuario,
            u.foto AS usuario_foto
            FROM empresas e INNER JOIN usuarios u ON e.idusuario = u.idusuario";

            $stmt = $this->pdo->query($sql);
            $organizaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $organizaciones;
        } catch (Exception $e) {
            throw new Exception("Error al buscar organizaciones: " . $e->getMessage());
        }
    }

    public function buscarOrganizaciones($filtro)
    {
        try {
            $sql = "SELECT e.*,
                        CONCAT(u.nombres, ' ', u.apellidos) AS usuario,
                        u.foto AS usuario_foto
                        FROM empresas e
                    INNER JOIN usuarios u ON e.idusuario = u.idusuario
                    WHERE (razon_social LIKE ? OR ruc LIKE ?)";

            $params = ["%$filtro%", "%$filtro%"];

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $organizaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $organizaciones;
        } catch (Exception $e) {
            throw new Exception("Error al buscar organizaciones: " . $e->getMessage());
        }
    }

    public function obtenerOrganizacion($id)
    {
        try {
            $sql = "SELECT e.*,
                        CONCAT(u.nombres, ' ', u.apellidos) AS usuario,
                        u.foto AS usuario_foto
                    FROM empresas e
                    INNER JOIN usuarios u ON e.idusuario = u.idusuario
                    WHERE e.idempresa = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

            return $cliente;
        } catch (Exception $e) {
            throw new Exception("Error al obtener organización: " . $e->getMessage());
        }
    }

    public function buscarClientesYOrganizaciones($filtro, $idusuario = null)
    {
        try {
            $clientes = $this->buscarClientes($filtro, $idusuario);
            $organizaciones = $this->buscarOrganizaciones($filtro);
            $resultados = array_merge($clientes, $organizaciones);

            return $resultados;
        } catch (Exception $e) {
            throw new Exception("Error al buscar clientes y organizaciones: " . $e->getMessage());
        }
    }

    public function obtenerHistorial($idreferencia = null, $tipoCliente = null)
    {
        try {
            return [
                "actividades" => $this->obtenerHistorialActividades($idreferencia, $tipoCliente),
                "notas"       => $this->obtenerHistorialNotas($idreferencia, $tipoCliente),
                "correos"     => $this->obtenerHistorialCorreos($idreferencia, $tipoCliente),
                "whatsapp"    => $this->obtenerHistorialWhatsapp($idreferencia, $tipoCliente),
                "archivos"    => $this->obtenerHistorialArchivos($idreferencia, $tipoCliente),
                "proyectos"   => $this->obtenerHistorialProyectos($idreferencia, $tipoCliente),
                "cambios"     => $this->registroCambioModel->obtenerRegistroCambiosPorCliente($idreferencia, $tipoCliente)
            ];
        } catch (Exception $e) {
            throw new Exception("Error al obtener historial: " . $e->getMessage());
        }
    }

    private function obtenerHistorialActividades($idreferencia = null, $tipoCliente = null)
    {
        $sql = "SELECT a.*, 
                   CONCAT(u.nombres, ' ', u.apellidos) AS usuario,
                   CASE 
                       WHEN acc.tipo_cliente = 'cliente' THEN CONCAT(c.nombres, ' ', c.apellidos)
                       WHEN acc.tipo_cliente = 'empresa' THEN e.razon_social
                       ELSE NULL
                   END AS cliente,
                   acc.tipo_cliente,
                   ea.estado
            FROM actividades a
            INNER JOIN usuarios u ON u.idusuario = a.idusuario
            INNER JOIN estados_actividades ea ON a.idestado = ea.idestado
            LEFT JOIN actividades_clientes acc ON a.idactividad = acc.idactividad
            LEFT JOIN clientes c ON acc.idreferencia = c.idcliente AND acc.tipo_cliente = 'cliente'
            LEFT JOIN empresas e ON acc.idreferencia = e.idempresa AND acc.tipo_cliente = 'empresa'
            WHERE 1=1";

        $params = [];

        if ($idreferencia !== null) {
            $sql .= " AND acc.idreferencia = ?";
            $params[] = $idreferencia;
        }

        if ($tipoCliente !== null) {
            $sql .= " AND acc.tipo_cliente = ?";
            $params[] = $tipoCliente;
        }

        $sql .= " ORDER BY a.fecha_creacion DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function obtenerHistorialNotas($idreferencia = null, $tipoCliente = null)
    {
        $sql = "SELECT n.*,
                   CONCAT(u.nombres, ' ', u.apellidos) AS usuario,
                   CASE 
                       WHEN c.idcliente IS NOT NULL THEN CONCAT(c.nombres, ' ', c.apellidos)
                       WHEN e.idempresa IS NOT NULL THEN e.razon_social
                       ELSE NULL
                   END AS cliente
            FROM notas n
            INNER JOIN usuarios u ON u.idusuario = n.idusuario
            LEFT JOIN clientes c ON n.idreferencia = c.idcliente AND n.tipo = 'cliente'
            LEFT JOIN empresas e ON n.idreferencia = e.idempresa AND n.tipo = 'empresa'
            WHERE 1=1";
        $params = [];
        if ($idreferencia !== null) {
            $sql .= " AND n.idreferencia = ?";
            $params[] = $idreferencia;
        }
        if ($tipoCliente !== null) {
            $sql .= " AND n.tipo = ?";
            $params[] = $tipoCliente;
        }
        $sql .= " ORDER BY n.fecha_creacion DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function obtenerHistorialCorreos($idreferencia = null, $tipoCliente = null)
    {
        $sql = "SELECT c.*,
                   CONCAT(u.nombres, ' ', u.apellidos) AS usuario,
                   CASE 
                       WHEN cli.idcliente IS NOT NULL THEN CONCAT(cli.nombres, ' ', cli.apellidos)
                       WHEN e.idempresa IS NOT NULL THEN e.razon_social
                       ELSE NULL
                   END AS cliente
            FROM act_correos c
            INNER JOIN usuarios u ON u.idusuario = c.idusuario
            LEFT JOIN clientes cli ON c.idreferencia = cli.idcliente AND c.tipo_cliente = 'cliente'
            LEFT JOIN empresas e ON c.idreferencia = e.idempresa AND c.tipo_cliente = 'empresa'
            WHERE 1=1";
        $params = [];
        if ($idreferencia !== null) {
            $sql .= " AND c.idreferencia = ?";
            $params[] = $idreferencia;
        }
        if ($tipoCliente !== null) {
            $sql .= " AND c.tipo_cliente = ?";
            $params[] = $tipoCliente;
        }
        $sql .= " ORDER BY c.fecha_creacion DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function obtenerHistorialWhatsapp($idreferencia = null, $tipoCliente = null)
    {
        $sql = "SELECT w.*,
                   CONCAT(u.nombres, ' ', u.apellidos) AS usuario,
                   CASE 
                       WHEN c.idcliente IS NOT NULL THEN CONCAT(c.nombres, ' ', c.apellidos)
                       WHEN e.idempresa IS NOT NULL THEN e.razon_social
                       ELSE NULL
                   END AS cliente
            FROM act_whatsapp w
            INNER JOIN usuarios u ON u.idusuario = w.idusuario
            LEFT JOIN clientes c ON w.idreferencia = c.idcliente AND w.tipo_cliente = 'cliente'
            LEFT JOIN empresas e ON w.idreferencia = e.idempresa AND w.tipo_cliente = 'empresa'
            WHERE 1=1";
        $params = [];
        if ($idreferencia !== null) {
            $sql .= " AND w.idreferencia = ?";
            $params[] = $idreferencia;
        }
        if ($tipoCliente !== null) {
            $sql .= " AND w.tipo_cliente = ?";
            $params[] = $tipoCliente;
        }
        $sql .= " ORDER BY w.fecha_creacion DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function obtenerHistorialArchivos($idreferencia = null, $tipoCliente = null)
    {
        $sql = "SELECT ar.*,
                   CONCAT(u.nombres, ' ', u.apellidos) AS usuario,
                   CASE 
                       WHEN c.idcliente IS NOT NULL THEN CONCAT(c.nombres, ' ', c.apellidos)
                       WHEN e.idempresa IS NOT NULL THEN e.razon_social
                       ELSE NULL
                   END AS cliente
            FROM act_archivos ar
            INNER JOIN usuarios u ON u.idusuario = ar.idusuario
            LEFT JOIN clientes c ON ar.idreferencia = c.idcliente AND ar.tipo_cliente = 'cliente'
            LEFT JOIN empresas e ON ar.idreferencia = e.idempresa AND ar.tipo_cliente = 'empresa'
            WHERE 1=1";
        $params = [];
        if ($idreferencia !== null) {
            $sql .= " AND ar.idreferencia = ?";
            $params[] = $idreferencia;
        }
        if ($tipoCliente !== null) {
            $sql .= " AND ar.tipo_cliente = ?";
            $params[] = $tipoCliente;
        }
        $sql .= " ORDER BY ar.fecha_creacion DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function obtenerHistorialProyectos($idreferencia = null, $tipoCliente = null)
    {
        $sql = "SELECT p.*,
                   CONCAT(u.nombres, ' ', u.apellidos) AS usuario,
                   CASE 
                       WHEN c.idcliente IS NOT NULL THEN CONCAT(c.nombres, ' ', c.apellidos)
                       WHEN e.idempresa IS NOT NULL THEN e.razon_social
                       ELSE NULL
                   END AS cliente
            FROM proyectos p
            INNER JOIN usuarios u ON u.idusuario = p.idusuario
            LEFT JOIN clientes_proyectos cp ON p.idproyecto = cp.idproyecto
            LEFT JOIN clientes c ON cp.idreferencia = c.idcliente AND cp.tipo_cliente = 'cliente'
            LEFT JOIN empresas e ON cp.idreferencia = e.idempresa AND cp.tipo_cliente = 'empresa'
            WHERE 1=1";
        $params = [];
        if ($idreferencia !== null) {
            $sql .= " AND cp.idreferencia = ?";
            $params[] = $idreferencia;
        }
        if ($tipoCliente !== null) {
            $sql .= " AND cp.tipo_cliente = ?";
            $params[] = $tipoCliente;
        }
        $sql .= " ORDER BY p.fecha_creacion DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crearCliente($data)
    {
        try {
            $sql = "INSERT INTO clientes (nombres, apellidos, num_doc, telefono, correo, idusuario, idestado, foto, extra) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data['nombres'],
                $data['apellidos'],
                $data['num_doc'] ?? null,
                $data['telefono'] ?? null,
                $data['correo'] ?? null,
                $data['idusuario'] ?? null,
                $data['idestado'] ?? null,
                $data['foto'] ?? 'assets/img/usuariodefault.png',
                $data['extra'] ?? null
            ]);

            $idcliente = $this->pdo->lastInsertId();

            if (isset($data['idempresa'])) {
                $this->asignarEmpresaACliente($idcliente, $data['idempresa']);
            }

            // Registrar cambio de creación
            if (!empty($_SESSION['idusuario'])) {
                $this->registroCambioModel->registrarCambio(
                    $_SESSION['idusuario'],
                    $idcliente,
                    'cliente',
                    'creacion',
                    null,
                    null,
                    null,
                    "Cliente creado: {$data['nombres']} {$data['apellidos']}"
                );
            }

            return $idcliente;
        } catch (Exception $e) {
            throw new Exception("Error al crear cliente: " . $e->getMessage());
        }
    }

    /**
     * Obtener proyectos de un cliente
     */
    private function obtenerProyectosPorCliente($idcliente, $tipoCliente = 'cliente')
    {
        try {
            $sql = "SELECT p.*, ep.estado
                FROM clientes_proyectos cp
                INNER JOIN proyectos p ON cp.idproyecto = p.idproyecto
                INNER JOIN estados_proyectos ep ON p.idestado = ep.idestado
                WHERE cp.idreferencia = ? AND cp.tipo_cliente = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$idcliente, $tipoCliente]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error al obtener proyectos del cliente: " . $e->getMessage());
        }
    }

    public function asignarUsuarioACliente($idcliente, $idusuario)
    {
        try {
            // Estado anterior
            $stmtPrevio = $this->pdo->prepare("SELECT idusuario FROM clientes WHERE idcliente = ?");
            $stmtPrevio->execute([$idcliente]);
            $usuarioPrevio = $stmtPrevio->fetch(PDO::FETCH_ASSOC);
            $estadoPrevio = $usuarioPrevio && $usuarioPrevio['idusuario']
                ? [['idreferencia' => $usuarioPrevio['idusuario']]]
                : [];

            // Actualizar asignación
            $sql = "UPDATE clientes SET idusuario = ? WHERE idcliente = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$idusuario, $idcliente]);

            // Estado nuevo
            $stmtNuevo = $this->pdo->prepare("SELECT idusuario FROM clientes WHERE idcliente = ?");
            $stmtNuevo->execute([$idcliente]);
            $usuarioNuevo = $stmtNuevo->fetch(PDO::FETCH_ASSOC);
            $estadoNuevo = $usuarioNuevo && $usuarioNuevo['idusuario']
                ? [['idreferencia' => $usuarioNuevo['idusuario']]]
                : [];

            // Registrar cambios
            if (!empty($_SESSION['idusuario'])) {
                $this->registroCambioModel->registrarAsignaciones(
                    $_SESSION['idusuario'],   // usuario que hace el cambio
                    $idcliente,               // id de referencia
                    'cliente',                // tipo
                    'usuario',                // relación
                    $estadoPrevio,
                    $estadoNuevo
                );
            }

            return true;
        } catch (Exception $e) {
            throw new Exception("Error al asignar usuario al cliente: " . $e->getMessage());
        }
    }

    public function asignarUsuarioAEmpresa($idempresa, $idusuario)
    {
        try {
            // Estado anterior
            $stmtPrevio = $this->pdo->prepare("SELECT idusuario FROM empresas WHERE idempresa = ?");
            $stmtPrevio->execute([$idempresa]);
            $usuarioPrevio = $stmtPrevio->fetch(PDO::FETCH_ASSOC);
            $estadoPrevio = $usuarioPrevio && $usuarioPrevio['idusuario']
                ? [['idreferencia' => $usuarioPrevio['idusuario']]]
                : [];

            // Actualizar asignación
            $sql = "UPDATE empresas SET idusuario = ? WHERE idempresa = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$idusuario, $idempresa]);

            // Estado nuevo
            $stmtNuevo = $this->pdo->prepare("SELECT idusuario FROM empresas WHERE idempresa = ?");
            $stmtNuevo->execute([$idempresa]);
            $usuarioNuevo = $stmtNuevo->fetch(PDO::FETCH_ASSOC);
            $estadoNuevo = $usuarioNuevo && $usuarioNuevo['idusuario']
                ? [['idreferencia' => $usuarioNuevo['idusuario']]]
                : [];

            // Registrar cambios
            if (!empty($_SESSION['idusuario'])) {
                $this->registroCambioModel->registrarAsignaciones(
                    $_SESSION['idusuario'],   // usuario que hace el cambio
                    $idempresa,               // id de referencia
                    'empresa',                // tipo
                    'usuario',                // relación
                    $estadoPrevio,
                    $estadoNuevo
                );
            }

            return true;
        } catch (Exception $e) {
            throw new Exception("Error al asignar usuario a la empresa: " . $e->getMessage());
        }
    }

    public function asignarProyectoACliente($idcliente, $idproyecto, $idusuario = null)
    {
        try {
            // Estado anterior
            $stmtPrevio = $this->pdo->prepare("SELECT idproyecto FROM clientes_proyectos WHERE idcliente = ?");
            $stmtPrevio->execute([$idcliente]);
            $proyectosPrevios = array_map(fn($p) => ['idreferencia' => $p['idproyecto']], $stmtPrevio->fetchAll(PDO::FETCH_ASSOC));

            // Insertar nueva asignación
            $sql = "INSERT IGNORE INTO clientes_proyectos (idcliente, idproyecto) VALUES (?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$idcliente, $idproyecto]);

            // Estado nuevo
            $stmtNuevo = $this->pdo->prepare("SELECT idproyecto FROM clientes_proyectos WHERE idcliente = ?");
            $stmtNuevo->execute([$idcliente]);
            $proyectosNuevos = array_map(fn($p) => ['idreferencia' => $p['idproyecto']], $stmtNuevo->fetchAll(PDO::FETCH_ASSOC));

            // Registrar cambios
            if (!empty($_SESSION['idusuario'])) {
                $this->registroCambioModel->registrarAsignaciones(
                    $_SESSION['idusuario'],
                    $idcliente,
                    'cliente',
                    'proyectos',
                    $proyectosPrevios,
                    $proyectosNuevos
                );
            }

            return true;
        } catch (Exception $e) {
            throw new Exception("Error al asignar proyecto al cliente: " . $e->getMessage());
        }
    }

    public function asignarEmpresaACliente($idcliente, $idempresa, $idusuario = null)
    {
        try {
            // Estado anterior
            $stmtPrevio = $this->pdo->prepare("SELECT idempresa FROM empresas_clientes WHERE idcliente = ?");
            $stmtPrevio->execute([$idcliente]);
            $empresaPrev = $stmtPrevio->fetchColumn();
            $empresasPrevias = $empresaPrev ? [['idreferencia' => $empresaPrev]] : [];

            if ($empresaPrev) {
                $stmtUpdate = $this->pdo->prepare("UPDATE empresas_clientes SET idempresa=? WHERE idcliente=?");
                $stmtUpdate->execute([$idempresa, $idcliente]);
            } else {
                $stmtInsert = $this->pdo->prepare("INSERT IGNORE INTO empresas_clientes (idcliente, idempresa) VALUES (?, ?)");
                $stmtInsert->execute([$idcliente, $idempresa]);
            }

            // Estado nuevo
            $stmtNuevo = $this->pdo->prepare("SELECT idempresa FROM empresas_clientes WHERE idcliente = ?");
            $stmtNuevo->execute([$idcliente]);
            $empresaNuevo = $stmtNuevo->fetchColumn();
            $empresasNuevas = $empresaNuevo ? [['idreferencia' => $empresaNuevo]] : [];

            // Registrar cambios
            if (!empty($_SESSION['idusuario'])) {
                $this->registroCambioModel->registrarAsignaciones(
                    $_SESSION['idusuario'],
                    $idcliente,
                    'cliente',
                    'empresas',
                    $empresasPrevias,
                    $empresasNuevas
                );
            }

            return true;
        } catch (Exception $e) {
            throw new Exception("Error al asignar empresa al cliente: " . $e->getMessage());
        }
    }

    public function actualizarCliente($id, $data)
    {
        try {
            $this->pdo->beginTransaction();

            // --- 1) Obtener cliente antes (para auditoría)
            $clienteAntes = $this->obtenerCliente($id);

            // --- 2) Armar actualización dinámica
            $campos = [];
            $params = [];
            $dataValidos = []; // <- Solo guardaremos los campos válidos

            $camposTabla = $this->registroCambioModel->obtenerCamposTabla("clientes");

            foreach ($data as $campo => $valor) {
                if (!in_array($campo, $camposTabla, true)) {
                    continue;
                }

                if (in_array($campo, ["idcliente"])) {
                    continue;
                }

                if ($campo === "foto") {
                    $campos[] = "foto = :foto";
                    $params[':foto'] = $valor ?? $clienteAntes['foto'] ?? "assets/img/usuariodefault.png";
                    $dataValidos[$campo] = $params[':foto'];
                } elseif (!in_array($campo, ["id", "idempresa", "idcliente", "empresa"])) {
                    $campos[] = "$campo = :{$campo}";
                    $params[":{$campo}"] = $valor;
                    $dataValidos[$campo] = $valor;
                }
            }

            if (!empty($campos)) {
                $sql = "UPDATE clientes SET " . implode(", ", $campos) . " WHERE idcliente = :id";
                $params[':id'] = $id;

                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
            }

            // --- 3) Si viene una empresa en los datos, actualizar la relación
            if (!empty($data['idempresa'])) {
                $this->asignarEmpresaACliente($id, $data['idempresa']);
            }

            // --- 4) Registrar cambios automáticos SOLO con campos válidos
            if (!empty($_SESSION['idusuario']) && !empty($dataValidos)) {
                $this->registroCambioModel->registrarCambiosAutomaticos(
                    $_SESSION['idusuario'],
                    $id,
                    'cliente',
                    'actualizacion',
                    $clienteAntes,
                    $dataValidos
                );
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Error al actualizar cliente: " . $e->getMessage());
        }
    }

    public function eliminarCliente($id)
    {
        try {
            $sql = "DELETE FROM clientes WHERE idcliente = ?";
            $stmt = $this->pdo->prepare($sql);

            $cliente = $this->obtenerCliente($id);
            if ($cliente && !empty($_SESSION['idusuario'])) {
                // Registrar cambio de eliminación
                $this->registroCambioModel->registrarCambio(
                    $_SESSION['idusuario'],
                    $id,
                    'cliente',
                    'eliminacion',
                    null,
                    "{$cliente['nombres']} {$cliente['apellidos']}",
                    null,
                    "Cliente eliminado: {$cliente['nombres']} {$cliente['apellidos']}"
                );
            }

            return $stmt->execute([$id]);
        } catch (Exception $e) {
            throw new Exception("Error al eliminar cliente: " . $e->getMessage());
        }
    }

    public function crearEmpresa($data)
    {
        try {
            $sql = "INSERT INTO empresas (razon_social, ruc, direccion, direccion_referencia, foto) 
                VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data['razon_social'],
                $data['ruc'] ?? null,
                $data['direccion'] ?? null,
                $data['direccion_referencia'] ?? null,
                $data['foto'] ?? 'assets/img/organizaciondefault.png'
            ]);

            if (!empty($_SESSION['idusuario'])) {
                $this->registroCambioModel->registrarCambio(
                    $_SESSION['idusuario'],
                    $this->pdo->lastInsertId(),
                    'empresa',
                    'creacion',
                    null,
                    null,
                    null,
                    "Empresa creada: {$data['razon_social']}"
                );
            }

            return $this->pdo->lastInsertId();
        } catch (Exception $e) {
            throw new Exception("Error al crear empresa: " . $e->getMessage());
        }
    }

    public function actualizarEmpresa($idempresa, $data)
    {
        try {
            $stmtFoto = $this->pdo->prepare("SELECT foto FROM empresas WHERE idempresa = :id");
            $stmtFoto->execute(['id' => $idempresa]);
            $fotoActual = $stmtFoto->fetchColumn();

            $sql = "UPDATE empresas 
                SET razon_social = ?, ruc = ?, direccion = ?, direccion_referencia = ?, foto = ?
                WHERE idempresa = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data['razon_social'],
                $data['ruc'] ?? null,
                $data['direccion'] ?? null,
                $data['direccion_referencia'] ?? null,
                $data['foto'] ?? $fotoActual ?? "assets/img/organizaciondefault.png",
                $idempresa
            ]);

            if (!empty($_SESSION['idusuario'])) {
                $this->registroCambioModel->registrarCambiosAutomaticos(
                    $_SESSION['idusuario'],
                    $idempresa,
                    'empresa',
                    'actualizacion',
                    $this->obtenerOrganizacion($idempresa),
                    $data
                );
            }

            return true;
        } catch (Exception $e) {
            throw new Exception("Error al actualizar empresa: " . $e->getMessage());
        }
    }

    public function editarEmpresa($id, $data)
    {
        try {
            $this->pdo->beginTransaction();

            // --- 1) Obtener empresa antes (para auditoría)
            $empresaAntes = $this->obtenerOrganizacion($id);

            // --- 2) Armar actualización dinámica
            $campos = [];
            $params = [];
            $dataValidos = []; // <- Solo guardaremos los campos válidos

            $camposTabla = $this->registroCambioModel->obtenerCamposTabla("empresas");

            foreach ($data as $campo => $valor) {
                if (!isset($camposTabla[$campo])) {
                    continue; // ignorar campos que no existen en la tabla/diccionario
                }

                if (in_array($campo, ["idempresa"])) {
                    continue;
                }

                // control especial para foto (si no viene, no la tocamos)
                if ($campo === "foto") {
                    $campos[] = "foto = :foto";
                    $params[':foto'] = $valor ?? $empresaAntes['foto'] ?? "assets/img/organizaciondefault.png";
                    $dataValidos[$campo] = $params[':foto'];
                } elseif (!in_array($campo, ["id", "idempresa", "idcliente"])) {
                    $campos[] = "$campo = :$campo";
                    $params[":$campo"] = $valor;
                    $dataValidos[$campo] = $valor;
                }
            }

            if (!empty($campos)) {
                $sql = "UPDATE empresas SET " . implode(", ", $campos) . " WHERE idempresa = :id";
                $params[':id'] = $id;

                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
            }

            // --- 4) Registrar cambios automáticos SOLO con campos válidos
            if (!empty($_SESSION['idusuario']) && !empty($dataValidos)) {
                $this->registroCambioModel->registrarCambiosAutomaticos(
                    $_SESSION['idusuario'],
                    $id,
                    'empresa',
                    'actualizacion',
                    $empresaAntes,
                    $dataValidos
                );
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Error al actualizar cliente: " . $e->getMessage());
        }
    }

    public function eliminarEmpresa($id)
    {
        try {
            $sql = "DELETE FROM empresas WHERE idempresa = ?";
            $stmt = $this->pdo->prepare($sql);

            $empresa = $this->obtenerOrganizacion($id);
            if ($empresa && !empty($_SESSION['idusuario'])) {
                // Registrar cambio de eliminación
                $this->registroCambioModel->registrarCambio(
                    $_SESSION['idusuario'],
                    $id,
                    'empresa',
                    'eliminacion',
                    null,
                    "{$empresa['razon_social']}",
                    null,
                    "Empresa eliminada: {$empresa['razon_social']}"
                );
            }

            return $stmt->execute([$id]);
        } catch (Exception $e) {
            throw new Exception("Error al eliminar empresa: " . $e->getMessage());
        }
    }
}
