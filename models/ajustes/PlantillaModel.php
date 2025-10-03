<?php
class PlantillaModel
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Crear una nueva plantilla
     */
    public function crearPlantilla($data)
    {
        try {
            $sql = "INSERT INTO plantillas (nombre, contenido, tipo, idusuario, fecha_creacion) 
                    VALUES (:nombre, :contenido, :tipo, :idusuario, NOW())";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ":nombre"    => $data['nombre'],
                ":contenido" => $data['contenido'],
                ":tipo"      => $data['tipo'] ?? 'general',
                ":idusuario" => $data['idusuario']
            ]);

            return $this->pdo->lastInsertId();
        } catch (Exception $e) {
            throw new Exception("Error al crear plantilla: " . $e->getMessage());
        }
    }

    /**
     * Obtener una plantilla por ID
     */
    public function obtenerPlantilla($idplantilla)
    {
        try {
            $sql = "SELECT * FROM plantillas WHERE idplantilla = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$idplantilla]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error al obtener plantilla: " . $e->getMessage());
        }
    }

    /**
     * Listar todas las plantillas
     */
    public function obtenerPlantillas()
    {
        try {
            $sql = "SELECT * FROM plantillas ORDER BY fecha_creacion DESC";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error al obtener plantillas: " . $e->getMessage());
        }
    }

    /**
     * Editar plantilla existente
     */
    public function editarPlantilla($idplantilla, $data)
    {
        try {
            $sql = "UPDATE plantillas 
                    SET nombre = :nombre, contenido = :contenido, tipo = :tipo 
                    WHERE idplantilla = :idplantilla";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ":nombre"      => $data['nombre'],
                ":contenido"   => $data['contenido'],
                ":tipo"        => $data['tipo'] ?? 'general',
                ":idplantilla" => $idplantilla
            ]);
        } catch (Exception $e) {
            throw new Exception("Error al editar plantilla: " . $e->getMessage());
        }
    }

    /**
     * Eliminar plantilla
     */
    public function eliminarPlantilla($idplantilla)
    {
        try {
            $sql = "DELETE FROM plantillas WHERE idplantilla = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$idplantilla]);
        } catch (Exception $e) {
            throw new Exception("Error al eliminar plantilla: " . $e->getMessage());
        }
    }
}
