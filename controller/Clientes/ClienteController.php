<?php
require_once __DIR__ . '/../../models/clientes/ClienteModel.php';

header('Content-Type: application/json; charset=utf-8');

$response = ["success" => false, "message" => "Acción no válida"];

try {
    $clienteModel = new ClienteModel();

    if (isset($_GET['action'])) {
        switch ($_GET['action']) {

            case 'read':
                $data = $clienteModel->obtenerClientes(!empty($_GET['idestado']) ? $_GET['idestado'] : '');
                echo json_encode($data);
                exit;
                break;

            case 'search':
                if (!isset($_GET['filtro'])) throw new Exception("Filtro requerido");
                $data = $clienteModel->buscarClientes($_GET['filtro'], !empty($_GET['idestado']) ? $_GET['idestado'] : '');
                echo json_encode($data);
                exit;
                break;

            case 'get':
                if (!isset($_GET['id'])) throw new Exception("ID requerido");
                $data = $clienteModel->obtenerCliente($_GET['id']);
                $response = ["success" => true, "message" => "Cliente obtenido", "data" => $data];
                break;

            case 'create':
                if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                    $archivoFoto = $_FILES['foto'];

                    // Validar tipo MIME
                    $permitidos = ['image/jpeg', 'image/png'];
                    if (!in_array($archivoFoto['type'], $permitidos)) {
                        throw new Exception('Error: La imagen debe estar en formato JPG o PNG.');
                    }

                    // Validar peso (200 KB máximo)
                    if ($archivoFoto['size'] > 200 * 1024) {
                        throw new Exception('Error: La imagen debe pesar menos de 200 KB.');
                    }

                    // Validar dimensiones
                    $dimensiones = getimagesize($archivoFoto['tmp_name']);
                    if ($dimensiones === false) {
                        throw new Exception('Error: No se pudo leer la imagen.');
                    }

                    $ancho = $dimensiones[0];
                    $alto = $dimensiones[1];
                    if ($ancho > 800 || $alto > 800) {
                        throw new Exception('Error: La imagen debe tener dimensiones máximas de 800 x 800 píxeles.');
                    }

                    $directorioDestino = __DIR__ . "/../../uploads/clientes/";
                    if (!is_dir($directorioDestino)) {
                        mkdir($directorioDestino, 0777, true);
                    }

                    $extension = pathinfo($archivoFoto['name'], PATHINFO_EXTENSION);
                    $nombreArchivo = uniqid("cliente_") . "." . $extension;
                    $rutaDestino = $directorioDestino . $nombreArchivo;

                    if (move_uploaded_file($archivoFoto['tmp_name'], $rutaDestino)) {
                        $_POST['foto'] = "uploads/clientes/" . $nombreArchivo;
                    } else {
                        $_POST['foto'] = "assets/img/usuariodefault.png";
                    }
                } else {
                    $_POST['foto'] = "assets/img/usuariodefault.png";
                }

                $id = $clienteModel->crearCliente($_POST);

                $response = ["success" => true, "message" => "Cliente creado", "id" => $id];
                break;

            case 'setProjects':
                if (empty($_POST['projects'])) throw new Exception("Seleccione algun proyecto");
                $projects = json_decode($_POST['projects'], true);
                foreach ($projects as $project) {
                    $clienteModel->asignarProyectoACliente($_POST['idcliente'], $project);
                }
                $response = ["success" => true, "message" => "Proyectos asignados"];
                break;

            case 'update':
                if (!isset($_POST['idcliente'])) throw new Exception("ID requerido");
                $clienteModel->actualizarCliente($_POST['idcliente'], $_POST);
                $response = ["success" => true, "message" => "Cliente actualizado"];
                break;

            case 'delete':
                if (!isset($_POST['idcliente'])) throw new Exception("ID requerido");
                $clienteModel->eliminarCliente($_POST['idcliente']);
                $response = ["success" => true, "message" => "Cliente eliminado"];
                break;
        }
    }
} catch (Exception $e) {
    $response = ["success" => false, "message" => "Error: " . $e->getMessage()];
}

echo json_encode($response);
