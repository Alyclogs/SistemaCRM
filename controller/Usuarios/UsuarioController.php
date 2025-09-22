<?php
require_once __DIR__ . '/../../models/usuarios/UsuarioModel.php';

$modelo = new UsuarioModel();
$mensaje = '';
$success = false;

try {
    if (!isset($_GET['action'])) {
        throw new Exception('Error: Acción no especificada.');
    }

    $action = $_GET['action'];

    switch ($action) {
        case 'listar':
            $usuarios = $modelo->obtenerUsuarios();
            echo json_encode($usuarios);
            exit;

        case 'eliminar':
            $id = $_POST['idusuario'];
            if (!is_numeric($id)) {
                throw new Exception('Error: ID inválido.');
            }
            $success = $modelo->eliminarUsuario($id);
            $mensaje = $success ? 'Usuario eliminado correctamente.' : 'Error al eliminar el usuario.';
            break;

        case 'buscar':
            $filtro = $_GET['filtro'] ?? '';
            $usuarios = $modelo->buscarUsuarios($filtro);
            echo json_encode($usuarios);
            exit;
            break;

        case 'usuarioTienePermiso':
            $codigo = $_GET['codigo'] ?? '';
            $result = $modelo->usuarioTienePermisoSesion($codigo);
            echo json_encode((bool)$result);
            exit;
            break;

        case 'actualizar':
            $id = $_POST['idusuario'] ?? null;
            if (!is_numeric($id)) {
                throw new Exception('Error: ID inválido.');
            }

            $nombres = ucwords($_POST['nombres']);
            $apellidos = ucwords($_POST['apellidos']);
            $dni = !empty($_POST['num_doc']) ? $_POST['num_doc'] : null;
            $telefono = !empty($_POST['telefono']) ? $_POST['telefono'] : null;
            $correo = !empty($_POST['correo']) ? $_POST['correo'] : null;
            $idrol = $_POST['idrol'];
            $idestado = $_POST['idestado'];
            $usuario = strtolower(trim($_POST['usuario']));
            $password = $_POST['password'];
            // Foto no es obligatoria
            $archivoFoto = null;
            $rutaFoto = null;
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

                $directorioDestino = __DIR__ . "/../../uploads/usuarios/";
                if (!is_dir($directorioDestino)) {
                    mkdir($directorioDestino, 0777, true);
                }

                $extension = pathinfo($archivoFoto['name'], PATHINFO_EXTENSION);
                $nombreArchivo = uniqid("cliente_") . "." . $extension;
                $rutaDestino = $directorioDestino . $nombreArchivo;

                if (move_uploaded_file($archivoFoto['tmp_name'], $rutaDestino)) {
                    $rutaFoto = "uploads/usuarios/" . $nombreArchivo;
                } else {
                    $rutaFoto = "assets/img/usuariodefault.png";
                }
            }

            // Llamar al modelo para actualizar el usuario
            $success = $modelo->actualizarUsuario($id, $nombres, $apellidos, $dni, $telefono, $correo, $idrol, $idestado, $usuario, $password, $rutaFoto);

            $mensaje = $success ? 'Usuario actualizado correctamente.' : 'Error al actualizar el usuario.';
            break;


        case 'create':
            try {
                $usuario = strtolower(trim($_POST['usuario']));
                $password = $_POST['password'];

                // Validaciones
                if ($modelo->existeUsuario($usuario)) {
                    throw new Exception('Error: El nombre de usuario ya existe.');
                }

                if (!preg_match('/^\d{8}$/', $password)) {
                    throw new Exception('Error: La contraseña debe tener exactamente 8 dígitos numéricos.');
                }

                // Procesamiento
                $nombres = ucwords($_POST['nombres']);
                $apellidos = ucwords($_POST['apellidos']);
                $dni = !empty($_POST['num_doc']) ? $_POST['num_doc'] : null;
                $telefono = !empty($_POST['telefono']) ? $_POST['telefono'] : null;
                $correo = !empty($_POST['correo']) ? $_POST['correo'] : null;
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $idrol = $_POST['idrol'];
                $idestado = $_POST['idestado'];
                $archivoFoto = null;
                $rutaFoto = "assets/img/usuariodefault.png";
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

                    $directorioDestino = __DIR__ . "/../../uploads/usuarios/";
                    if (!is_dir($directorioDestino)) {
                        mkdir($directorioDestino, 0777, true);
                    }

                    $extension = pathinfo($archivoFoto['name'], PATHINFO_EXTENSION);
                    $nombreArchivo = uniqid("cliente_") . "." . $extension;
                    $rutaDestino = $directorioDestino . $nombreArchivo;

                    if (move_uploaded_file($archivoFoto['tmp_name'], $rutaDestino)) {
                        $rutaFoto = "uploads/usuarios/" . $nombreArchivo;
                    } else {
                        $rutaFoto = "assets/img/usuariodefault.png";
                    }
                }

                $success = $modelo->guardarUsuario($nombres, $apellidos, $dni, $telefono, $correo, $idrol, $idestado, $usuario, $passwordHash, $rutaFoto);
                $mensaje = $success ? 'El registro de usuario se guardo de forma existosa.' : 'Error al registrar el usuario.';

                // Respuesta en JSON
                echo json_encode(['success' => $success, 'message' => $mensaje]);
                exit;
            } catch (Exception $e) {
                // Respuesta de error
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                exit;
            }
            break;

        case 'obtenerRoles':
            try {
                $resultado = $modelo->obtenerRoles();
                echo json_encode($resultado);
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            exit;
            break;

        case 'agregarRol':
            try {
                $idrol = isset($_POST['idrol']) ? (int) $_POST['idrol'] : null;
                $rol = isset($_POST['rol']) ? trim($_POST['rol']) : null;
                $permisos = isset($_POST['permisos']) ? json_decode($_POST['permisos'], true) : [];

                if (!$rol) throw new Exception("El nombre del rol es obligatorio");

                $resultado = $modelo->agregarRol($idrol, $rol, $permisos);
                echo json_encode([
                    'success' => $resultado,
                    'message' => $resultado ? 'Rol guardado correctamente' : 'Error al guardar el rol'
                ]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            exit;
            break;

        case 'eliminarRol':
            try {
                $idrol = isset($_POST['idrol']) ? (int) $_POST['idrol'] : null;
                $resultado = $modelo->eliminarRol($idrol);
                echo json_encode([
                    'success' => $resultado,
                    'message' => $resultado ? 'Rol eliminado correctamente' : 'Error al eliminar el rol'
                ]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            exit;
            break;

        case 'obtenerPermisos':
            try {
                $resultado = $modelo->obtenerPermisos();
                echo json_encode($resultado);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
            exit;
            break;

        case 'buscarRoles':
            try {
                $filtro = isset($_GET['filtro']) ? $_GET['filtro'] : null;
                $resultado = $modelo->buscarRoles($filtro);
                echo json_encode($resultado);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
            exit;
            break;

        case 'obtenerPermisosPorRol':
            try {
                $idrol = isset($_GET['idrol']) ? (int) $_GET['idrol'] : null;
                if (!$idrol) {
                    throw new Exception("Debe especificar un rol válido");
                }

                $resultado = $modelo->obtenerPermisosPorRol($idrol);
                echo json_encode($resultado);
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
            exit;

        default:
            throw new Exception('Error: Acción no válida.');
    }
} catch (Exception $e) {
    $mensaje = $e->getMessage();
    $success = false;
}

// Salida JSON estándar
header('Content-Type: application/json');
echo json_encode([
    'success' => $success,
    'message' => $mensaje
]);
