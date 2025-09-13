<?php
require_once __DIR__ . "/../../models/tareas/TareaModel.php";

header('Content-Type: application/json; charset=utf-8');

$response = ["success" => false, "message" => "Acción no válida"];

try {
    $tareaModel = new TareaModel();

    if (isset($_GET['action'])) {
        switch ($_GET['action']) {

            case 'listar':
                if (!isset($_GET['idproyecto'])) throw new Exception("ID de proyecto requerido");
                $data = $tareaModel->obtenerTareasPorProyecto($_GET['idproyecto']);
                $response = ["success" => true, "message" => "Tareas obtenidas correctamente", "data" => $data];
                break;

            case 'ver':
                if (!isset($_GET['id'])) throw new Exception("ID de tarea requerido");
                $data = $tareaModel->obtenerTarea($_GET['id']);
                $response = ["success" => true, "message" => "Tarea obtenida", "data" => $data];
                break;

            case 'crear':
                $colaboradores = [];
                if (isset($_POST['colaboradores'])) {
                    $colaboradores = json_decode($_POST['colaboradores'], true);
                }
                $id = $tareaModel->crearTarea($_POST, $colaboradores);
                $response = ["success" => true, "message" => "Tarea creada", "id" => $id];
                break;

            case 'actualizar':
                if (!isset($_POST['idtarea'])) throw new Exception("ID de tarea requerido");
                $colaboradores = [];
                if (isset($_POST['colaboradores'])) {
                    $colaboradores = json_decode($_POST['colaboradores'], true);
                }
                $tareaModel->actualizarTarea($_POST['idtarea'], $_POST, $colaboradores);
                $response = ["success" => true, "message" => "Tarea actualizada"];
                break;

            case 'eliminar':
                if (!isset($_POST['idtarea'])) throw new Exception("ID de tarea requerido");
                $tareaModel->eliminarTarea($_POST['idtarea']);
                $response = ["success" => true, "message" => "Tarea eliminada"];
                break;

            case 'asignar_colaboradores':
                if (!isset($_POST['idtarea'])) throw new Exception("ID de tarea requerido");
                if (!isset($_POST['colaboradores'])) throw new Exception("Array de colaboradores requerido");

                $colaboradores = json_decode($_POST['colaboradores'], true);
                if (!is_array($colaboradores)) {
                    throw new Exception("Formato inválido, colaboradores debe ser un array JSON");
                }

                // Reemplaza los colaboradores actuales
                $tareaModel->actualizarTarea($_POST['idtarea'], $_POST, $colaboradores);
                $response = ["success" => true, "message" => "Colaboradores asignados a la tarea"];
                break;

            case 'agregar_colaboradores':
                if (!isset($_POST['idtarea'])) throw new Exception("ID de tarea requerido");
                if (!isset($_POST['colaboradores'])) throw new Exception("Array de colaboradores requerido");

                $colaboradores = json_decode($_POST['colaboradores'], true);
                if (!is_array($colaboradores)) {
                    throw new Exception("Formato inválido, colaboradores debe ser un array JSON");
                }

                $tareaModel->agregarColaboradores($_POST['idtarea'], $colaboradores);
                $response = ["success" => true, "message" => "Colaboradores agregados a la tarea"];
                break;

            case 'remover_colaboradores':
                if (!isset($_POST['idtarea'])) throw new Exception("ID de tarea requerido");
                if (!isset($_POST['colaboradores'])) throw new Exception("Array de colaboradores requerido");

                $colaboradores = json_decode($_POST['colaboradores'], true);
                if (!is_array($colaboradores)) {
                    throw new Exception("Formato inválido, colaboradores debe ser un array JSON");
                }

                $tareaModel->removerColaboradores($_POST['idtarea'], $colaboradores);
                $response = ["success" => true, "message" => "Colaboradores removidos de la tarea"];
                break;
        }
    }
} catch (Exception $e) {
    $response = ["success" => false, "message" => $e->getMessage()];
}

echo json_encode($response);
