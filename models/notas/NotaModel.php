<?php
require_once __DIR__ . "/../../config/database.php";

class NotaModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = connectDatabase();
    }

    public function obtenerNotas($idreferencia, $tipo)
    {
        $sql = "SELECT * FROM notas WHERE idreferencia = ? AND tipo = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idreferencia, $tipo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crearNota($idreferencia, $tipo, $contenido)
    {
        $sql = "INSERT INTO notas (idreferencia, tipo, contenido) VALUES (?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idreferencia, $tipo, $contenido]);
        return $this->pdo->lastInsertId();
    }

    public function actualizarNota($idnota, $contenido)
    {
        $sql = "UPDATE notas SET contenido = ? WHERE idnota = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$contenido, $idnota]);
    }

    public function eliminarNota($idnota)
    {
        $sql = "DELETE FROM notas WHERE idnota = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$idnota]);
    }
}
