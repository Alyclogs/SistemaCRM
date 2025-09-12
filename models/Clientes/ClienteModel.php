<?php
require_once __DIR__ . "/../../config/database.php";

class ClienteModel
{
    private $pdo;

    public function __construct()
    {
        try {
            $this->pdo = connectDatabase();
        } catch (PDOException $e) {
            die("Error al conectar en ClienteModel: " . $e->getMessage());
        }
    }

    public function obtenerClientes()
    {
        try {
            $sql = "SELECT c.*, ec.estado FROM clientes c
            INNER JOIN estados_clientes ec ON c.idestado = ec.idestado";
            $stmt = $this->pdo->query($sql);
            $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($clientes as &$cliente) {
                $cliente['proyectos'] = $this->obtenerProyectosPorCliente($cliente['idcliente']);
            }

            return $clientes;
        } catch (Exception $e) {
            throw new Exception("Error al obtener clientes: " . $e->getMessage());
        }
    }

    public function buscarClientes($filtro)
    {
        try {
            $sql = "SELECT c.*, ec.estado FROM clientes c WHERE c.nombre LIKE ? OR c.dni LIKE ? OR c.ruc LIKE ?
            INNER JOIN estados_clientes ec ON c.idestado = ec.idestado";
            $filtro = '%' . $filtro . '%';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$filtro, $filtro, $filtro]);
            $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($clientes as &$cliente) {
                $cliente['proyectos'] = $this->obtenerProyectosPorCliente($cliente['idcliente']);
            }

            return $clientes;
        } catch (Exception $e) {
            throw new Exception("Error al buscar clientes: " . $e->getMessage());
        }
    }

    public function obtenerEstadosClientes()
    {
        try {
            $sql = "SELECT * FROM estados_clientes";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error al obtener estados de clientes: " . $e->getMessage());
        }
    }

    public function obtenerCliente($id)
    {
        try {
            $sql = "SELECT c.*, ec.estado FROM clientes c
            INNER JOIN estados_clientes ec ON c.idestado = ec.idestado
            WHERE c.idcliente = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error al obtener cliente: " . $e->getMessage());
        }
    }

    public function crearCliente($data)
    {
        try {
            $sql = "INSERT INTO clientes (nombre, dni, ruc, telefono, correo, idestado) 
                VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data['nombre'],
                $data['dni'],
                $data['ruc'],
                $data['telefono'],
                $data['correo'],
                $data['idestado']
            ]);

            $idcliente = $this->pdo->lastInsertId();

            // Asignar proyecto al cliente
            if (isset($data['idproyecto'])) {
                $this->asignarProyectoACliente($idcliente, $data['idproyecto']);
            }

            return $idcliente;
        } catch (Exception $e) {
            throw new Exception("Error al crear cliente: " . $e->getMessage());
        }
    }

    /**
     * Obtener proyectos de un cliente
     */
    private function obtenerProyectosPorCliente($idcliente)
    {
        try {
            $sql = "SELECT p.*, ep.estado
                FROM clientes_proyectos cp
                INNER JOIN proyectos p ON cp.idproyecto = p.idproyecto
                INNER JOIN estados_proyectos ep ON p.idestado = ep.idestado
                WHERE cp.idcliente = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$idcliente]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error al obtener proyectos del cliente: " . $e->getMessage());
        }
    }

    /**
     * Asignar un proyecto a un cliente
     */
    public function asignarProyectoACliente($idcliente, $idproyecto)
    {
        try {
            $sql = "INSERT IGNORE INTO clientes_proyectos (idcliente, idproyecto) VALUES (?, ?)";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$idcliente, $idproyecto]);
        } catch (Exception $e) {
            throw new Exception("Error al asignar proyecto al cliente: " . $e->getMessage());
        }
    }

    public function actualizarCliente($id, $data)
    {
        try {
            $sql = "UPDATE clientes SET nombre=?, dni=?, ruc=?, telefono=?, correo=?, idestado=? 
                    WHERE idcliente=?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                $data['nombre'],
                $data['dni'],
                $data['ruc'],
                $data['telefono'],
                $data['correo'],
                $data['idestado'],
                $id
            ]);
        } catch (Exception $e) {
            throw new Exception("Error al actualizar cliente: " . $e->getMessage());
        }
    }

    public function eliminarCliente($id)
    {
        try {
            $sql = "DELETE FROM clientes WHERE idcliente = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            throw new Exception("Error al eliminar cliente: " . $e->getMessage());
        }
    }
}
