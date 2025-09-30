<?php
require_once __DIR__ . "/../../models/ajustes/AjustesModel.php";
require_once __DIR__ . "/../../models/cambios/RegistroCambio.php";

header('Content-Type: application/json; charset=utf-8');

$response = ["success" => false, "message" => "Acción no válida"];

try {
    $pdo = Database::getConnection();
    $registroCambioModel = new RegistroCambioModel($pdo);
    $ajustesModel = new AjustesModel($pdo, $registroCambioModel);

    if (isset($_GET['action'])) {
        switch ($_GET['action']) {

            case 'listar_campos':
                if (isset($_GET['tabla'])) {
                    $data = $ajustesModel->obtenerCamposPorTabla($_GET['tabla']);
                    $response = $data;
                    exit;
                }
                $data = $ajustesModel->obtenerCampos();
                $response = $data;
                break;

            case 'listar_campos_extra':
                if (isset($_GET['tabla'])) {
                    $data = $ajustesModel->obtenerCamposExtraPorTabla($_GET['idreferencia'] ?? null, $_GET['tabla']);
                    $response = $data;
                    exit;
                }
                $data = $ajustesModel->obtenerCamposExtra();
                $response = $data;
                break;

            case 'ver_campo':
                if (!isset($_GET['idcampo'])) throw new Exception("ID requerido");
                $data = $ajustesModel->obtenerCampoExtra($_GET['idcampo']);
                $response = $data;
                break;

            case 'crear_campo':
                $id = $ajustesModel->crearCampoExtra($_POST);
                $response = [
                    "success" => true,
                    "message" => "Campo extra creado",
                    "id" => $id
                ];
                break;

            case 'actualizar_campo':
                if (!isset($_POST['idcampo'])) throw new Exception("ID requerido");
                $ajustesModel->actualizarCampoExtra($_POST['idcampo'], $_POST);
                $response = [
                    "success" => true,
                    "message" => "Campo extra actualizado"
                ];
                break;

            case 'eliminar_campo':
                if (!isset($_POST['idcampo'])) throw new Exception("ID requerido");
                $ajustesModel->eliminarCampo($_POST['idcampo']);
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
