<?php
require_once __DIR__ . "/../../config/database.php";

class ColaboradorModel
{
    private $pdo;

    public function __construct()
    {
        try {
            $this->pdo = connectDatabase();
        } catch (PDOException $e) {
            die("Error al conectar en ColaboradorModel: " . $e->getMessage());
        }
    }

    public function obtenerColaboradores()
    {
        try {
            $sql = "SELECT * FROM colaboradores";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error al obtener colaboradores: " . $e->getMessage());
        }
    }

    public function obtenerColaborador($id)
    {
        try {
            $sql = "SELECT * FROM colaboradores WHERE idcolaborador = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error al obtener colaborador: " . $e->getMessage());
        }
    }

    public function crearColaborador($data)
    {
        try {
            $sql = "INSERT INTO colaboradores (nombres, apellidos, dni, telefono, correo, idestado) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data['nombres'],
                $data['apellidos'],
                $data['dni'],
                $data['telefono'],
                $data['correo'],
                $data['idestado']
            ]);
            return $this->pdo->lastInsertId();
        } catch (Exception $e) {
            throw new Exception("Error al crear colaborador: " . $e->getMessage());
        }
    }

    public function actualizarColaborador($id, $data)
    {
        try {
            $sql = "UPDATE colaboradores 
                    SET nombres=?, apellidos=?, dni=?, telefono=?, correo=?, idestado=? 
                    WHERE idcolaborador=?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                $data['nombres'],
                $data['apellidos'],
                $data['dni'],
                $data['telefono'],
                $data['correo'],
                $data['idestado'],
                $id
            ]);
        } catch (Exception $e) {
            throw new Exception("Error al actualizar colaborador: " . $e->getMessage());
        }
    }

    public function eliminarColaborador($id)
    {
        try {
            $sql = "DELETE FROM colaboradores WHERE idcolaborador = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            throw new Exception("Error al eliminar colaborador: " . $e->getMessage());
        }
    }
}
