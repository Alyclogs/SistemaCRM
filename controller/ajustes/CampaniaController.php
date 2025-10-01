<?php
require_once __DIR__ . "/../../models/ajustes/EnvioModel.php";

header('Content-Type: application/json; charset=utf-8');

$response = ["success" => false, "message" => "Acción no válida"];

try {
    $pdo = Database::getConnection();
    $envioModel = new EnvioModel($pdo);

    if (isset($_GET['action'])) {
        switch ($_GET['action']) {

            // 📌 Listar campañas
            case 'listar':
                $data = $envioModel->obtenerCampanias();
                $response = $data;
                break;

            // 📌 Ver campaña por ID
            case 'ver':
                if (!isset($_GET['idcampania'])) throw new Exception("ID de campaña requerido");
                $data = $envioModel->obtenerCampania($_GET['idcampania']);
                $response = $data;
                break;

            // 📌 Crear nueva campaña (con programaciones de envío)
            case 'crear':
                $data = $_POST;

                if (!empty($data['programaciones']) && is_string($data['programaciones'])) {
                    $data['programaciones'] = json_decode($data['programaciones'], true);
                }

                if (empty($data['idusuario'])) {
                    if (!empty($_SESSION['idusuario'])) {
                        $data['idusuario'] = $_SESSION['idusuario'];
                    } else {
                        throw new Exception("ID de usuario requerido");
                    }
                }

                $id = $envioModel->crearCampania($data);
                $response = [
                    "success" => true,
                    "message" => "Campaña creada",
                    "id" => $id
                ];
                break;

            // 📌 Actualizar campaña
            case 'actualizar':
                if (!isset($_POST['idcampania'])) throw new Exception("ID de campaña requerido");
                $data = $_POST;

                if (!empty($data['programaciones']) && is_string($data['programaciones'])) {
                    $data['programaciones'] = json_decode($data['programaciones'], true);
                }

                $envioModel->actualizarCampania($_POST['idcampania'], $data);
                $response = [
                    "success" => true,
                    "message" => "Campaña actualizada"
                ];
                break;

            // 📌 Eliminar campaña
            case 'eliminar':
                if (!isset($_POST['idcampania'])) throw new Exception("ID de campaña requerido");
                $envioModel->eliminarCampania($_POST['idcampania']);
                $response = [
                    "success" => true,
                    "message" => "Campaña eliminada"
                ];
                break;
        }
    }
} catch (Exception $e) {
    $response = ["success" => false, "message" => $e->getMessage()];
}

echo json_encode($response);
