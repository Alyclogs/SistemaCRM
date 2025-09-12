<?php
require_once __DIR__ . "/../../config/database.php";

class ProyectoModel
{
    private $pdo;

    public function __construct()
    {
        try {
            $this->pdo = connectDatabase();
        } catch (PDOException $e) {
            die("Error al conectar en ProyectoModel: " . $e->getMessage());
        }
    }

    public function obtenerProyectos()
    {
        try {
            $sql = "SELECT * FROM proyectos";
            $stmt = $this->pdo->query($sql);
            $proyectos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Agregar colaboradores a cada proyecto
            foreach ($proyectos as &$proyecto) {
                $proyecto['colaboradores'] = $this->obtenerColaboradoresPorProyecto($proyecto['idproyecto']);
            }

            return $proyectos;
        } catch (Exception $e) {
            throw new Exception("Error al obtener proyectos: " . $e->getMessage());
        }
    }

    public function obtenerProyecto($id)
    {
        try {
            $sql = "SELECT * FROM proyectos WHERE idproyecto = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            $proyecto = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($proyecto) {
                $proyecto['colaboradores'] = $this->obtenerColaboradoresPorProyecto($id);
            }

            return $proyecto;
        } catch (Exception $e) {
            throw new Exception("Error al obtener proyecto: " . $e->getMessage());
        }
    }

    public function crearProyecto($data, $colaboradores = [])
    {
        try {
            $sql = "INSERT INTO proyectos (nombre, fecha_inicio, idestado, descripcion, presupuesto, prioridad) 
                    VALUES (?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data['nombre'],
                $data['fecha_inicio'],
                $data['idestado'],
                $data['descripcion'],
                $data['presupuesto'],
                $data['prioridad']
            ]);
            $idproyecto = $this->pdo->lastInsertId();

            // Insertar colaboradores si los hay
            if (!empty($colaboradores)) {
                $this->asignarColaboradores($idproyecto, $colaboradores);
            }

            return $idproyecto;
        } catch (Exception $e) {
            throw new Exception("Error al crear proyecto: " . $e->getMessage());
        }
    }

    public function actualizarProyecto($id, $data, $colaboradores = [])
    {
        try {
            $sql = "UPDATE proyectos 
                    SET nombre=?, fecha_inicio=?, idestado=?, descripcion=?, presupuesto=?, prioridad=?
                    WHERE idproyecto=?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data['fecha_inicio'],
                $data['idestado'],
                $data['descripcion'],
                $id
            ]);

            // Actualizar colaboradores
            $sqlDel = "DELETE FROM proyectos_colaboradores WHERE idproyecto=?";
            $stmtDel = $this->pdo->prepare($sqlDel);
            $stmtDel->execute([$id]);

            if (!empty($colaboradores)) {
                $this->asignarColaboradores($id, $colaboradores);
            }

            return true;
        } catch (Exception $e) {
            throw new Exception("Error al actualizar proyecto: " . $e->getMessage());
        }
    }

    public function eliminarProyecto($id)
    {
        try {
            $sql = "DELETE FROM proyectos WHERE idproyecto = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            throw new Exception("Error al eliminar proyecto: " . $e->getMessage());
        }
    }

    private function asignarColaboradores($idproyecto, $colaboradores)
    {
        try {
            $sql = "INSERT INTO proyectos_colaboradores (idproyecto, idcolaborador) VALUES (?, ?)";
            $stmt = $this->pdo->prepare($sql);
            foreach ($colaboradores as $idcolaborador) {
                $stmt->execute([$idproyecto, $idcolaborador]);
            }
        } catch (Exception $e) {
            throw new Exception("Error al asignar colaboradores: " . $e->getMessage());
        }
    }

    private function obtenerColaboradoresPorProyecto($idproyecto)
    {
        try {
            $sql = "SELECT c.* 
                    FROM proyectos_colaboradores pc
                    INNER JOIN colaboradores c ON pc.idcolaborador = c.idcolaborador
                    WHERE pc.idproyecto = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$idproyecto]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error al obtener colaboradores del proyecto: " . $e->getMessage());
        }
    }
}
