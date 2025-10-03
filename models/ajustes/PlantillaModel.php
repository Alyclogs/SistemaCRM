<?php
require_once __DIR__ . "/../cambios/RegistroCambio.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


class PlantillaModel
{
    private $pdo;
    private $registroCambioModel;

    public function __construct($pdo, $registroCambioModel = null)
    {
        $this->pdo = $pdo;
        $this->registroCambioModel = $registroCambioModel ?: new RegistroCambioModel($this->pdo);
    }

    /**
     * Crear una nueva plantilla
     */
    public function crearPlantilla($data)
    {
        try {
            $sql = "INSERT INTO plantillas (tipo, nombre, asunto, descripcion, contenido_texto, contenido_html, idusuario, fecha_creacion) 
                    VALUES (:tipo, :nombre, :descripcion, :contenido_texto, :contenido_html, :idusuario, NOW())";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ":tipo"      => $data['tipo'] ?? 'general',
                ":nombre"    => $data['nombre'],
                ":descripcion" => $data['descripcion'],
                ":asunto" => $data['asunto'],
                ":contenido_texto" => $data['contenido_texto'],
                ":contenido_html" => $data['contenido_html'],
                ":idusuario" => $data['idusuario'] ?? $_SESSION['idusuario']
            ]);
            $idplantilla = $this->pdo->lastInsertId();

            // Auditoría
            $this->registroCambioModel->registrarCambio(
                $data['idusuario'] ?? $_SESSION['idusuario'],
                $idplantilla,
                'plantilla',
                'creacion',
                null,
                null,
                null,
                "Plantilla creada: " . ($data['nombre'] ?? '')
            );

            return $idplantilla;
        } catch (Exception $e) {
            throw new Exception("Error al crear plantilla: " . $e->getMessage());
        }
    }

    /**
     * Obtener una plantilla por ID
     */
    public function obtenerPlantilla($idplantilla)
    {
        try {
            $sql = "SELECT * FROM plantillas WHERE idplantilla = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$idplantilla]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error al obtener plantilla: " . $e->getMessage());
        }
    }

    /**
     * Listar todas las plantillas
     */
    public function obtenerPlantillas()
    {
        try {
            $sql = "SELECT * FROM plantillas ORDER BY fecha_creacion DESC";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error al obtener plantillas: " . $e->getMessage());
        }
    }

    public function actualizarPlantilla($idplantilla, $data)
    {
        try {
            $this->pdo->beginTransaction();

            // --- 1) Obtener valores antes (para auditoría)
            $plantillaAntes = $this->obtenerPlantilla($idplantilla);

            // --- 2) Preparar actualización dinámica
            $campos = [];
            $params = [];
            $dataValidos = [];

            $camposTabla = $this->registroCambioModel->obtenerCamposTabla("plantillas");

            foreach ($data as $campo => $valor) {
                if (!in_array($campo, $camposTabla, true)) {
                    continue; // ignorar campos inexistentes
                }
                if ($campo === "idplantilla") {
                    continue; // excluir PK
                }

                // Normalizar valores opcionales
                if (in_array($campo, ["descripcion", "contenido_texto", "contenido_html"])) {
                    $valor = (isset($valor) && trim($valor) !== '') ? $valor : null;
                }

                $campos[] = "$campo = :$campo";
                $params[":$campo"] = $valor;
                $dataValidos[$campo] = $valor;
            }

            if (!empty($campos)) {
                $sql = "UPDATE plantillas SET " . implode(", ", $campos) . " WHERE idplantilla = :id";
                $params[':id'] = $idplantilla;
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
            }

            // --- 3) Registrar cambios automáticos
            if (!empty($_SESSION['idusuario']) && !empty($dataValidos)) {
                $this->registroCambioModel->registrarCambiosAutomaticos(
                    $_SESSION['idusuario'],
                    $idplantilla,
                    'plantilla',
                    'actualizacion',
                    $plantillaAntes,
                    $dataValidos
                );
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Error al editar plantilla: " . $e->getMessage());
        }
    }

    public function eliminarPlantilla($idplantilla)
    {
        try {
            $sql = "DELETE FROM plantillas WHERE idplantilla = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$idplantilla]);
        } catch (Exception $e) {
            throw new Exception("Error al eliminar plantilla: " . $e->getMessage());
        }
    }
}
