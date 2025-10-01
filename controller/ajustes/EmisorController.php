<?php
require_once __DIR__ . "/../../models/ajustes/EnvioModel.php"; // donde tienes el modelo unificado

header('Content-Type: application/json; charset=utf-8');

$response = ["success" => false, "message" => "Acción no válida"];

try {
    $pdo = Database::getConnection();
    $envioModel = new EnvioModel($pdo);

    if (isset($_GET['action'])) {
        switch ($_GET['action']) {

            // 📌 Listar todos los emisores
            case 'listar':
                $data = $envioModel->obtenerEmisores();
                $response = $data;
                break;

            // 📌 Ver emisor por ID
            case 'ver':
                if (!isset($_GET['idemisor'])) throw new Exception("ID de emisor requerido");
                $data = $envioModel->obtenerEmisor($_GET['idemisor']);
                $response = $data;
                break;

            // 📌 Crear nuevo emisor
            case 'crear':
                $data = $_POST;
                if (empty($data['idusuario'])) {
                    if (!empty($_SESSION['idusuario'])) {
                        $data['idusuario'] = $_SESSION['idusuario'];
                    } else {
                        throw new Exception("ID de usuario requerido");
                    }
                }

                $id = $envioModel->crearEmisor($data);
                $response = [
                    "success" => true,
                    "message" => "Emisor creado",
                    "id" => $id
                ];
                break;

            // 📌 Actualizar emisor
            case 'actualizar':
                if (!isset($_POST['idemisor'])) throw new Exception("ID de emisor requerido");
                $data = $_POST;
                $envioModel->actualizarEmisor($_POST['idemisor'], $data);
                $response = [
                    "success" => true,
                    "message" => "Emisor actualizado"
                ];
                break;

            // 📌 Eliminar emisor
            case 'eliminar':
                if (!isset($_POST['idemisor'])) throw new Exception("ID de emisor requerido");
                $envioModel->eliminarEmisor($_POST['idemisor']);
                $response = [
                    "success" => true,
                    "message" => "Emisor eliminado"
                ];
                break;
        }
    }
} catch (Exception $e) {
    $response = ["success" => false, "message" => $e->getMessage()];
}

echo json_encode($response);
