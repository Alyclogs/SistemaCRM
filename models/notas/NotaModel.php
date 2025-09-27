<?php
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../models/cambios/RegistroCambio.php";

class NotaModel
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function obtenerNotas($idreferencia, $tipo)
    {
        $sql = "SELECT * FROM notas WHERE idreferencia = ? AND tipo = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idreferencia, $tipo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crearNota($idreferencia, $tipo, $idusuario, $contenido)
    {
        $sql = "INSERT INTO notas (idreferencia, tipo, idusuario, contenido) VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idreferencia, $tipo, $idusuario, $contenido]);
        $idnota = $this->pdo->lastInsertId();
        return $idnota;
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
            return $this->actualizarNota($idnota, $idusuario, $contenido);
        } else {
            return $this->crearNota($idreferencia, $tipo, $idusuario, $contenido);
        }
    }

    public function actualizarNota($idnota, $idusuario, $contenido)
    {
        $sql = "UPDATE notas SET contenido = ? WHERE idnota = ? AND idusuario = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$contenido, $idnota, $idusuario]);
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
