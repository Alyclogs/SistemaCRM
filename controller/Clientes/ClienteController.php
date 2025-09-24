<?php
require_once __DIR__ . '/../../models/clientes/ClienteModel.php';

header('Content-Type: application/json; charset=utf-8');

$response = ["success" => false, "message" => "Acción no válida"];

try {
    $clienteModel = new ClienteModel();

    if (isset($_GET['action'])) {
        switch ($_GET['action']) {

            case 'listar':
                if (!isset($_GET['tipo']) || empty($_GET['tipo'])) throw new Exception("Tipo requerido");
                if ($_GET['tipo'] == '1') {
                    $data = $clienteModel->obtenerClientes(!empty($_GET['idestado']) ? $_GET['idestado'] : '');
                    echo json_encode($data);
                    exit;
                }
                if ($_GET['tipo'] == '2') {
                    $data = $clienteModel->obtenerOrganizaciones();
                    echo json_encode($data);
                    exit;
                }
                break;

            case 'buscar':
                if (!isset($_GET['tipo']) || empty($_GET['tipo'])) throw new Exception("Tipo requerido");
                if (!isset($_GET['filtro']) || empty($_GET['filtro'])) throw new Exception("Filtro requerido");

                if ($_GET['tipo'] == '1') {
                    $data = $clienteModel->buscarClientes($_GET['filtro'], !empty($_GET['idestado']) ? $_GET['idestado'] : '');
                    echo json_encode($data);
                    exit;
                }
                if ($_GET['tipo'] == '2') {
                    $data = $clienteModel->buscarOrganizaciones($_GET['filtro']);
                    echo json_encode($data);
                    exit;
                }
                break;

            case 'ver':
                if (!isset($_GET['id'])) throw new Exception("ID requerido");
                $data = $clienteModel->obtenerCliente($_GET['id']);
                $response = ["success" => true, "message" => "Cliente obtenido", "data" => $data];
                break;

            case 'buscarOrganizaciones':
                if (!isset($_GET['filtro'])) throw new Exception("Filtro requerido");
                $data = $clienteModel->buscarOrganizaciones($_GET['filtro']);
                echo json_encode($data);
                exit;
                break;

            case 'crear':
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

            case 'actualizar':
                if (!isset($_POST['idexistente'])) throw new Exception("ID requerido");

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
                    $_POST['foto'] = null;
                }

                $clienteModel->actualizarCliente($_POST['idexistente'], $_POST);
                $response = ["success" => true, "message" => "Cliente actualizado"];
                break;

            case 'eliminar':
                if (!isset($_POST['idexistente'])) throw new Exception("ID requerido");
                $clienteModel->eliminarCliente($_POST['idexistente']);
                $response = ["success" => true, "message" => "Cliente eliminado"];
                break;

            case 'crearOrganizacion':
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

                    $directorioDestino = __DIR__ . "/../../uploads/organizaciones/";
                    if (!is_dir($directorioDestino)) {
                        mkdir($directorioDestino, 0777, true);
                    }

                    $extension = pathinfo($archivoFoto['name'], PATHINFO_EXTENSION);
                    $nombreArchivo = uniqid("organizacion_") . "." . $extension;
                    $rutaDestino = $directorioDestino . $nombreArchivo;

                    if (move_uploaded_file($archivoFoto['tmp_name'], $rutaDestino)) {
                        $_POST['foto'] = "uploads/organizaciones/" . $nombreArchivo;
                    } else {
                        $_POST['foto'] = "assets/img/organizaciondefault.png";
                    }
                } else {
                    $_POST['foto'] = null;
                }

                $id = $clienteModel->crearEmpresa($_POST);
                $response = ["success" => true, "message" => "Organización creada", "id" => $id];
                break;

            case 'actualizarOrganizacion':
                if (!isset($_POST['idexistente'])) throw new Exception("ID requerido");

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

                    $directorioDestino = __DIR__ . "/../../uploads/organizaciones/";
                    if (!is_dir($directorioDestino)) {
                        mkdir($directorioDestino, 0777, true);
                    }

                    $extension = pathinfo($archivoFoto['name'], PATHINFO_EXTENSION);
                    $nombreArchivo = uniqid("organizacion_") . "." . $extension;
                    $rutaDestino = $directorioDestino . $nombreArchivo;

                    if (move_uploaded_file($archivoFoto['tmp_name'], $rutaDestino)) {
                        $_POST['foto'] = "uploads/organizaciones/" . $nombreArchivo;
                    } else {
                        $_POST['foto'] = "assets/img/organizaciondefault.png";
                    }
                } else {
                    $_POST['foto'] = null;
                }

                $clienteModel->actualizarEmpresa($_POST['idexistente'], $_POST);
                $response = ["success" => true, "message" => "Organización actualizada"];
                break;

            case 'eliminarOrganizacion':
                if (!isset($_POST['idexistente'])) throw new Exception("ID requerido");
                $clienteModel->eliminarEmpresa($_POST['idexistente']);
                $response = ["success" => true, "message" => "Organización eliminada"];
                break;
        }
    }
} catch (Exception $e) {
    $response = ["success" => false, "message" => "Error: " . $e->getMessage()];
}

echo json_encode($response);
