<?php
require_once __DIR__ . "/../../models/ajustes/EnvioModel.php";

header('Content-Type: application/json; charset=utf-8');

$response = ["success" => false, "message" => "Acción no válida"];

try {
    $pdo = Database::getConnection();
    $envioModel = new EnvioModel($pdo);

    if (isset($_GET['action'])) {
        switch ($_GET['action']) {

            case 'listar':
                $data = $envioModel->obtenerCampanias();
                $response = $data;
                break;

            case 'ver':
                if (!isset($_GET['idcampania'])) throw new Exception("ID de campaña requerido");
                $data = $envioModel->obtenerCampania($_GET['idcampania']);
                $response = $data;
                break;

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

            case 'eliminar':
                if (!isset($_POST['idcampania'])) throw new Exception("ID de campaña requerido");
                $envioModel->eliminarCampania($_POST['idcampania']);
                $response = [
                    "success" => true,
                    "message" => "Campaña eliminada"
                ];
                break;

            case 'obtenerProgramacionesPendientes':
                $data = $envioModel->obtenerProgramacionesPendientes();
                $response = $data;
                break;

            case 'actualizarEstadoEnvio':
                $idenvio = $_POST['idenvio'] ?? null;
                $nuevoEstado = $_POST['nuevoEstado'] ?? null;
                if (!$idenvio || !$nuevoEstado) throw new Exception("Faltan datos necesarios para la solicitud");
                $envioModel->actualizarEstadoEnvio($idenvio, $nuevoEstado);
                $response = [
                    "success" => true,
                    "message" => "Estado de envío actualizado"
                ];
                break;

            case 'finalizarCampania':
                $idcampania = $_POST['idcampania'] ?? null;
                if (!$idcampania) throw new Exception("ID de campaña requerido");
                $envioModel->finalizarCampania($idcampania);
                $response = [
                    "success" => true,
                    "message" => "Campaña finalizada"
                ];
                break;
        }
    }
} catch (Exception $e) {
    $response = ["success" => false, "message" => $e->getMessage()];
}

echo json_encode($response);
