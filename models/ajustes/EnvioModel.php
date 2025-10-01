<?php
require_once __DIR__ . "/../notas/NotaModel.php";
require_once __DIR__ . "/../cambios/RegistroCambio.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class EnvioModel
{
    private $pdo;
    private $notaModel;
    private $registroCambioModel;

    public function __construct($pdo, $registroCambioModel = null)
    {
        try {
            $this->pdo = $pdo;
            $this->notaModel = new NotaModel($this->pdo);
            $this->registroCambioModel = $registroCambioModel ?: new RegistroCambioModel($this->pdo);
        } catch (PDOException $e) {
            throw new Exception("Error al conectar en EnvioModel: " . $e->getMessage());
        }
    }

    /* ==========================
     * EMISORES
     * ========================== */
    public function obtenerEmisores()
    {
        $sql = "SELECT * FROM emisores ORDER BY fecha_creacion DESC";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    private function obtenerEmisorAleatorio()
    {
        $emisores = $this->obtenerEmisores();
        if (empty($emisores)) {
            throw new Exception("No existen emisores registrados.");
        }
        $index = array_rand($emisores);
        return $emisores[$index]['idemisor'];
    }

    public function crearEmisor($data)
    {
        try {
            $this->pdo->beginTransaction();

            $campos = [];
            $placeholders = [];
            $params = [];

            $camposTabla = $this->registroCambioModel->obtenerCamposTabla("emisores");

            foreach ($data as $campo => $valor) {
                if (!in_array($campo, $camposTabla, true)) {
                    continue; // ignorar campos inexistentes
                }

                if ($campo === "idemisor") {
                    continue; // excluir PK
                }

                // Normalizar valores opcionales
                if (in_array($campo, ["descripcion", "correo", "telefono"])) {
                    $valor = (isset($valor) && trim($valor) !== '') ? $valor : null;
                }

                $campos[] = $campo;
                $placeholders[] = ":$campo";
                $params[":$campo"] = $valor;
            }

            if (!empty($campos)) {
                $sql = "INSERT INTO emisores (" . implode(", ", $campos) . ")
                    VALUES (" . implode(", ", $placeholders) . ")";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
            }

            $idemisor = $this->pdo->lastInsertId();

            // --- Auditoría
            $this->registroCambioModel->registrarCambio(
                $data['idusuario'] ?? $_SESSION['idusuario'],
                $idemisor,
                'emisor',
                'creacion',
                null,
                null,
                null,
                "Emisor creado: " . ($data['nombre'] ?? '')
            );

            $this->pdo->commit();
            return $idemisor;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Error al crear emisor: " . $e->getMessage());
        }
    }

    public function obtenerEmisor($id)
    {
        try {
            $sql = "SELECT e.*, CONCAT(u.nombres, ' ', u.apellidos) AS usuario
                FROM emisores e
                INNER JOIN usuarios u ON u.idusuario = e.idusuario
                WHERE e.idemisor = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error al obtener emisor: " . $e->getMessage());
        }
    }

    public function actualizarEmisor($id, $data)
    {
        try {
            $this->pdo->beginTransaction();

            // --- 1) Obtener emisor antes (para auditoría)
            $emisorAntes = $this->obtenerEmisor($id);

            // --- 2) Preparar actualización dinámica
            $campos = [];
            $params = [];
            $dataValidos = [];

            $camposTabla = $this->registroCambioModel->obtenerCamposTabla("emisores");

            foreach ($data as $campo => $valor) {
                if (!in_array($campo, $camposTabla, true)) {
                    continue; // ignorar campos inexistentes
                }
                if ($campo === "idemisor") {
                    continue; // excluir PK
                }

                // Normalizar valores opcionales
                if (in_array($campo, ["descripcion", "correo", "telefono"])) {
                    $valor = (isset($valor) && trim($valor) !== '') ? $valor : null;
                }

                $campos[] = "$campo = :$campo";
                $params[":$campo"] = $valor;
                $dataValidos[$campo] = $valor;
            }

            if (!empty($campos)) {
                $sql = "UPDATE emisores SET " . implode(", ", $campos) . " WHERE idemisor = :id";
                $params[':id'] = $id;
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
            }

            // --- 3) Registrar cambios automáticos
            if (!empty($_SESSION['idusuario']) && !empty($dataValidos)) {
                $this->registroCambioModel->registrarCambiosAutomaticos(
                    $_SESSION['idusuario'],
                    $id,
                    'emisor',
                    'actualizacion',
                    $emisorAntes,
                    $dataValidos
                );
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Error al actualizar emisor: " . $e->getMessage());
        }
    }

    public function eliminarEmisor($id)
    {
        try {
            $this->pdo->beginTransaction();

            // --- 1) Obtener emisor antes (para auditoría)
            $emisorAntes = $this->obtenerEmisor($id);

            // Validar que no tenga programaciones activas
            $stmtCheck = $this->pdo->prepare("SELECT COUNT(*) FROM programacion_envios WHERE idemisor = ?");
            $stmtCheck->execute([$id]);
            $count = $stmtCheck->fetchColumn();

            if ($count > 0) {
                throw new Exception("No se puede eliminar el emisor porque tiene programaciones de envío asociadas.");
            }

            // --- 2) Eliminar emisor
            $stmt = $this->pdo->prepare("DELETE FROM emisores WHERE idemisor = ?");
            $stmt->execute([$id]);

            // --- 3) Registrar auditoría
            if (isset($_SESSION['idusuario'])) {
                $this->registroCambioModel->registrarCambio(
                    $_SESSION['idusuario'],
                    $id,
                    'emisor',
                    'eliminacion',
                    $emisorAntes,
                    null,
                    null,
                    "Emisor eliminado: " . ($emisorAntes['nombre'] ?? '')
                );
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Error al eliminar emisor: " . $e->getMessage());
        }
    }

    /* ==========================
     * CAMPAÑAS
     * ========================== */
    public function crearCampania($data)
    {
        try {
            $this->pdo->beginTransaction();

            $sql = "INSERT INTO campanias (nombre, descripcion, fecha_inicio, fecha_fin, idusuario, fecha_creacion) 
                    VALUES (:nombre, :descripcion, :fecha_inicio, :fecha_fin, :idusuario, NOW())";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':nombre' => $data['nombre'],
                ':descripcion' => $data['descripcion'],
                ':fecha_inicio' => $data['fecha_inicio'],
                ':fecha_fin' => $data['fecha_fin'],
                ':idusuario' => $data['idusuario']
            ]);
            $idcampania = $this->pdo->lastInsertId();

            // Programaciones de envío
            if (!empty($data['programaciones']) && is_array($data['programaciones'])) {
                foreach ($data['programaciones'] as $prog) {
                    $this->programarEnvio([
                        'idemisor' => null, // asignado aleatoriamente dentro del método
                        'idreceptor' => $prog['idreceptor'],
                        'idcampania' => $idcampania,
                        'idplantilla' => $prog['idplantilla'],
                        'fecha_envio' => $prog['fecha_envio'],
                        'idestado' => $prog['idestado'] ?? 1,
                        'idusuario' => $data['idusuario']
                    ]);
                }
            }

            // Nota inicial (opcional)
            if (!empty($data['nota'])) {
                $this->notaModel->guardarNota($idcampania, 'campania', $data['idusuario'], $data['nota']);
            }

            // Registrar cambio
            $this->registroCambioModel->registrarCambio(
                $data['idusuario'] ?? $_SESSION['idusuario'],
                $idcampania,
                'campania',
                'creacion',
                null,
                null,
                null,
                "Campaña creada: " . $data['nombre']
            );

            $this->pdo->commit();
            return $idcampania;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Error al crear campaña: " . $e->getMessage());
        }
    }

    public function obtenerCampanias()
    {
        $sql = "SELECT c.*, CONCAT(u.nombres, ' ', u.apellidos) AS usuario
                FROM campanias c
                INNER JOIN usuarios u ON u.idusuario = c.idusuario
                ORDER BY c.fecha_creacion DESC";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerCampania($id)
    {
        $sql = "SELECT c.*, CONCAT(u.nombres, ' ', u.apellidos) AS usuario
                FROM campanias c
                INNER JOIN usuarios u ON u.idusuario = c.idusuario
                WHERE idcampania = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $campania = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($campania) {
            $campania['programaciones'] = $this->obtenerEnviosPorCampania($id);
            $campania['notas'] = $this->notaModel->obtenerNotas($id, 'campania');
        }

        return $campania;
    }

    public function actualizarCampania($id, $data)
    {
        try {
            $this->pdo->beginTransaction();

            // --- 1) Obtener campaña antes (para auditoría)
            $campaniaAntes = $this->obtenerCampania($id);

            // --- 2) Armar actualización dinámica
            $campos = [];
            $params = [];
            $dataValidos = [];

            $camposTabla = $this->registroCambioModel->obtenerCamposTabla("campanias");

            foreach ($data as $campo => $valor) {
                if (!in_array($campo, $camposTabla, true)) {
                    continue; // ignorar campos inexistentes
                }
                if ($campo === "idcampania") {
                    continue; // excluir PK
                }

                $campos[] = "$campo = :$campo";
                $params[":$campo"] = is_array($valor) ? json_encode($valor) : $valor;
                $dataValidos[$campo] = $params[":$campo"];
            }

            if (!empty($campos)) {
                $sql = "UPDATE campanias SET " . implode(", ", $campos) . " WHERE idcampania = :id";
                $params[':id'] = $id;
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
            }

            // --- 3) Reemplazar programaciones de envío (si se pasan)
            if (isset($data['programaciones']) && is_array($data['programaciones'])) {
                // Eliminar programaciones previas
                $stmtDel = $this->pdo->prepare("DELETE FROM programacion_envios WHERE idcampania = ?");
                $stmtDel->execute([$id]);

                // Insertar nuevas
                foreach ($data['programaciones'] as $prog) {
                    $this->programarEnvio([
                        'idemisor' => null, // asignado aleatoriamente
                        'idreceptor' => $prog['idreceptor'],
                        'idcampania' => $id,
                        'idplantilla' => $prog['idplantilla'],
                        'fecha_envio' => $prog['fecha_envio'],
                        'idestado' => $prog['idestado'] ?? 1,
                        'idusuario' => $data['idusuario']
                    ]);
                }
            }

            // --- 4) Nota asociada
            if (isset($data['nota']) && !empty($data['nota'])) {
                $this->notaModel->guardarNota($id, 'campania', $data['idusuario'], $data['nota']);
            }

            // --- 5) Registrar cambios
            if (!empty($_SESSION['idusuario']) && !empty($dataValidos)) {
                $this->registroCambioModel->registrarCambiosAutomaticos(
                    $_SESSION['idusuario'] ?? $_SESSION['idusuario'],
                    $id,
                    'campania',
                    'actualizacion',
                    $campaniaAntes,
                    $dataValidos
                );
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Error al actualizar campaña: " . $e->getMessage());
        }
    }

    public function eliminarCampania($id)
    {
        try {
            $this->pdo->beginTransaction();

            // Obtener campaña antes (para log)
            $campaniaAntes = $this->obtenerCampania($id);

            // Eliminar notas asociadas
            $stmtNotas = $this->pdo->prepare("DELETE FROM notas WHERE entidad = 'campania' AND identidad = ?");
            $stmtNotas->execute([$id]);

            // Eliminar programaciones asociadas
            $stmtProg = $this->pdo->prepare("DELETE FROM programacion_envios WHERE idcampania = ?");
            $stmtProg->execute([$id]);

            // Eliminar campaña
            $stmtCamp = $this->pdo->prepare("DELETE FROM campanias WHERE idcampania = ?");
            $stmtCamp->execute([$id]);

            // Registrar eliminación
            if (isset($_SESSION['idusuario'])) {
                $this->registroCambioModel->registrarCambio(
                    $_SESSION['idusuario'],
                    $id,
                    'campania',
                    'eliminacion',
                    $campaniaAntes,
                    null,
                    null,
                    "Campaña eliminada: " . ($campaniaAntes['nombre'] ?? '')
                );
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Error al eliminar campaña: " . $e->getMessage());
        }
    }

    /* ==========================
     * PROGRAMACIÓN DE ENVÍOS
     * ========================== */
    public function programarEnvio($data)
    {
        $idemisor = $data['idemisor'] ?? $this->obtenerEmisorAleatorio();

        $sql = "INSERT INTO programacion_envios (idemisor, idreceptor, idcampania, idplantilla, fecha_envio, idestado, idusuario) 
                VALUES (:idemisor, :idreceptor, :idcampania, :idplantilla, :fecha_envio, :idestado, :idusuario)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':idemisor' => $idemisor,
            ':idreceptor' => $data['idreceptor'],
            ':idcampania' => $data['idcampania'] ?? null,
            ':idplantilla' => $data['idplantilla'],
            ':fecha_envio' => $data['fecha_envio'],
            ':idestado' => $data['idestado'],
            ':idusuario' => $data['idusuario']
        ]);

        $idenvio = $this->pdo->lastInsertId();

        // Registrar auditoría
        $this->registroCambioModel->registrarCambio(
            $data['idusuario'] ?? $_SESSION['idusuario'],
            $idenvio,
            'envio',
            'creacion',
            null,
            null,
            null,
            "Programación de envío creada (receptor {$data['idreceptor']})"
        );

        return $idenvio;
    }

    public function obtenerEnviosPorCampania($idcampania)
    {
        $sql = "SELECT pe.*, e.nombre AS emisor, est.estado, c.nombre AS campania,
                       CONCAT(u.nombres, ' ', u.apellidos) AS usuario
                FROM programacion_envios pe
                INNER JOIN emisores e ON e.idemisor = pe.idemisor
                INNER JOIN estados_envios est ON est.idestado = pe.idestado
                INNER JOIN usuarios u ON u.idusuario = pe.idusuario
                LEFT JOIN campanias c ON c.idcampania = pe.idcampania
                WHERE pe.idcampania = ?
                ORDER BY pe.fecha_envio ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idcampania]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function actualizarEstadoEnvio($idenvio, $nuevoEstado)
    {
        $sql = "UPDATE programacion_envios SET idestado = :idestado WHERE idenvio = :idenvio";
        $stmt = $this->pdo->prepare($sql);
        $resultado = $stmt->execute([
            ':idestado' => $nuevoEstado,
            ':idenvio' => $idenvio
        ]);

        if ($resultado && isset($_SESSION['idusuario'])) {
            $this->registroCambioModel->registrarCambio(
                $_SESSION['idusuario'],
                $idenvio,
                'envio',
                'actualizacion',
                null,
                $nuevoEstado,
                null,
                "Estado de envío actualizado a {$nuevoEstado}"
            );
        }

        return $resultado;
    }

    /* ==========================
     * ESTADOS
     * ========================== */
    public function obtenerEstadosEnvio()
    {
        return $this->pdo->query("SELECT * FROM estados_envios ORDER BY idestado ASC")->fetchAll(PDO::FETCH_ASSOC);
    }
}
