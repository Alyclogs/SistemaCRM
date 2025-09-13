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
                $id = $clienteModel->crearCliente($_POST);
                $response = ["success" => true, "message" => "Cliente creado", "id" => $id];
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
