<?php
require_once __DIR__ . "/../../models/ProyectoModel.php";

header('Content-Type: application/json; charset=utf-8');

$response = ["success" => false, "message" => "Acción no válida"];

try {
    $proyectoModel = new ProyectoModel();

    if (isset($_GET['action'])) {
        switch ($_GET['action']) {

            case 'listar':
                $data = $proyectoModel->obtenerProyectos();
                $response = ["success" => true, "message" => "Proyectos obtenidos correctamente", "data" => $data];
                break;

            case 'ver':
                if (!isset($_GET['id'])) throw new Exception("ID requerido");
                $data = $proyectoModel->obtenerProyecto($_GET['id']);
                $response = ["success" => true, "message" => "Proyecto obtenido", "data" => $data];
                break;

            case 'crear':
                $colaboradores = [];
                if (isset($_POST['colaboradores'])) {
                    $colaboradores = json_decode($_POST['colaboradores'], true);
                }
                $id = $proyectoModel->crearProyecto($_POST, $colaboradores);
                $response = ["success" => true, "message" => "Proyecto creado", "id" => $id];
                break;

            case 'actualizar':
                if (!isset($_POST['idproyecto'])) throw new Exception("ID requerido");
                $colaboradores = [];
                if (isset($_POST['colaboradores'])) {
                    $colaboradores = json_decode($_POST['colaboradores'], true);
                }
                $proyectoModel->actualizarProyecto($_POST['idproyecto'], $_POST, $colaboradores);
                $response = ["success" => true, "message" => "Proyecto actualizado"];
                break;

            case 'asignar_colaboradores':
                if (!isset($_POST['idproyecto'])) throw new Exception("ID de proyecto requerido");
                if (!isset($_POST['colaboradores'])) throw new Exception("Array de colaboradores requerido");

                $colaboradores = json_decode($_POST['colaboradores'], true);
                if (!is_array($colaboradores)) {
                    throw new Exception("Formato de colaboradores inválido, debe ser un array JSON");
                }
                $proyectoModel->actualizarProyecto($_POST['idproyecto'], $_POST, $colaboradores);

                $response = ["success" => true, "message" => "Colaboradores asignados al proyecto"];
                break;

            case 'eliminar':
                if (!isset($_POST['idproyecto'])) throw new Exception("ID requerido");
                $proyectoModel->eliminarProyecto($_POST['idproyecto']);
                $response = ["success" => true, "message" => "Proyecto eliminado"];
                break;
        }
    }
} catch (Exception $e) {
    $response = ["success" => false, "message" => $e->getMessage()];
}

echo json_encode($response);
