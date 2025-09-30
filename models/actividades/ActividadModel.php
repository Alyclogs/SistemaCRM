<?php
require_once __DIR__ . "/../notas/NotaModel.php";
require_once __DIR__ . "/../cambios/RegistroCambio.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class ActividadModel
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
            throw new Exception("Error al conectar en ActividadModel: " . $e->getMessage());
        }
    }

    public function obtenerClientesPorActividad($idactividad)
    {
        try {
            $sql = "SELECT ac.id, ac.tipo_cliente, ac.idreferencia,
                        CASE 
                            WHEN ac.tipo_cliente = 'cliente' THEN CONCAT(c.nombres, ' ', c.apellidos)
                            WHEN ac.tipo_cliente = 'empresa' THEN e.razon_social
                        END AS nombre,
                        CASE 
                            WHEN ac.tipo_cliente = 'cliente' THEN c.num_doc
                            WHEN ac.tipo_cliente = 'empresa' THEN e.ruc
                        END AS documento
                    FROM actividades_clientes ac
                    LEFT JOIN clientes c ON c.idcliente = ac.idreferencia AND ac.tipo_cliente = 'cliente'
                    LEFT JOIN empresas e ON e.idempresa = ac.idreferencia AND ac.tipo_cliente = 'empresa'
                    WHERE ac.idactividad = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$idactividad]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error al obtener clientes de la actividad: " . $e->getMessage());
        }
    }

    public function asignarClientesActividad($idactividad, $clientes = [])
    {
        try {
            // 1. Obtener relaciones previas
            $sqlPrevios = "SELECT idreferencia, tipo_cliente FROM actividades_clientes WHERE idactividad = ?";
            $stmtPrevios = $this->pdo->prepare($sqlPrevios);
            $stmtPrevios->execute([$idactividad]);
            $clientesAnteriores = $stmtPrevios->fetchAll(PDO::FETCH_ASSOC) ?: [];

            // 2. Eliminar relaciones previas
            $sqlDelete = "DELETE FROM actividades_clientes WHERE idactividad = ?";
            $stmtDelete = $this->pdo->prepare($sqlDelete);
            $stmtDelete->execute([$idactividad]);

            // 3. Insertar nuevas relaciones
            if (!empty($clientes)) {
                $sqlInsert = "INSERT INTO actividades_clientes (idactividad, idreferencia, tipo_cliente) VALUES (?, ?, ?)";
                $stmtInsert = $this->pdo->prepare($sqlInsert);

                foreach ($clientes as $cliente) {
                    $stmtInsert->execute([$idactividad, $cliente['idreferencia'], $cliente['tipo_cliente']]);
                }
            }

            // 4. Registrar cambios en log
            if (isset($_SESSION['idusuario'])) {
                $this->registroCambioModel->registrarAsignaciones(
                    $_SESSION['idusuario'],
                    $idactividad,          // referencia principal
                    'actividad',           // tipo principal
                    'clientes',            // campo de relación
                    $clientesAnteriores,   // estado previo
                    $clientes              // estado nuevo
                );
            }

            return true;
        } catch (Exception $e) {
            throw new Exception("Error al asignar clientes a la actividad: " . $e->getMessage());
        }
    }

    public function obtenerActividades()
    {
        try {
            $sql = "SELECT a.*, 
                        CONCAT(u.nombres, ' ', u.apellidos) AS usuario,
                        ea.estado
                    FROM actividades a
                    INNER JOIN estados_actividades ea ON ea.idestado = a.idestado
                    INNER JOIN usuarios u ON u.idusuario = a.idusuario
                    ORDER BY a.fecha_creacion ASC";
            $stmt = $this->pdo->query($sql);
            $actividades = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($actividades as &$actividad) {
                $actividad['notas'] = $this->notaModel->obtenerNotas($actividad['idactividad'], 'actividad');
                $actividad['clientes'] = $this->obtenerClientesPorActividad($actividad['idactividad']);
                if (!empty($actividad['extra'])) {
                    $actividad['extra'] = json_decode($actividad['extra'], true);
                }
            }

            return $actividades;
        } catch (Exception $e) {
            throw new Exception("Error al obtener actividades: " . $e->getMessage());
        }
    }

    public function obtenerEstados()
    {
        try {
            $sql = "SELECT * FROM estados_actividades";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error al obtener estados de actividades: " . $e->getMessage());
        }
    }

    public function obtenerActividad($id)
    {
        try {
            $sql = "SELECT a.*,
                        CONCAT(u.nombres, ' ', u.apellidos) AS usuario,
                        ea.estado
                    FROM actividades a
                    INNER JOIN estados_actividades ea ON ea.idestado = a.idestado
                    INNER JOIN usuarios u ON u.idusuario = a.idusuario
                    WHERE a.idactividad = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            $actividad = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($actividad) {
                $actividad['notas'] = $this->obtenerNotasActividad($id);
                $actividad['clientes'] = $this->obtenerClientesPorActividad($id);
                if (!empty($actividad['extra'])) {
                    $actividad['extra'] = json_decode($actividad['extra'], true);
                }
            }

            return $actividad;
        } catch (Exception $e) {
            throw new Exception("Error al obtener actividad: " . $e->getMessage());
        }
    }

    public function crearActividad($data)
    {
        try {
            $this->pdo->beginTransaction();

            $campos = [];
            $placeholders = [];
            $params = [];

            $camposTabla = $this->registroCambioModel->obtenerCamposTabla("actividades");

            foreach ($data as $campo => $valor) {
                if (!in_array($campo, $camposTabla, true)) {
                    continue; // ignorar campos que no existen
                }

                // Excluir PK
                if ($campo === "idactividad") {
                    continue;
                }

                // Validación especial: campos nulos
                if (in_array($campo, ["descripcion", "direccion", "direccion_referencia", "enlace"])) {
                    $valor = (isset($valor) && trim($valor) !== '') ? $valor : null;
                }

                // Serializar arrays como JSON (ej. extra)
                $valor = is_array($valor) ? json_encode($valor) : $valor;

                $campos[] = $campo;
                $placeholders[] = ":$campo";
                $params[":$campo"] = $valor;
            }

            if (!empty($campos)) {
                $sql = "INSERT INTO actividades (" . implode(", ", $campos) . ") 
                    VALUES (" . implode(", ", $placeholders) . ")";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
            }

            $idactividad = $this->pdo->lastInsertId();

            // --- Relaciones con clientes
            if (!empty($data['clientes'])) {
                $clientes = is_string($data['clientes']) ? json_decode($data['clientes'], true) : $data['clientes'];
                $this->asignarClientesActividad($idactividad, $clientes, $data['idusuario']);
            }

            // --- Nota inicial
            if (!empty($data['nota'])) {
                $this->notaModel->guardarNota($idactividad, 'actividad', $data['idusuario'], $data['nota']);
            }

            // --- Registrar cambio
            $this->registroCambioModel->registrarCambio(
                $data['idusuario'],
                $idactividad,
                'actividad',
                'creacion',
                null,
                null,
                null,
                "Actividad creada: " . $data['nombre']
            );

            $this->pdo->commit();
            return $idactividad;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Error al crear actividad: " . $e->getMessage());
        }
    }


    public function actualizarActividad($id, $data)
    {
        try {
            $this->pdo->beginTransaction();

            // --- 1) Obtener actividad antes (para auditoría)
            $actividadAntes = $this->obtenerActividad($id);

            $campos = [];
            $params = [];
            $dataValidos = [];

            $camposTabla = $this->registroCambioModel->obtenerCamposTabla("actividades");

            foreach ($data as $campo => $valor) {
                if (!in_array($campo, $camposTabla, true)) {
                    continue;
                }

                // Excluir PK
                if ($campo === "idactividad") {
                    continue;
                }

                // Validación especial: campos nulos
                if (in_array($campo, ["descripcion", "direccion", "direccion_referencia", "enlace"])) {
                    $valor = (isset($valor) && trim($valor) !== '') ? $valor : null;
                }

                // Serializar arrays como JSON (ej. extra)
                $valor = is_array($valor) ? json_encode($valor) : $valor;

                $campos[] = "$campo = :$campo";
                $params[":$campo"] = $valor;
                $dataValidos[$campo] = $valor;
            }

            if (!empty($campos)) {
                $sql = "UPDATE actividades SET " . implode(", ", $campos) . " WHERE idactividad = :id";
                $params[':id'] = $id;

                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
            }

            // --- 2) Relaciones con clientes
            if (isset($data['clientes'])) {
                $clientes = is_string($data['clientes']) ? json_decode($data['clientes'], true) : $data['clientes'];
                $this->asignarClientesActividad($id, $clientes, $data['idusuario']);
            }

            // --- 3) Guardar nota asociada
            if (!empty($data['nota'])) {
                $this->notaModel->guardarNota($id, 'actividad', $data['idusuario'], $data['nota']);
            }

            // --- 4) Registrar cambios automáticos
            if (!empty($_SESSION['idusuario']) && !empty($dataValidos)) {
                $this->registroCambioModel->registrarCambiosAutomaticos(
                    $_SESSION['idusuario'],
                    $id,
                    'actividad',
                    'actualizacion',
                    $actividadAntes,
                    $dataValidos
                );
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Error al actualizar actividad: " . $e->getMessage());
        }
    }

    public function obtenerActividadesPorCliente($idcliente, $tipo_cliente = 'cliente')
    {
        try {
            $sql = "SELECT a.*,
                    CONCAT(u.nombres, ' ', u.apellidos) AS usuario,
                    ea.estado
                FROM actividades a
                INNER JOIN estados_actividades ea ON ea.idestado = a.idestado
                INNER JOIN usuarios u ON u.idusuario = a.idusuario
                INNER JOIN actividades_clientes ac ON ac.idactividad = a.idactividad
                WHERE ac.idreferencia = ? 
                ORDER BY a.fecha_creacion ASC";
            $stmt = $this->pdo->prepare($sql);

            $params = [$idcliente];
            if ($tipo_cliente) {
                $sql .= " AND ac.tipo_cliente = ?";
                $params[] = $tipo_cliente;
            }

            $stmt->execute($params);
            $actividades = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($actividades as &$actividad) {
                $actividad['notas'] = $this->obtenerNotasActividad($actividad['idactividad']);
                $actividad['clientes'] = $this->obtenerClientesPorActividad($actividad['idactividad']);
                if (!empty($actividad['extra'])) {
                    $actividad['extra'] = json_decode($actividad['extra'], true);
                }
            }

            return $actividades;
        } catch (Exception $e) {
            throw new Exception("Error al obtener actividades por cliente/empresa: " . $e->getMessage());
        }
    }

    public function obtenerActividadesPorUsuario($idusuario)
    {
        try {
            $sql = "SELECT a.*,
                    CONCAT(u.nombres, ' ', u.apellidos) AS usuario,
                    ea.estado
                FROM actividades a
                INNER JOIN estados_actividades ea ON ea.idestado = a.idestado
                INNER JOIN usuarios u ON u.idusuario = a.idusuario
                WHERE a.idusuario = ?
                ORDER BY a.fecha_creacion ASC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$idusuario]);
            $actividades = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($actividades as &$actividad) {
                $actividad['notas'] = $this->obtenerNotasActividad($actividad['idactividad']);
                $actividad['clientes'] = $this->obtenerClientesPorActividad($actividad['idactividad']);
                if (!empty($actividad['extra'])) {
                    $actividad['extra'] = json_decode($actividad['extra'], true);
                }
            }

            return $actividades;
        } catch (Exception $e) {
            throw new Exception("Error al obtener actividades por usuario: " . $e->getMessage());
        }
    }

    public function eliminarActividad($id)
    {
        try {
            // Borrar relaciones en actividades_clientes
            $sqlClientes = "DELETE FROM actividades_clientes WHERE idactividad = ?";
            $stmtClientes = $this->pdo->prepare($sqlClientes);
            $stmtClientes->execute([$id]);

            // Borrar notas relacionadas
            $sqlNotas = "DELETE FROM notas WHERE idreferencia = ? AND tipo = 'actividad'";
            $stmtNotas = $this->pdo->prepare($sqlNotas);
            $stmtNotas->execute([$id]);

            // Obtener la actividad antes de borrarla (para registrar cambios)
            $actividad = $this->obtenerActividad($id);

            // Borrar la actividad
            $sql = "DELETE FROM actividades WHERE idactividad = ?";
            $stmt = $this->pdo->prepare($sql);

            if ($actividad) {
                $this->registroCambioModel->registrarCambio(
                    $_SESSION['idusuario'],
                    $id,
                    'actividad',
                    'eliminacion',
                    null,
                    $actividad['nombre'],
                    null,
                    "Actividad eliminada: " . $actividad['nombre']
                );
            }

            $resultado = $stmt->execute([$id]);
            return $resultado;
        } catch (Exception $e) {
            throw new Exception("Error al eliminar actividad: " . $e->getMessage());
        }
    }

    public function obtenerNotasActividad($idactividad)
    {
        try {
            return $this->notaModel->obtenerNotas($idactividad, 'actividad');
        } catch (Exception $e) {
            throw new Exception("Error al obtener notas de la actividad: " . $e->getMessage());
        }
    }
}
