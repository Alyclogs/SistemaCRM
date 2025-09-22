<?php
require_once __DIR__ . "/../../config/database.php";

class ActividadModel
{
    private $pdo;

    public function __construct()
    {
        try {
            $this->pdo = connectDatabase();
        } catch (PDOException $e) {
            die("Error al conectar en ActividadModel: " . $e->getMessage());
        }
    }

    public function obtenerActividades()
    {
        try {
            $sql = "SELECT * FROM actividades ORDER BY fecha DESC, hora_inicio ASC";
            $stmt = $this->pdo->query($sql);
            $actividades = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Agregar notas a cada actividad
            foreach ($actividades as &$actividad) {
                $actividad['notas'] = $this->obtenerNotasActividad($actividad['idactividad']);
                if (!empty($actividad['extra'])) {
                    $actividad['extra'] = json_decode($actividad['extra'], true);
                }
            }

            return $actividades;
        } catch (Exception $e) {
            throw new Exception("Error al obtener actividades: " . $e->getMessage());
        }
    }

    public function obtenerActividad($id)
    {
        try {
            $sql = "SELECT * FROM actividades WHERE idactividad = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            $actividad = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($actividad) {
                $actividad['notas'] = $this->obtenerNotasActividad($id);
                if (!empty($actividad['extra'])) {
                    $actividad['extra'] = json_decode($actividad['extra'], true);
                }
            }

            return $actividad;
        } catch (Exception $e) {
            throw new Exception("Error al obtener actividad: " . $e->getMessage());
        }
    }

    public function obtenerActividadesPorCliente($idcliente)
    {
        try {
            $sql = "SELECT * FROM actividades WHERE idcliente = ? ORDER BY fecha DESC, hora_inicio ASC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$idcliente]);
            $actividades = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($actividades as &$actividad) {
                $actividad['notas'] = $this->obtenerNotasActividad($actividad['idactividad']);
                if (!empty($actividad['extra'])) {
                    $actividad['extra'] = json_decode($actividad['extra'], true);
                }
            }

            return $actividades;
        } catch (Exception $e) {
            throw new Exception("Error al obtener actividades por cliente: " . $e->getMessage());
        }
    }

    public function crearActividad($data)
    {
        try {
            $sql = "INSERT INTO actividades (nombre, idcliente, idusuario, fecha, hora_inicio, hora_fin, tipo, extra) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data['nombre'],
                $data['idcliente'] ?? null,
                $data['idusuario'] ?? null,
                $data['fecha'],
                $data['hora_inicio'],
                $data['hora_fin'],
                $data['tipo'],
                !empty($data['extra']) ? json_encode($data['extra']) : null
            ]);

            $idactividad = $this->pdo->lastInsertId();

            // Si se envía nota inicial
            if (!empty($data['nota'])) {
                $this->guardarNotaActividad($idactividad, $data['nota']);
            }

            return $idactividad;
        } catch (Exception $e) {
            throw new Exception("Error al crear actividad: " . $e->getMessage());
        }
    }

    public function actualizarActividad($id, $data)
    {
        try {
            $sql = "UPDATE actividades 
                    SET nombre=?, idcliente=?, idusuario=?, fecha=?, hora_inicio=?, hora_fin=?, tipo=?, extra=? 
                    WHERE idactividad=?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data['nombre'],
                $data['idcliente'] ?? null,
                $data['idusuario'] ?? null,
                $data['fecha'],
                $data['hora_inicio'],
                $data['hora_fin'],
                $data['tipo'],
                !empty($data['extra']) ? json_encode($data['extra']) : null,
                $id
            ]);

            // Si se actualiza nota
            if (isset($data['nota'])) {
                $this->guardarNotaActividad($id, $data['nota']);
            }

            return true;
        } catch (Exception $e) {
            throw new Exception("Error al actualizar actividad: " . $e->getMessage());
        }
    }

    public function eliminarActividad($id)
    {
        try {
            // Borrar notas relacionadas
            $sqlNotas = "DELETE FROM notas WHERE idreferencia = ? AND tipo = 'actividad'";
            $stmtNotas = $this->pdo->prepare($sqlNotas);
            $stmtNotas->execute([$id]);

            // Borrar actividad
            $sql = "DELETE FROM actividades WHERE idactividad = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            throw new Exception("Error al eliminar actividad: " . $e->getMessage());
        }
    }

    public function obtenerNotasActividad($idactividad)
    {
        try {
            $sql = "SELECT * FROM notas WHERE idreferencia = ? AND tipo = 'actividad'";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$idactividad]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error al obtener notas de la actividad: " . $e->getMessage());
        }
    }

    public function guardarNotaActividad($idactividad, $contenido)
    {
        try {
            // Si está vacía, eliminar nota
            if (trim($contenido) === "") {
                $sql = "DELETE FROM notas WHERE idreferencia = ? AND tipo = 'actividad'";
                $stmt = $this->pdo->prepare($sql);
                return $stmt->execute([$idactividad]);
            }

            // Si ya existe, actualizar
            $sqlCheck = "SELECT idnota FROM notas WHERE idreferencia = ? AND tipo = 'actividad'";
            $stmtCheck = $this->pdo->prepare($sqlCheck);
            $stmtCheck->execute([$idactividad]);
            $idnota = $stmtCheck->fetchColumn();

            if ($idnota) {
                $sql = "actualizar notas SET contenido=? WHERE idnota=?";
                $stmt = $this->pdo->prepare($sql);
                return $stmt->execute([$contenido, $idnota]);
            } else {
                $sql = "INSERT INTO notas (idreferencia, tipo, contenido) VALUES (?, 'actividad', ?)";
                $stmt = $this->pdo->prepare($sql);
                return $stmt->execute([$idactividad, $contenido]);
            }
        } catch (Exception $e) {
            throw new Exception("Error al guardar nota de actividad: " . $e->getMessage());
        }
    }
}
