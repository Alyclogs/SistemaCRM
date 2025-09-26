<?php
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../notas/NotaModel.php";
require_once __DIR__ . "/../cambios/RegistroCambio.php";

class ActividadModel
{
    private $pdo;
    private $notaModel;
    private $registroCambioModel;

    public function __construct()
    {
        try {
            $this->pdo = connectDatabase();
            $this->notaModel = new NotaModel();
            $this->registroCambioModel = new RegistroCambioModel();
        } catch (PDOException $e) {
            die("Error al conectar en ActividadModel: " . $e->getMessage());
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
            // Eliminar relaciones previas
            $sqlDelete = "DELETE FROM actividades_clientes WHERE idactividad = ?";
            $stmtDelete = $this->pdo->prepare($sqlDelete);
            $stmtDelete->execute([$idactividad]);

            // Insertar nuevas relaciones
            if (!empty($clientes)) {
                $sqlInsert = "INSERT INTO actividades_clientes (idactividad, idreferencia, tipo_cliente) VALUES (?, ?, ?)";
                $stmtInsert = $this->pdo->prepare($sqlInsert);
                foreach ($clientes as $cliente) {
                    $stmtInsert->execute([$idactividad, $cliente['idreferencia'], $cliente['tipo_cliente']]);
                }
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
            $sql = "INSERT INTO actividades (nombre, idusuario, fecha, hora_inicio, hora_fin, tipo, idestado, extra) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data['nombre'],
                $data['idusuario'],
                $data['fecha'],
                $data['hora_inicio'],
                $data['hora_fin'],
                $data['tipo'],
                $data['idestado'] ?? 1,
                !empty($data['extra']) ? json_encode($data['extra']) : null
            ]);

            $idactividad = $this->pdo->lastInsertId();

            // Asignar clientes/empresas
            if (!empty($data['clientes'])) {
                $clientes = json_decode($data['clientes'], true);
                $this->asignarClientesActividad($idactividad, $clientes);
            }

            // Nota inicial
            if (!empty($data['nota'])) {
                $this->notaModel->guardarNota($idactividad, 'actividad', $data['idusuario'], $data['nota']);
            }

            $this->registroCambioModel->registrarCambio(
                $data['idusuario'],
                $idactividad,
                'actividad',
                'creacion',
                null,
                null,
                null,
                null,
                "Actividad creada: " . $data['nombre']
            );

            return $idactividad;
        } catch (Exception $e) {
            throw new Exception("Error al crear actividad: " . $e->getMessage());
        }
    }

    public function actualizarActividad($id, $data)
    {
        try {
            $sql = "UPDATE actividades 
                    SET nombre=?, idusuario=?, fecha=?, hora_inicio=?, hora_fin=?, tipo=?, idestado=?, extra=? 
                    WHERE idactividad=?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data['nombre'],
                $data['idusuario'],
                $data['fecha'],
                $data['hora_inicio'],
                $data['hora_fin'],
                $data['tipo'],
                $data['idestado'] ?? 1,
                !empty($data['extra']) ? json_encode($data['extra']) : null,
                $id
            ]);

            // Actualizar relaciones con clientes/empresas
            if (isset($data['clientes'])) {
                $clientes = json_decode($data['clientes'], true);
                $this->asignarClientesActividad($id, $clientes);
            }

            // Actualizar nota
            if (isset($data['nota'])) {
                $this->notaModel->guardarNota($id, 'actividad', $data['idusuario'], $data['nota']);
            }

            $this->registroCambioModel->registrarCambiosAutomaticos(
                $data['idusuario'],
                $id,
                'actividad',
                'actualizacion',
                $this->obtenerActividad($id),
                $data
            );

            return true;
        } catch (Exception $e) {
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
            $resultado = $stmt->execute([$id]);

            if ($actividad) {
                $this->registroCambioModel->registrarCambio(
                    $actividad['idusuario'],
                    $id,
                    'actividad',
                    'eliminacion',
                    null,
                    $actividad['nombre'],
                    null,
                    null,
                    "Actividad eliminada: " . $actividad['nombre']
                );
            }

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
