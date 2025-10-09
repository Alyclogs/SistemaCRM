<?php
require_once __DIR__ . "/../../models/ajustes/DiccionarioModel.php";
require_once __DIR__ . "/../../config/database.php";

header('Content-Type: application/json; charset=utf-8');

$response = ["success" => false, "message" => "Acción no válida"];

try {
    $pdo = Database::getConnection();
    $dicModel = new DiccionarioModel($pdo, $registroCambioModel ?? null);

    if (isset($_GET['action'])) {
        switch ($_GET['action']) {

            case 'listar':
                $tabla = $_GET['tabla'] ?? null;
                $contexto = $_GET['contexto'] ?? null;
                $data = $dicModel->listar($tabla, $contexto);
                $response = $data;
                break;

            case 'save':
            case 'guardar':
                $tabla = $_POST['tabla'] ?? null;
                $columnas = $_POST['columnas'] ?? null;

                if (!$tabla) throw new Exception("Parámetro 'tabla' requerido");

                if (is_string($columnas)) {
                    $columnas = json_decode($columnas, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new Exception("JSON inválido en 'columnas': " . json_last_error_msg());
                    }
                }

                if (!is_array($columnas)) {
                    throw new Exception("'columnas' debe ser un array JSON");
                }

                $dicModel->guardarPorTabla($tabla, $columnas);
                $response = ["success" => true, "message" => "Configuración guardada"];
                break;

            case 'actualizar':
                $idd = $_POST['iddiccionario'] ?? null;
                if (!$idd) throw new Exception("iddiccionario requerido");

                $data = $_POST;
                unset($data['action']);
                $res = $dicModel->actualizar($idd, $data);
                $response = ["success" => (bool)$res, "message" => $res ? "Actualizado" : "No se actualizó"];
                break;

            case 'eliminar':
                if (!empty($_POST['iddiccionario'])) {
                    $res = $dicModel->eliminarPorId($_POST['iddiccionario']);
                    $response = ["success" => (bool)$res, "message" => $res ? "Eliminado" : "No eliminado"];
                } elseif (!empty($_POST['tabla'])) {
                    $res = $dicModel->eliminarPorTabla($_POST['tabla']);
                    $response = ["success" => (bool)$res, "message" => $res ? "Configuraciones eliminadas" : "No eliminado"];
                } else {
                    throw new Exception("iddiccionario o tabla requerido para eliminar");
                }
                break;

            default:
                $response = ["success" => false, "message" => "Acción no válida"];
                break;
        }
    }
} catch (Exception $e) {
    $response = ["success" => false, "message" => $e->getMessage()];
}

echo json_encode($response);
