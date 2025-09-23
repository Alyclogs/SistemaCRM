<?php
require_once __DIR__ . "/../../models/actividades/ActividadModel.php";

header('Content-Type: application/json; charset=utf-8');

$response = ["success" => false, "message" => "Acción no válida"];

try {
    $actividadModel = new ActividadModel();

    if (isset($_GET['action'])) {
        switch ($_GET['action']) {

            case 'listar':
                if (isset($_GET['idcliente'])) {
                    $data = $actividadModel->obtenerActividadesPorCliente($_GET['idcliente']);
                    $response = $data;
                } elseif (isset($_GET['idusuario'])) {
                    $data = $actividadModel->obtenerActividadesPorUsuario($_GET['idusuario']);
                    $response = $data;
                } else {
                    $data = $actividadModel->obtenerActividades();
                    $response = $data;
                }
                break;

            case 'ver':
                if (!isset($_GET['id'])) throw new Exception("ID requerido");
                $data = $actividadModel->obtenerActividad($_GET['id']);
                $response = $data;
                break;

            case 'listar_por_cliente':
                if (!isset($_GET['idcliente'])) throw new Exception("ID de cliente requerido");
                $data = $actividadModel->obtenerActividadesPorCliente($_GET['idcliente']);
                $response = $data;
                break;

            case 'crear':
                $data = $_POST;
                if (isset($_POST['extra']) && is_string($_POST['extra'])) {
                    $data['extra'] = json_decode($_POST['extra'], true);
                }
                $id = $actividadModel->crearActividad($data);
                $response = [
                    "success" => true,
                    "message" => "Actividad creada",
                    "id" => $id
                ];
                break;

            case 'actualizar':
                if (!isset($_POST['idactividad'])) throw new Exception("ID requerido");
                $data = $_POST;
                if (isset($_POST['extra']) && is_string($_POST['extra'])) {
                    $data['extra'] = json_decode($_POST['extra'], true);
                }
                $actividadModel->actualizarActividad($_POST['idactividad'], $data);
                $response = [
                    "success" => true,
                    "message" => "Actividad actualizada"
                ];
                break;

            case 'eliminar':
                if (!isset($_POST['idactividad'])) throw new Exception("ID requerido");
                $actividadModel->eliminarActividad($_POST['idactividad']);
                $response = [
                    "success" => true,
                    "message" => "Actividad eliminada"
                ];
                break;

            case 'listar_notas':
                if (!isset($_GET['idactividad'])) throw new Exception("ID de actividad requerido");
                $data = $actividadModel->obtenerNotasActividad($_GET['idactividad']);
                $response = [
                    "success" => true,
                    "message" => "Notas obtenidas",
                    "data" => $data
                ];
                break;

            case 'guardar_nota':
                if (!isset($_POST['idactividad'])) throw new Exception("ID de actividad requerido");
                $actividadModel->guardarNotaActividad($_POST['idactividad'], $_POST['idusuario'], $_POST['nota'] ?? "");
                $response = [
                    "success" => true,
                    "message" => "Nota guardada"
                ];
                break;
        }
    }
} catch (Exception $e) {
    $response = ["success" => false, "message" => $e->getMessage()];
}

echo json_encode($response);
