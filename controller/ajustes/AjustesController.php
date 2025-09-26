<?php
require_once __DIR__ . "/../../models/ajustes/AjustesModel.php";

header('Content-Type: application/json; charset=utf-8');

$response = ["success" => false, "message" => "Acción no válida"];

try {
    $ajustesModel = new AjustesModel();

    if (isset($_GET['action'])) {
        switch ($_GET['action']) {

            /* =======================
             * CAMPOS EXTRA
             * ======================= */
            case 'listar_campos':
                if (isset($_GET['tipo']) && isset($_GET['idreferencia'])) {
                    $data = $ajustesModel->obtenerCamposPorTipo($_GET['idreferencia'], $_GET['tipo']);
                    $response = $data;
                    exit;
                }
                $data = $ajustesModel->obtenerCampos();
                $response = $data;
                break;

            case 'ver_campo':
                if (!isset($_GET['idcampo'])) throw new Exception("ID requerido");
                $data = $ajustesModel->obtenerCampo($_GET['idcampo']);
                $response = $data;
                break;

            case 'crear_campo':
                $id = $ajustesModel->crearCampo($_POST, $_POST['idusuario'] ?? null);
                $response = [
                    "success" => true,
                    "message" => "Campo extra creado",
                    "id" => $id
                ];
                break;

            case 'actualizar_campo':
                if (!isset($_POST['idcampo'])) throw new Exception("ID requerido");
                $ajustesModel->actualizarCampo($_POST['idcampo'], $_POST, $_POST['idusuario'] ?? null);
                $response = [
                    "success" => true,
                    "message" => "Campo extra actualizado"
                ];
                break;

            case 'eliminar_campo':
                if (!isset($_POST['idcampo'])) throw new Exception("ID requerido");
                $ajustesModel->eliminarCampo($_POST['idcampo'], $_POST['idusuario'] ?? null);
                $response = [
                    "success" => true,
                    "message" => "Campo extra eliminado"
                ];
                break;


            /* =======================
             * DISPONIBILIDAD GENERAL
             * ======================= */
            case 'listar_disponibilidad':
                if (!isset($_POST['iddisponibilidad'])) throw new Exception("ID requerido");
                $data = $ajustesModel->obtenerDisponibilidad($_POST['iddisponibilidad']);
                $response = $data;
                break;

            case 'crear_disponibilidad':
                $id = $ajustesModel->crearDisponibilidad($_POST, $_POST['idusuario'] ?? null);
                $response = [
                    "success" => true,
                    "message" => "Disponibilidad creada",
                    "id" => $id
                ];
                break;

            case 'actualizar_disponibilidad':
                if (!isset($_POST['iddisponibilidad'])) throw new Exception("ID requerido");
                $ajustesModel->actualizarDisponibilidad($_POST['iddisponibilidad'], $_POST, $_POST['idusuario'] ?? null);
                $response = [
                    "success" => true,
                    "message" => "Disponibilidad actualizada"
                ];
                break;

            case 'eliminar_disponibilidad':
                if (!isset($_POST['iddisponibilidad'])) throw new Exception("ID requerido");
                $ajustesModel->eliminarDisponibilidad($_POST['iddisponibilidad'], $_POST['idusuario'] ?? null);
                $response = [
                    "success" => true,
                    "message" => "Disponibilidad eliminada"
                ];
                break;
        }
    }
} catch (Exception $e) {
    $response = ["success" => false, "message" => $e->getMessage()];
}

echo json_encode($response);
