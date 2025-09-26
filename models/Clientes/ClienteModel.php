<?php
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../cambios/RegistroCambio.php";
require_once __DIR__ . "/../actividades/ActividadModel.php";

class ClienteModel
{
    private $pdo;
    private $registroCambioModel;

    public function __construct()
    {
        try {
            $this->pdo = connectDatabase();
            $this->registroCambioModel = new RegistroCambioModel();
        } catch (PDOException $e) {
            die("Error al conectar en ClienteModel: " . $e->getMessage());
        }
    }

    public function obtenerClientes($idestado = null)
    {
        try {
            $sql = '';

            $sql = "SELECT c.*,
                    ec.estado,
                    e.idempresa,
                    e.razon_social AS empresa_nombre,
                    e.ruc AS empresa_ruc,
                    e.foto AS empresa_foto
                FROM clientes c
                LEFT JOIN estados_clientes ec ON c.idestado = ec.idestado
                LEFT JOIN empresas_clientes emc ON c.idcliente = emc.idcliente
                LEFT JOIN empresas e ON e.idempresa = emc.idempresa";

            $params = [];

            if (!empty($idestado)) {
                $sql .= " WHERE c.idestado = ?";
                $params[] = $idestado;
            }

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($clientes as &$cliente) {
                $cliente['proyectos'] = $this->obtenerProyectosPorCliente($cliente['idcliente'], "cliente");
            }

            return $clientes;
        } catch (Exception $e) {
            throw new Exception("Error al obtener clientes: " . $e->getMessage());
        }
    }

    public function buscarClientes($filtro, $idestado = null)
    {
        try {
            $sql = "SELECT c.*,
                    ec.estado,
                    e.idempresa,
                    e.razon_social AS empresa_nombre,
                    e.ruc AS empresa_ruc,
                    e.foto AS empresa_foto
                FROM clientes c
                LEFT JOIN estados_clientes ec ON c.idestado = ec.idestado
                LEFT JOIN empresas_clientes emc ON c.idcliente = emc.idcliente
                LEFT JOIN empresas e ON e.idempresa = emc.idempresa
                WHERE (c.nombres LIKE ? OR c.apellidos LIKE ? OR c.num_doc LIKE ?)";

            $params = ["%$filtro%", "%$filtro%", "%$filtro%"];

            if (!empty($idestado)) {
                $sql .= " AND c.idestado = ?";
                $params[] = $idestado;
            }

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($clientes as &$cliente) {
                $cliente['proyectos'] = $this->obtenerProyectosPorCliente($cliente['idcliente'], "cliente");
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
                    e.foto AS empresa_foto
            FROM clientes c WHERE c.idestado = ?
            LEFT JOIN estados_clientes ec ON c.idestado = ec.idestado
            LEFT JOIN empresas_clientes emc ON c.idcliente = emc.idcliente
            LEFT JOIN empresas e ON e.idempresa = emc.idempresa";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$idestado]);
            $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($clientes as &$cliente) {
                $cliente['proyectos'] = $this->obtenerProyectosPorCliente($cliente['idcliente'], "cliente");
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
                    e.foto AS empresa_foto
            FROM clientes c
            LEFT JOIN estados_clientes ec ON c.idestado = ec.idestado
            LEFT JOIN empresas_clientes emc ON c.idcliente = emc.idcliente
            LEFT JOIN empresas e ON e.idempresa = emc.idempresa
            WHERE c.idcliente = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
            $cliente['proyectos'] = $this->obtenerProyectosPorCliente($cliente['idcliente'], "cliente");

            if ($cliente) {
                require_once __DIR__ . '/../ajustes/AjustesModel.php';
                $ajustesModel = new AjustesModel();
                $cliente['campos_extra'] = $ajustesModel->obtenerCampos($idcliente, 'cliente');
            }

            return $cliente;
        } catch (Exception $e) {
            throw new Exception("Error al obtener cliente: " . $e->getMessage());
        }
    }

    public function obtenerOrganizaciones()
    {
        try {
            $sql = "SELECT * FROM empresas";

            $stmt = $this->pdo->query($sql);
            $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $clientes;
        } catch (Exception $e) {
            throw new Exception("Error al buscar clientes: " . $e->getMessage());
        }
    }

    public function buscarOrganizaciones($filtro)
    {
        try {
            $sql = "SELECT * FROM empresas
                WHERE (razon_social LIKE ? OR ruc LIKE ?)";

            $params = ["%$filtro%", "%$filtro%"];

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $clientes;
        } catch (Exception $e) {
            throw new Exception("Error al buscar clientes: " . $e->getMessage());
        }
    }

    public function obtenerOrganizacion($id)
    {
        try {
            $sql = "SELECT * FROM empresas
            WHERE idempresa = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

            return $cliente;
        } catch (Exception $e) {
            throw new Exception("Error al obtener organización: " . $e->getMessage());
        }
    }

    public function buscarClientesYOrganizaciones($filtro, $idestado = null)
    {
        try {
            $clientes = $this->buscarClientes($filtro, $idestado);
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
            LEFT JOIN clientes c ON n.idreferencia = c.idcliente AND n.tipo_cliente = 'cliente'
            LEFT JOIN empresas e ON n.idreferencia = e.idempresa AND n.tipo_cliente = 'empresa'
            WHERE 1=1";
        $params = [];
        if ($idreferencia !== null) {
            $sql .= " AND n.idreferencia = ?";
            $params[] = $idreferencia;
        }
        if ($tipoCliente !== null) {
            $sql .= " AND n.tipo_cliente = ?";
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
            $sql = "INSERT INTO clientes (nombres, apellidos, num_doc, telefono, correo, idestado, foto) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data['nombres'],
                $data['apellidos'],
                $data['num_doc'] ?? null,
                $data['telefono'] ?? null,
                $data['correo'] ?? null,
                $data['idestado'] ?? null,
                $data['foto'] ?? 'assets/img/usuariodefault.png'
            ]);

            $idcliente = $this->pdo->lastInsertId();

            if (isset($data['idempresa'])) {
                $this->asignarEmpresaACliente($idcliente, $data['idempresa']);
            }

            // Registrar cambio de creación
            $this->registroCambioModel->registrarCambio(
                $data['idusuario'],
                $idcliente,
                'cliente',
                'creacion',
                null,
                null,
                null,
                "Cliente creado: {$data['nombres']} {$data['apellidos']}"
            );

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

    /**
     * Asignar un proyecto a un cliente
     */
    public function asignarProyectoACliente($idcliente, $idproyecto)
    {
        try {
            $sql = "INSERT IGNORE INTO clientes_proyectos (idcliente, idproyecto) VALUES (?, ?)";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$idcliente, $idproyecto]);
        } catch (Exception $e) {
            throw new Exception("Error al asignar proyecto al cliente: " . $e->getMessage());
        }
    }

    public function asignarEmpresaACliente($idcliente, $idempresa)
    {
        try {
            $stmtCheck = $this->pdo->prepare("SELECT idempresa FROM empresas_clientes WHERE idcliente=?");
            $stmtCheck->execute([$idcliente]);
            $empresaExistente = $stmtCheck->fetchColumn();

            if ($empresaExistente) {
                // Actualizar relación
                $stmtUpdate = $this->pdo->prepare("UPDATE empresas_clientes SET idempresa=? WHERE idcliente=?");
                $stmtUpdate->execute([$idempresa, $idcliente]);
            } else {
                $stmtInsert = $this->pdo->prepare("INSERT IGNORE INTO empresas_clientes (idcliente, idempresa) VALUES (?, ?)");
                $stmtInsert->execute([$idcliente, $idempresa]);
            }
        } catch (Exception $e) {
            throw new Exception("Error al asignar empresa al cliente: " . $e->getMessage());
        }
    }

    public function actualizarCliente($id, $data)
    {
        try {
            $this->pdo->beginTransaction();

            // --- 1) Obtener la foto actual
            $stmtFoto = $this->pdo->prepare("SELECT foto FROM clientes WHERE idcliente = :id");
            $stmtFoto->execute(['id' => $id]);
            $fotoActual = $stmtFoto->fetchColumn();

            // --- 2) Actualizar datos del cliente
            $sql = "UPDATE clientes 
                SET nombres=?, apellidos=?, num_doc=?, telefono=?, correo=?, idestado=?, foto=? 
                WHERE idcliente=?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data['nombres'],
                $data['apellidos'],
                $data['num_doc'] ?? null,
                $data['telefono'] ?? null,
                $data['correo'] ?? null,
                $data['idestado'] ?? null,
                $data['foto'] ?? $fotoActual ?? "assets/img/usuariodefault.png",
                $id
            ]);

            // --- 3) Si viene una empresa en los datos, actualizar la relación
            if (!empty($data['idempresa'])) {
                $this->asignarEmpresaACliente($id, $data['idempresa']);
            }

            // --- 4) Registrar cambios automáticos
            $this->registroCambioModel->registrarCambiosAutomaticos(
                $data['idusuario'],
                $id,
                'cliente',
                'actualizacion',
                $this->obtenerCliente($id),
                $data
            );

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
            if ($cliente) {
                // Registrar cambio de eliminación
                $this->registroCambioModel->registrarCambio(
                    null,
                    $id,
                    'cliente',
                    'eliminacion',
                    null,
                    null,
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

            $this->registroCambioModel->registrarCambio(
                $data['idusuario'],
                $this->pdo->lastInsertId(),
                'empresa',
                'creacion',
                null,
                null,
                null,
                "Empresa creada: {$data['razon_social']}"
            );

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

            $this->registroCambioModel->registrarCambiosAutomaticos(
                $data['idusuario'],
                $idempresa,
                'empresa',
                'actualizacion',
                $this->obtenerOrganizacion($idempresa),
                $data
            );

            return true;
        } catch (Exception $e) {
            throw new Exception("Error al actualizar empresa: " . $e->getMessage());
        }
    }

    public function eliminarEmpresa($id)
    {
        try {
            $sql = "DELETE FROM empresas WHERE idempresa = ?";
            $stmt = $this->pdo->prepare($sql);

            $empresa = $this->obtenerOrganizacion($id);
            if ($empresa) {
                // Registrar cambio de eliminación
                $this->registroCambioModel->registrarCambio(
                    null,
                    $id,
                    'empresa',
                    'eliminacion',
                    null,
                    null,
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
