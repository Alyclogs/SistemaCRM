<?php
require_once __DIR__ . "/../../models/colaboradores/ColaboradorModel.php";

header('Content-Type: application/json; charset=utf-8');

$response = ["success" => false, "message" => "Acción no válida"];

try {
    $colaboradorModel = new ColaboradorModel();

    if (isset($_GET['action'])) {
        switch ($_GET['action']) {

            case 'listar':
                $data = $colaboradorModel->obtenerColaboradores();
                $response = ["success" => true, "message" => "Colaboradores obtenidos correctamente", "data" => $data];
                break;

            case 'ver':
                if (!isset($_GET['id'])) throw new Exception("ID requerido");
                $data = $colaboradorModel->obtenerColaborador($_GET['id']);
                $response = ["success" => true, "message" => "Colaborador obtenido", "data" => $data];
                break;

            case 'crear':
                $id = $colaboradorModel->crearColaborador($_POST);
                $response = ["success" => true, "message" => "Colaborador creado", "id" => $id];
                break;

            case 'actualizar':
                if (!isset($_POST['idcolaborador'])) throw new Exception("ID requerido");
                $colaboradorModel->actualizarColaborador($_POST['idcolaborador'], $_POST);
                $response = ["success" => true, "message" => "Colaborador actualizado"];
                break;

            case 'eliminar':
                if (!isset($_POST['idcolaborador'])) throw new Exception("ID requerido");
                $colaboradorModel->eliminarColaborador($_POST['idcolaborador']);
                $response = ["success" => true, "message" => "Colaborador eliminado"];
                break;
        }
    }
} catch (Exception $e) {
    $response = ["success" => false, "message" => $e->getMessage()];
}

echo json_encode($response);
