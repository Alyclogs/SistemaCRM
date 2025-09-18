<?php
// Importa la clase PDO
require_once __DIR__ . '/../../config/database.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class UsuarioModel
{
    public function verificarUsuario($usuario, $password)
    {
        try {
            $pdo = connectDatabase();

            $stmt = $pdo->prepare("
            SELECT u.*, r.rol AS nombre_rol 
            FROM usuarios u
            INNER JOIN roles r ON u.idrol = r.idrol
            WHERE u.usuario = :usuario
            LIMIT 1
        ");
            $stmt->execute([
                'usuario' => $usuario
            ]);

            $usuarioData = $stmt->fetch(PDO::FETCH_ASSOC);
            closeDatabase($pdo);

            // Verificación de contraseña
            if ($usuarioData && password_verify($password, $usuarioData['password'])) {
                return $usuarioData; // Login correcto, incluye nombre_rol
            } else {
                return false; // Usuario o password incorrecto
            }
        } catch (PDOException $e) {
            die("Error al verificar usuario: " . $e->getMessage());
        }
    }
    public function existeUsuario($nombreusuario)
    {
        try {
            $pdo = connectDatabase();

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE usuario = :usuario");
            $stmt->execute(['usuario' => $nombreusuario]);

            $usuarioData = $stmt->fetch(PDO::FETCH_ASSOC);

            closeDatabase($pdo);
            return $usuarioData['COUNT(*)'] > 0;
        } catch (PDOException $e) {
        }
    }

    public function obtenerUsuarioPorId($id)
    {
        try {
            $pdo = connectDatabase();

            $stmt = $pdo->prepare("
            SELECT 
                u.*, 
                r.idrol, r.rol AS nombre_rol
            FROM usuarios u
            INNER JOIN roles r ON u.idrol = r.idRol
            WHERE u.idusuario = :id
        ");

            $stmt->execute(['id' => $id]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            closeDatabase($pdo);

            // Si no encuentra, devuelve null
            return $usuario ?: null;
        } catch (PDOException $e) {
            die("Error al obtener usuario por ID: " . $e->getMessage());
        }
    }


    public function obtenerUsuarios()
    {
        try {
            $pdo = connectDatabase();

            $stmt = $pdo->prepare("
            SELECT 
                u.*,
                r.idrol, 
                r.rol AS nombre_rol,
                e.idestado,
                e.estado
            FROM usuarios u
            INNER JOIN roles r ON u.idrol = r.idrol
            INNER JOIN estados_usuarios e ON u.idestado = e.idestado
            ORDER BY (u.idusuario = :idusuario) DESC, u.idusuario DESC;
        ");

            $stmt->execute([
                ':idusuario' => $_SESSION['idusuario']
            ]);

            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            closeDatabase($pdo);

            return $usuarios;
        } catch (PDOException $e) {
            die('Error al obtener usuarios: ' . $e->getMessage());
        }
    }

    public function buscarUsuarios($filtro = '')
    {
        try {
            $pdo = connectDatabase();

            $sql = "
            SELECT 
                u.*, 
                r.idrol, 
                r.rol AS nombre_rol,
                e.idestado,
                e.estado
            FROM usuarios u
            INNER JOIN roles r ON u.idrol = r.idrol
            INNER JOIN estados_usuarios e ON u.idestado = e.idestado
        ";

            // Si hay filtro, se agrega la condición
            if (!empty($filtro)) {
                $sql .= " AND (
                u.nombres LIKE :filtro 
                OR u.apellidos LIKE :filtro 
                OR u.dni LIKE :filtro
            )";
            }

            $sql .= " ORDER BY (u.idusuario = :idusuario) DESC, u.idusuario DESC;";

            $stmt = $pdo->prepare($sql);

            if (!empty($filtro)) {
                $filtro = '%' . $filtro . '%';
                $stmt->bindParam(':filtro', $filtro, PDO::PARAM_STR);
            }
            $stmt->bindParam(':idusuario', $_SESSION['idusuario'], PDO::PARAM_INT);

            $stmt->execute();
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            closeDatabase($pdo);

            return $usuarios;
        } catch (PDOException $e) {
            die("Error al buscar usuarios: " . $e->getMessage());
        }
    }

    public function obtenerEstados()
    {
        try {
            $pdo = connectDatabase();

            $stmt = $pdo->prepare("SELECT * FROM estados_usuarios;
");
            $stmt->execute();
            $estados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            closeDatabase($pdo);

            return $estados;
        } catch (PDOException $e) {
            die("Error al obtener estados: " . $e->getMessage());
        }
    }

    public function guardarUsuario(
        $nombres,
        $apellidos,
        $dni,
        $telefono,
        $correo,
        $idrol,
        $idestado,
        $usuario,
        $passwordHash,
        $foto
    ) {
        $pdo = connectDatabase();

        try {
            // Iniciar transacción
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
            INSERT INTO usuarios 
                (nombres, apellidos, num_doc, telefono, correo, idrol, idestado, usuario, password, foto)
            VALUES 
                (:nombres, :apellidos, :dni, :telefono, :correo, :idrol, :idestado, :usuario, :password, :foto)
        ");

            $executed = $stmt->execute([
                'nombres'   => $nombres,
                'apellidos' => $apellidos,
                'dni'       => $dni,
                'telefono'  => $telefono,
                'correo'    => $correo,
                'idrol'     => $idrol,
                'idestado'  => $idestado,
                'usuario'   => $usuario,
                'password'  => $passwordHash,
                'foto'      => $foto
            ]);

            if (!$executed) {
                throw new Exception("Error al insertar el usuario en la base de datos.");
            }

            $idUsuarioInsertado = $pdo->lastInsertId();

            // Si todo salió bien, confirmar
            $pdo->commit();

            return $idUsuarioInsertado;
        } catch (Exception $e) {
            // Rollback en caso de error
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        } finally {
            closeDatabase($pdo);
        }
    }

    public function actualizarUsuario(
        $id,
        $nombres,
        $apellidos,
        $dni,
        $telefono,
        $correo,
        $idrol,
        $idestado,
        $usuario,
        $password = null,
        $foto = null
    ) {
        $pdo = connectDatabase();

        try {
            // Iniciamos transacción
            $pdo->beginTransaction();

            $stmtFoto = $pdo->prepare("SELECT foto FROM usuarios WHERE idusuario = :id");
            $stmtFoto->execute(['id' => $id]);
            $fotoActual = $stmtFoto->fetchColumn();

            // Actualizar usuario
            $sql = "
            UPDATE usuarios
            SET nombres   = :nombres,
                apellidos = :apellidos,
                num_doc   = :dni,
                telefono  = :telefono,
                correo    = :correo,
                idrol     = :idrol,
                idestado  = :idestado,
                usuario   = :usuario,
                " . ($password ? "password = :password," : "") . "
                foto      = :foto
            WHERE idusuario = :id
        ";

            $stmt = $pdo->prepare($sql);

            $params = [
                'id'        => $id,
                'nombres'   => $nombres,
                'apellidos' => $apellidos,
                'dni'       => $dni,
                'telefono'  => $telefono,
                'correo'    => $correo,
                'idrol'     => $idrol,
                'idestado'  => $idestado,
                'usuario'   => $usuario,
                'foto'      => $foto ?? $fotoActual ?? "assets/img/usuariodefault.png"
            ];

            if ($password) {
                $params['password'] = password_hash($password, PASSWORD_DEFAULT);
            }

            $stmt->execute($params);

            // Confirmamos cambios
            $pdo->commit();

            closeDatabase($pdo);
            return true;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            closeDatabase($pdo);
            throw $e;
        }
    }

    public function eliminarUsuario($id)
    {
        try {
            $pdo = connectDatabase();

            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE idusuario = :id");
            $stmt->execute(['id' => $id]);
            closeDatabase($pdo);

            return true;
        } catch (PDOException $e) {
            die("Error al eliminar usuario: " . $e->getMessage());
        }
    }

    function obtenerRoles()
    {
        try {
            $pdo = connectDatabase();

            // Traemos roles con permisos en una sola consulta JOIN
            $sql = "SELECT r.idrol, r.rol, p.idpermiso, p.permiso, p.descripcion
                FROM roles r
                LEFT JOIN permisos_roles pr ON r.idrol = pr.idrol
                LEFT JOIN permisos p ON pr.idpermiso = p.idpermiso
                ORDER BY 
                        CASE WHEN r.rol = 'ADMINISTRADOR' THEN 0 ELSE 1 END,
                        r.rol;";
            $stmt = $pdo->query($sql);

            $roles = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $idrol = $row['idrol'];

                if (!isset($roles[$idrol])) {
                    $roles[$idrol] = [
                        'idrol' => $idrol,
                        'rol' => $row['rol'],
                        'permisos' => []
                    ];
                }

                if ($row['idpermiso']) {
                    $roles[$idrol]['permisos'][] = [
                        'idpermiso' => $row['idpermiso'],
                        'permiso' => $row['permiso'],
                        'descripcion' => $row['descripcion']
                    ];
                }
            }

            return array_values($roles);
        } catch (PDOException $e) {
            throw new Exception("Error en obtener: " . $e->getMessage());
            return [];
        }
    }

    function buscarRoles(string $filtro)
    {
        try {
            $pdo = connectDatabase();

            // Traemos roles con permisos en una sola consulta JOIN
            $sql = "SELECT r.idrol, r.rol, p.idpermiso, p.permiso, p.descripcion
                FROM roles r
                LEFT JOIN permisos_roles pr ON r.idrol = pr.idrol
                LEFT JOIN permisos p ON pr.idpermiso = p.idpermiso
                " . (!empty($filtro) ? " WHERE r.rol LIKE :filtro" : '')
                . " ORDER BY 
                        CASE WHEN r.rol = 'ADMINISTRADOR' THEN 0 ELSE 1 END,
                        r.rol;";

            $stmt = $pdo->prepare($sql);

            if (!empty($filtro)) {
                $filtro = '%' . $filtro . '%';
                $stmt->bindParam(':filtro', $filtro, PDO::PARAM_STR);
            }
            $stmt->execute();

            $roles = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $idrol = $row['idrol'];

                if (!isset($roles[$idrol])) {
                    $roles[$idrol] = [
                        'id' => $idrol,
                        'rol' => $row['rol'],
                        'permisos' => []
                    ];
                }

                if ($row['idpermiso']) {
                    $roles[$idrol]['permisos'][] = [
                        'idpermiso' => $row['idpermiso'],
                        'permiso' => $row['permiso'],
                        'descripcion' => $row['descripcion']
                    ];
                }
            }

            return array_values($roles);
        } catch (PDOException $e) {
            throw new Exception("Error: " . $e->getMessage());
            return [];
        }
    }

    function agregarRol($idrol = null, $rol, $permisos = [])
    {
        try {
            $pdo = connectDatabase();
            $pdo->beginTransaction();

            if ($idrol) {
                // Actualizar rol
                $stmt = $pdo->prepare("UPDATE roles SET rol = :rol WHERE idrol = :idrol");
                $stmt->bindParam(":rol", $rol);
                $stmt->bindParam(":idrol", $idrol);
                $stmt->execute();

                // Eliminar relaciones anteriores
                $stmt = $pdo->prepare("DELETE FROM permisos_roles WHERE idrol = :idrol");
                $stmt->bindParam(":idrol", $idrol);
                $stmt->execute();
            } else {
                // Insertar nuevo rol
                $stmt = $pdo->prepare("INSERT INTO roles (rol) VALUES (:rol)");
                $stmt->bindParam(":rol", $rol);
                $stmt->execute();
                $idrol = $pdo->lastInsertId();
            }

            // Insertar permisos seleccionados
            if (!empty($permisos)) {
                $stmt = $pdo->prepare("INSERT INTO permisos_roles (idpermiso, idrol) VALUES (:idpermiso, :idrol)");
                foreach ($permisos as $idpermiso) {
                    $stmt->bindParam(":idpermiso", $idpermiso);
                    $stmt->bindParam(":idrol", $idrol);
                    $stmt->execute();
                }
            }

            $pdo->commit();
            return true;
        } catch (PDOException $e) {
            $pdo->rollBack();
            throw new Exception("Error: " . $e->getMessage());
            return false;
        }
    }

    function eliminarRol($idrol)
    {
        try {
            $pdo = connectDatabase();
            $pdo->beginTransaction();

            // Eliminar relaciones primero
            $stmt = $pdo->prepare("DELETE FROM permisos_roles WHERE idrol = :idrol");
            $stmt->bindParam(":idrol", $idrol);
            $stmt->execute();

            // Luego eliminar el rol
            $stmt = $pdo->prepare("DELETE FROM roles WHERE idrol = :idrol");
            $stmt->bindParam(":idrol", $idrol);
            $stmt->execute();

            $pdo->commit();
            return true;
        } catch (PDOException $e) {
            $pdo->rollBack();
            throw new Exception("Error: " . $e->getMessage());
            return false;
        }
    }

    function obtenerPermisos()
    {
        try {
            $pdo = connectDatabase();
            $sql = "SELECT * FROM permisos ORDER BY categoria ASC";
            $stmt = $pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error: " . $e->getMessage());
            return [];
        }
    }

    function obtenerPermisosPorRol($idrol)
    {
        try {
            $pdo = connectDatabase();
            $sql = "SELECT p.*
                FROM permisos p
                INNER JOIN permisos_roles pr ON p.idpermiso = pr.idpermiso
                WHERE pr.idrol = :idrol
                ORDER BY p.categoria";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(":idrol", $idrol, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error: " . $e->getMessage());
            return [];
        }
    }

    function obtenerPermisosUsuario(int $idusuario): array
    {
        try {
            $pdo = connectDatabase();

            $sql = "SELECT p.codigo
                FROM usuarios u
                INNER JOIN roles r ON u.idrol = r.idrol
                INNER JOIN permisos_roles pr ON r.idrol = pr.idrol
                INNER JOIN permisos p ON pr.idpermiso = p.idpermiso
                WHERE u.idusuario = :idusuario
                ORDER BY p.categoria";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':idusuario', $idusuario, PDO::PARAM_INT);
            $stmt->execute();

            $permisos = $stmt->fetchAll(PDO::FETCH_COLUMN);

            return $permisos ?: [];
        } catch (PDOException $e) {
            throw new Exception("Error al obtener permisos: " . $e->getMessage());
        }
    }

    function usuarioTienePermiso(int $idusuario, string $codigoPermiso): bool
    {
        try {
            $pdo = connectDatabase();

            $sql = "SELECT COUNT(*) as total
                FROM usuarios u
                INNER JOIN roles r ON u.idrol = r.idrol
                INNER JOIN permisos_roles pr ON r.idrol = pr.idrol
                INNER JOIN permisos p ON pr.idpermiso = p.idpermiso
                WHERE u.idusuario = :idusuario
                  AND p.codigo = :codigoPermiso";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':idusuario', $idusuario, PDO::PARAM_INT);
            $stmt->bindParam(':codigoPermiso', $codigoPermiso, PDO::PARAM_STR);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return $row && $row['total'] > 0;
        } catch (PDOException $e) {
            throw new Exception("Error: " . $e->getMessage());
            return false;
        }
    }

    function usuarioTienePermisoSesion(string $codigoPermiso): bool
    {
        return in_array($codigoPermiso, $_SESSION['permisos'] ?? []);
    }
}
