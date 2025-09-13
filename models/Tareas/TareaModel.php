<?php
require_once __DIR__ . "/../../config/database.php";

class TareaModel
{
    private $pdo;

    public function __construct()
    {
        try {
            $this->pdo = connectDatabase();
        } catch (PDOException $e) {
            die("Error al conectar en TareaModel: " . $e->getMessage());
        }
    }

    public function obtenerTareasPorProyecto($idproyecto)
    {
        try {
            $sql = "SELECT * FROM tareas WHERE idproyecto = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$idproyecto]);
            $tareas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($tareas as &$tarea) {
                $tarea['colaboradores'] = $this->obtenerColaboradoresPorTarea($tarea['idtarea']);
            }

            return $tareas;
        } catch (Exception $e) {
            throw new Exception("Error al obtener tareas: " . $e->getMessage());
        }
    }

    public function obtenerTarea($id)
    {
        try {
            $sql = "SELECT * FROM tareas WHERE idtarea = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            $tarea = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($tarea) {
                $tarea['colaboradores'] = $this->obtenerColaboradoresPorTarea($id);
            }

            return $tarea;
        } catch (Exception $e) {
            throw new Exception("Error al obtener tarea: " . $e->getMessage());
        }
    }

    public function crearTarea($data, $colaboradores = [])
    {
        try {
            $sql = "INSERT INTO tareas (idproyecto, fecha_inicio, nombre, descripcion, idestado) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data['idproyecto'],
                $data['fecha_inicio'],
                $data['nombre'],
                $data['descripcion'],
                $data['idestado']
            ]);

            $idtarea = $this->pdo->lastInsertId();

            if (!empty($colaboradores)) {
                $this->asignarColaboradores($idtarea, $colaboradores);
            }

            return $idtarea;
        } catch (Exception $e) {
            throw new Exception("Error al crear tarea: " . $e->getMessage());
        }
    }

    public function actualizarTarea($id, $data, $colaboradores = [])
    {
        try {
            $sql = "UPDATE tareas 
                    SET idproyecto=?, fecha_inicio=?, nombre=?, descripcion=?, idestado=? 
                    WHERE idtarea=?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data['idproyecto'],
                $data['fecha_inicio'],
                $data['nombre'],
                $data['descripcion'],
                $data['idestado'],
                $id
            ]);

            // Reemplazar colaboradores
            $sqlDel = "DELETE FROM tareas_colaboradores WHERE idtarea=?";
            $stmtDel = $this->pdo->prepare($sqlDel);
            $stmtDel->execute([$id]);

            if (!empty($colaboradores)) {
                $this->asignarColaboradores($id, $colaboradores);
            }

            return true;
        } catch (Exception $e) {
            throw new Exception("Error al actualizar tarea: " . $e->getMessage());
        }
    }

    public function eliminarTarea($id)
    {
        try {
            $sql = "DELETE FROM tareas WHERE idtarea = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            throw new Exception("Error al eliminar tarea: " . $e->getMessage());
        }
    }

    private function asignarColaboradores($idtarea, $colaboradores)
    {
        try {
            $sql = "INSERT IGNORE INTO tareas_colaboradores (idtarea, idcolaborador) VALUES (?, ?)";
            $stmt = $this->pdo->prepare($sql);

            foreach ($colaboradores as $idcolaborador) {
                $stmt->execute([$idtarea, $idcolaborador]);
            }
        } catch (Exception $e) {
            throw new Exception("Error al asignar colaboradores a la tarea: " . $e->getMessage());
        }
    }

    private function obtenerColaboradoresPorTarea($idtarea)
    {
        try {
            $sql = "SELECT c.* 
                    FROM tareas_colaboradores tc
                    INNER JOIN colaboradores c ON tc.idcolaborador = c.idcolaborador
                    WHERE tc.idtarea = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$idtarea]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error al obtener colaboradores de la tarea: " . $e->getMessage());
        }
    }

    public function agregarColaboradores($idtarea, $colaboradores)
    {
        try {
            $sql = "INSERT IGNORE INTO tareas_colaboradores (idtarea, idcolaborador) VALUES (?, ?)";
            $stmt = $this->pdo->prepare($sql);

            foreach ($colaboradores as $idcolaborador) {
                $stmt->execute([$idtarea, $idcolaborador]);
            }
            return true;
        } catch (Exception $e) {
            throw new Exception("Error al agregar colaboradores a la tarea: " . $e->getMessage());
        }
    }

    public function removerColaboradores($idtarea, $colaboradores)
    {
        try {
            $sql = "DELETE FROM tareas_colaboradores WHERE idtarea=? AND idcolaborador=?";
            $stmt = $this->pdo->prepare($sql);

            foreach ($colaboradores as $idcolaborador) {
                $stmt->execute([$idtarea, $idcolaborador]);
            }
            return true;
        } catch (Exception $e) {
            throw new Exception("Error al remover colaboradores de la tarea: " . $e->getMessage());
        }
    }
}
