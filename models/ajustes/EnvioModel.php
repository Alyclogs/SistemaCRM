<?php
require_once __DIR__ . "/../notas/NotaModel.php";
require_once __DIR__ . "/../cambios/RegistroCambio.php";
require_once __DIR__ . '/PlantillaModel.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class EnvioModel
{
    private $pdo;
    private $notaModel;
    private $registroCambioModel;
    private $plantillaModel;

    public function __construct($pdo, $registroCambioModel = null)
    {
        try {
            $this->pdo = $pdo;
            $this->notaModel = new NotaModel($this->pdo);
            $this->plantillaModel = new PlantillaModel($this->pdo);
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

                if ($campo === "idemisor") continue; // excluir PK

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

            // 🧾 Registrar cambio
            $this->registroCambioModel->registrarCambio(
                $data['idusuario'] ?? $_SESSION['idusuario'] ?? null,
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
    public function obtenerCampanias()
    {
        $sql = "SELECT c.*, CONCAT(u.nombres, ' ', u.apellidos) AS usuario
            FROM campanias c
            INNER JOIN usuarios u ON u.idusuario = c.idusuario
            ORDER BY c.fecha_creacion DESC";

        $campanias = $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        foreach ($campanias as &$campania) {
            $id = $campania['idcampania'];
            $campania['programaciones'] = $this->obtenerEnviosPorCampania($id);
            $campania['notas'] = $this->notaModel->obtenerNotas($id, 'campania');
        }

        return $campanias;
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

    public function crearCampania($data)
    {
        try {
            $this->pdo->beginTransaction();
            $modalidad = $data['modalidadProgramacion'];

            $sql = "INSERT INTO campanias (nombre, descripcion, fecha_inicio, fecha_fin, modalidad_envio, estado, idusuario, fecha_creacion) 
                VALUES (:nombre, :descripcion, :fecha_inicio, :fecha_fin, :modalidad_envio, :estado, :idusuario, NOW())";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':nombre' => $data['nombre'],
                ':descripcion' => $data['descripcion'] ?? null,
                ':fecha_inicio' => $data['fecha_inicio'] ?? null,
                ':fecha_fin' => $data['fecha_fin'] ?? null,
                ':modalidad_envio' => $modalidad ?? "dias_despues",
                ':estado' => $data['estado'] ?? "creada",
                ':idusuario' => $data['idusuario'] ?? $_SESSION['idusuario']
            ]);
            $idcampania = $this->pdo->lastInsertId();

            // Programaciones de envío
            if (!empty($data['programaciones']) && is_array($data['programaciones'])) {
                foreach ($data['programaciones'] as $prog) {
                    $idenvio = $this->programarEnvio([
                        'idemisor' => null, // asignado aleatoriamente
                        'idcampania' => $idcampania,
                        'idplantilla' => $prog['idplantilla'],
                        'hora_envio' => $prog['hora_envio'] ?? '08:00:00',
                        'dias_despues' => $modalidad === "dias_despues" ? $prog['dias_despues'] ?? null : null,
                        'dia_semana' => $modalidad === "dias_semana" ? $prog['dia_semana'] ?? null : null,
                        'estado' => $prog['estado'] ?? "creada",
                        'idusuario' => $data['idusuario'] ?? $_SESSION['idusuario']
                    ]);

                    // Asociar receptores (clientes)
                    if (!empty($prog['receptores']) && is_array($prog['receptores'])) {
                        $this->actualizarRelacionesEnvioClientes($idenvio, $prog['receptores']);
                    }
                }
            }

            // Nota inicial (opcional)
            if (!empty($data['nota'])) {
                $this->notaModel->guardarNota($idcampania, 'campania', $data['idusuario'], $data['nota']);
            }

            // Registro de cambio
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

    public function actualizarCampania($id, $data)
    {
        try {
            $this->pdo->beginTransaction();

            // Obtener campaña antes (para auditoría)
            $campaniaAntes = $this->obtenerCampania($id);

            // Actualizar datos básicos
            $campos = [];
            $params = [];
            $dataValidos = [];
            $camposTabla = $this->registroCambioModel->obtenerCamposTabla("campanias");

            foreach ($data as $campo => $valor) {
                if (!in_array($campo, $camposTabla, true) || $campo === "idcampania") continue;

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

            // Reemplazar programaciones de envío
            if (isset($data['programaciones']) && is_array($data['programaciones'])) {
                // Eliminar programaciones previas
                $stmtDel = $this->pdo->prepare("DELETE FROM programacion_envios WHERE idcampania = ?");
                $stmtDel->execute([$id]);

                // Insertar nuevas
                foreach ($data['programaciones'] as $prog) {
                    $idenvio = $this->programarEnvio([
                        'idemisor' => null,
                        'idcampania' => $id,
                        'idplantilla' => $prog['idplantilla'],
                        'hora_envio' => $prog['hora_envio'] ?? '08:00:00',
                        'dias_despues' => $prog['dias_despues'] ?? null,
                        'dia_semana' => $prog['dia_semana'] ?? null,
                        'estado' => $prog['estado'] ?? "creada",
                        'idusuario' => $data['idusuario'] ?? $_SESSION['idusuario']
                    ]);

                    if (!empty($prog['receptores']) && is_array($prog['receptores'])) {
                        $this->actualizarRelacionesEnvioClientes($idenvio, $prog['receptores']);
                    }
                }
            }

            // Nota asociada
            if (!empty($data['nota'])) {
                $this->notaModel->guardarNota($id, 'campania', $data['idusuario'], $data['nota']);
            }

            // Registrar auditoría
            if (!empty($_SESSION['idusuario']) && !empty($dataValidos)) {
                $this->registroCambioModel->registrarCambiosAutomaticos(
                    $_SESSION['idusuario'],
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
            $stmtNotas = $this->pdo->prepare("DELETE FROM notas WHERE tipo = 'campania' AND idreferencia = ?");
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
                    $campaniaAntes['nombre'],
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

    public function iniciarCampania($idcampania, $idusuario = null, $programaciones = [])
    {
        try {
            $this->pdo->beginTransaction();

            // 1️⃣ Verificar campaña existente
            $campaniaAntes = $this->obtenerCampania($idcampania);
            if (!$campaniaAntes) {
                throw new Exception("La campaña no existe.");
            }

            // 2️⃣ Activar campaña
            $sql = "UPDATE campanias
            SET estado = 'activa',
                fecha_inicio = NOW()
            WHERE idcampania = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$idcampania]);

            // 3️⃣ Preparar sentencia para actualizar programaciones
            $sqlProg = "UPDATE programacion_envios
                    SET estado = 'activa',
                        fecha_envio = :fecha_envio,
                        hora_envio = :hora_envio
                    WHERE idenvio = :idenvio AND idcampania = :idcampania";
            $stmtProg = $this->pdo->prepare($sqlProg);

            // 4️⃣ Preparar sentencia para insertar receptores
            $sqlInsertReceptor = "INSERT INTO envios_receptores (idenvio, idreceptor, estado)
                              VALUES (:idenvio, :idreceptor, 'activa')";
            $stmtInsertReceptor = $this->pdo->prepare($sqlInsertReceptor);

            // 5️⃣ Recorremos programaciones
            if (!empty($programaciones) && is_array($programaciones)) {
                foreach ($programaciones as $p) {
                    if (empty($p['idenvio']) || empty($p['fecha_envio'])) continue;

                    // Actualizar programación
                    $stmtProg->execute([
                        ':fecha_envio' => $p['fecha_envio'],
                        ':hora_envio'  => $p['hora_envio'] ?? null,
                        ':idenvio'     => $p['idenvio'],
                        ':idcampania'  => $idcampania
                    ]);

                    // Registrar clientes (si existen)
                    if (!empty($p['clientes']) && is_array($p['clientes'])) {
                        foreach ($p['clientes'] as $idCliente) {
                            if (empty($idCliente)) continue;

                            // Verificar si ya existe relación
                            $checkSql = "SELECT COUNT(*) FROM envios_receptores WHERE idenvio = ? AND idreceptor = ?";
                            $stmtCheck = $this->pdo->prepare($checkSql);
                            $stmtCheck->execute([$p['idenvio'], $idCliente]);
                            $existe = $stmtCheck->fetchColumn();

                            if (!$existe) {
                                $stmtInsertReceptor->execute([
                                    ':idenvio'    => $p['idenvio'],
                                    ':idreceptor' => $idCliente
                                ]);
                            }
                        }
                    }
                }
            } else {
                // Si no se enviaron programaciones desde el frontend, activar todas sin modificar fechas
                $sqlProg = "UPDATE programacion_envios
                        SET estado = 'activa'
                        WHERE idcampania = ?";
                $stmtProg = $this->pdo->prepare($sqlProg);
                $stmtProg->execute([$idcampania]);
            }

            // 6️⃣ Registrar cambios
            $usuario = $idusuario ?? ($_SESSION['idusuario'] ?? null);
            if ($usuario) {
                // Cambio de estado de la campaña
                $this->registroCambioModel->registrarCambio(
                    $usuario,
                    $idcampania,
                    'campania',
                    'actualizacion',
                    'estado',
                    $campaniaAntes['estado'] ?? null,
                    'activa',
                    'Campaña iniciada: ' . $campaniaAntes['nombre']
                );

                // Cambio de estado de las programaciones
                $this->registroCambioModel->registrarCambio(
                    $usuario,
                    $idcampania,
                    'programacion_envios',
                    'actualizacion',
                    'estado',
                    'creada',
                    'activa',
                    "Programaciones de campaña '{$campaniaAntes['nombre']}' activadas"
                );
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Error al iniciar campaña: " . $e->getMessage());
        }
    }

    public function finalizarCampania($idcampania, $idusuario = null)
    {
        try {
            $this->pdo->beginTransaction();

            $campaniaAntes = $this->obtenerCampania($idcampania);
            if (!$campaniaAntes) {
                throw new Exception("La campaña no existe.");
            }

            // 1️⃣ Finalizar campaña
            $sql = "UPDATE campanias
                SET estado = 'finalizada',
                    fecha_fin = NOW()
                WHERE idcampania = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$idcampania]);

            // 2️⃣ Desactivar todas las programaciones relacionadas
            $sqlProg = "UPDATE programacion_envios
                    SET estado = 'inactiva'
                    WHERE idcampania = ?";
            $stmtProg = $this->pdo->prepare($sqlProg);
            $stmtProg->execute([$idcampania]);

            // 3️⃣ Registrar cambio
            $usuario = $idusuario ?? ($_SESSION['idusuario'] ?? null);
            if ($usuario) {
                $this->registroCambioModel->registrarCambio(
                    $usuario,
                    $idcampania,
                    'campania',
                    'actualizacion',
                    'estado',
                    $campaniaAntes['estado'] ?? null,
                    'finalizada',
                    "Campaña finalizada: " . $campaniaAntes['nombre']
                );

                $this->registroCambioModel->registrarCambio(
                    $usuario,
                    $idcampania,
                    'programacion_envios',
                    'actualizacion',
                    'estado',
                    'activa',
                    'inactiva',
                    "Programaciones de campaña " . $campaniaAntes['nombre'] . " desactivadas"
                );
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Error al finalizar campaña: " . $e->getMessage());
        }
    }

    public function obtenerEnviosPorCampania($idcampania)
    {
        $sql = "SELECT pe.*, p.nombre AS plantilla_nombre, p.descripcion AS plantilla_descripcion
            FROM programacion_envios pe
            INNER JOIN plantillas p ON p.idplantilla = pe.idplantilla
            WHERE pe.idcampania = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idcampania]);
        $programaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($programaciones as &$prog) {
            $prog['receptores'] = $this->obtenerReceptoresPorEnvio($prog['idenvio']);
            $prog['plantilla'] = $this->plantillaModel->obtenerPlantilla($prog['idplantilla']);
        }

        return $programaciones;
    }

    public function obtenerReceptoresPorEnvio($idenvio)
    {
        $sql = "SELECT c.*
            FROM envios_receptores ec
            INNER JOIN clientes c ON c.idcliente = ec.idreceptor
            WHERE ec.idenvio = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idenvio]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ==========================
 * PROGRAMACIÓN DE ENVÍOS
 * ========================== */
    public function obtenerProgramacionesPendientes()
    {
        $sql = "SELECT 
                pe.*,
                c.fecha_inicio,
                c.estado,
                c.nombre AS nombre_campania
            FROM programacion_envios pe
            INNER JOIN campanias c ON c.idcampania = pe.idcampania
            WHERE c.estado = 'activa'
              AND pe.estado != 'enviada'";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $programaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($programaciones as &$prog) {
            $prog['receptores'] = $this->obtenerReceptoresPorEnvio($prog['idenvio']);
            $prog['plantilla'] = $this->plantillaModel->obtenerPlantilla($prog['idplantilla']);
        }
        return $programaciones;
    }

    public function obtenerProgramacion($id)
    {
        $sql = "SELECT 
                pe.*,
                c.fecha_inicio,
                c.estado,
                c.nombre AS nombre_campania
            FROM programacion_envios pe
            INNER JOIN campanias c ON c.idcampania = pe.idcampania
            WHERE c.estado = 'activa'
              AND pe.estado != 'enviada'
              AND pe.idenvio = ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $prog = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        $prog['receptores'] = $this->obtenerReceptoresPorEnvio($prog['idenvio']);
        $prog['plantilla'] = $this->plantillaModel->obtenerPlantilla($prog['idplantilla']);

        return $prog;
    }

    public function programarEnvio($data)
    {
        $idemisor = $data['idemisor'] ?? $this->obtenerEmisorAleatorio();

        $sql = "INSERT INTO programacion_envios (idemisor, idcampania, idplantilla, hora_envio, dias_despues, dia_semana, estado, idusuario) 
            VALUES (:idemisor, :idcampania, :idplantilla, :hora_envio, :dias_despues, :dia_semana, :estado, :idusuario)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':idemisor' => $idemisor,
            ':idcampania' => $data['idcampania'] ?? null,
            ':idplantilla' => $data['idplantilla'],
            ':hora_envio' => $data['hora_envio'],
            ':dias_despues' => $data['dias_despues'],
            ':dia_semana' => $data['dia_semana'],
            ':estado' => $data['estado'],
            ':idusuario' => $data['idusuario']
        ]);

        $idenvio = $this->pdo->lastInsertId();

        // Auditoría
        $this->registroCambioModel->registrarCambio(
            $data['idusuario'] ?? $_SESSION['idusuario'],
            $idenvio,
            'envio',
            'creacion',
            null,
            null,
            null,
            "Programación de envío creada"
        );

        return $idenvio;
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

    public function actualizarRelacionesEnvioClientes($idenvio, $clientes = [])
    {
        $stmtDel = $this->pdo->prepare("DELETE FROM envios_receptores WHERE idenvio = ?");
        $stmtDel->execute([$idenvio]);

        if (!empty($clientes)) {
            $stmtIns = $this->pdo->prepare("INSERT INTO envios_receptores (idenvio, idreceptor) VALUES (:idenvio, :idreceptor)");
            foreach ($clientes as $idreceptor) {
                $stmtIns->execute([
                    ':idenvio' => $idenvio,
                    ':idreceptor' => $idreceptor
                ]);
            }
        }
    }

    public function marcarProgramacionEnviada($idenvio, $idusuario = null)
    {
        $sql = "UPDATE programacion_envios SET estado = 'enviada', fecha_envio = NOW() WHERE idenvio = ?";
        $stmt = $this->pdo->prepare($sql);
        $res = $stmt->execute([$idenvio]);

        if ($res && isset($_SESSION['idusuario'])) {
            $this->registroCambioModel->registrarCambio(
                $idusuario ?? $_SESSION['idusuario'],
                $idenvio,
                'programacion_envios',
                'actualizacion',
                'estado',
                null,
                'enviada',
                "Programación marcada como enviada (idenvio: $idenvio)"
            );
        }
        return $res;
    }

    public function marcarProgramacionError($idenvio, $info = null, $idusuario = null)
    {
        $sql = "UPDATE programacion_envios SET estado = 'error', fecha_envio = NOW() WHERE idenvio = ?";
        $stmt = $this->pdo->prepare($sql);
        $res = $stmt->execute([$idenvio]);

        if ($res && isset($_SESSION['idusuario'])) {
            $this->registroCambioModel->registrarCambio(
                $idusuario ?? $_SESSION['idusuario'],
                $idenvio,
                'programacion_envios',
                'error',
                null,
                null,
                null,
                "Error enviando programación (idenvio: $idenvio) - " . json_encode($info)
            );
        }
        return $res;
    }

    public function actualizarEnvioPorReceptor($idenvio, $idreceptor, $nuevoEstado)
    {
        try {
            if (empty($idenvio) || empty($idreceptor) || empty($nuevoEstado)) {
                throw new Exception("Parámetros insuficientes para actualizar el estado del envío");
            }

            $sql = "UPDATE envios_receptores
                SET estado = :estado,
                    fecha_envio = NOW()
                WHERE idenvio = :idenvio AND idreceptor = :idreceptor";

            $stmt = $this->pdo->prepare($sql);
            $res = $stmt->execute([
                ':estado' => $nuevoEstado,
                ':idenvio' => $idenvio,
                ':idreceptor' => $idreceptor
            ]);

            // Si hay registro de cambios habilitado
            if ($res && $this->registroCambioModel && isset($_SESSION['idusuario'])) {
                $this->registroCambioModel->registrarCambio(
                    $_SESSION['idusuario'],
                    $idenvio,
                    'envios_receptores',
                    'actualizacion',
                    'estado',
                    null,
                    $nuevoEstado,
                    "Estado actualizado a '{$nuevoEstado}' para receptor {$idreceptor} en envío {$idenvio}"
                );
            }

            return $res;
        } catch (Exception $e) {
            throw new Exception("Error al actualizar estado del envío: " . $e->getMessage());
        }
    }

    /* ==========================
     * ESTADOS
     * ========================== */
    public function obtenerEstadosEnvio()
    {
        return $this->pdo->query("SELECT * FROM estados_envios ORDER BY idestado ASC")->fetchAll(PDO::FETCH_ASSOC);
    }
}
