<?php
require_once __DIR__ . "/../notas/NotaModel.php";

class ActividadModel
{
    private $pdo;
    private $notaModel;

    public function __construct()
    {
        $this->pdo = connectDatabase();
        $this->notaModel = new NotaModel();
    }

    public function obtenerActividad($id)
    {
        $sql = "SELECT * FROM actividades WHERE idactividad = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $actividad = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($actividad) {
            $actividad['extra'] = $actividad['extra'] ? json_decode($actividad['extra'], true) : [];
            $actividad['notas'] = $this->notaModel->obtenerNotas($id, "actividad");
        }

        return $actividad;
    }

    public function crearActividad($data)
    {
        $sql = "INSERT INTO actividades (nombre, idcliente, idusuario, fecha, hora_inicio, hora_fin, tipo, extra) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['nombre'],
            $data['idcliente'],
            $data['idusuario'],
            $data['fecha'],
            $data['hora_inicio'],
            $data['hora_fin'],
            $data['tipo'],
            !empty($data['extra']) ? json_encode($data['extra']) : null
        ]);
        $idactividad = $this->pdo->lastInsertId();

        // Guardar nota si existe
        if (!empty($data['nota'])) {
            $this->notaModel->crearNota($idactividad, "actividad", $data['nota']);
        }

        return $idactividad;
    }

    public function actualizarActividad($id, $data)
    {
        $sql = "UPDATE actividades 
                SET nombre=?, idcliente=?, idusuario=?, fecha=?, hora_inicio=?, hora_fin=?, tipo=?, extra=? 
                WHERE idactividad=?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['nombre'],
            $data['idcliente'],
            $data['idusuario'],
            $data['fecha'],
            $data['hora_inicio'],
            $data['hora_fin'],
            $data['tipo'],
            !empty($data['extra']) ? json_encode($data['extra']) : null,
            $id
        ]);

        // Manejar nota
        if (isset($data['nota'])) {
            $notas = $this->notaModel->obtenerNotas($id, "actividad");

            if (empty($data['nota'])) {
                // Si el campo está vacío -> eliminar
                foreach ($notas as $nota) {
                    $this->notaModel->eliminarNota($nota['idnota']);
                }
            } elseif (!empty($notas)) {
                // Si ya existe -> actualizar la primera
                $this->notaModel->actualizarNota($notas[0]['idnota'], $data['nota']);
            } else {
                // Si no existe -> crear
                $this->notaModel->crearNota($id, "actividad", $data['nota']);
            }
        }

        return true;
    }
}
