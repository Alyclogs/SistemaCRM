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

    public function guardarNota($idreferencia, $tipo, $idusuario, $contenido)
    {
        // Si está vacía, eliminar nota
        if (trim($contenido) === "") {
            return $this->eliminarNotaPorReferencia($idreferencia, $tipo, $idusuario);
        }

        // Si ya existe, actualizar
        $idnota = $this->obtenerNotaPorReferencia($idreferencia, $tipo, $idusuario);

        if ($idnota) {
            return $this->actualizarNota($idnota, $contenido);
        } else {
            return $this->crearNota($idreferencia, $tipo, $contenido);
        }
    }

    public function actualizarNota($idnota, $contenido)
    {
        $sql = "UPDATE notas SET contenido = ? WHERE idnota = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$contenido, $idnota]);
    }

    public function obtenerNotaPorReferencia($idreferencia, $tipo, $idusuario)
    {
        $stmt = $this->pdo->prepare("SELECT idnota FROM notas WHERE idreferencia = ? AND tipo = ? AND idusuario = ?");
        $stmt->execute([$idreferencia, $tipo, $idusuario]);
        $idnota = $stmt->fetchColumn();
        return $idnota ?? null;
    }

    public function eliminarNota($idnota)
    {
        $sql = "DELETE FROM notas WHERE idnota = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$idnota]);
    }

    public function eliminarNotaPorReferencia($idreferencia, $tipo, $idusuario)
    {
        $sql = "DELETE FROM notas WHERE idreferencia = ? AND tipo = ? AND idusuario = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$idreferencia, $tipo, $idusuario]);
    }
}
