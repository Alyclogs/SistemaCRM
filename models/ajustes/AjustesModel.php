<?php
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../cambios/RegistroCambio.php";

class AjustesModel
{
    private $pdo;
    private $registroCambioModel;

    public function __construct($pdo, $registroCambioModel = null)
    {
        try {
            $this->pdo = $pdo;
            $this->registroCambioModel = $registroCambioModel;
        } catch (PDOException $e) {
            die("Error al conectar en AjustesModel: " . $e->getMessage());
        }
    }

    public function obtenerDisponibilidades()
    {
        try {
            $sql = "SELECT * FROM disponibilidad_general ORDER BY fecha_creacion ASC";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error al obtener disponibilidades: " . $e->getMessage());
        }
    }

    public function obtenerDisponibilidad($id)
    {
        try {
            $sql = "SELECT * FROM disponibilidad_general WHERE iddisponibilidad = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error al obtener disponibilidad: " . $e->getMessage());
        }
    }

    public function crearDisponibilidad($data, $idusuario)
    {
        try {
            $sql = "INSERT INTO disponibilidad_general (fecha_inicio, fecha_fin, dia_semana, hora_inicio, hora_fin, estado) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data['fecha_inicio'] ?? null,
                $data['fecha_fin'] ?? null,
                $data['dia_semana'] ?? null,
                $data['hora_inicio'],
                $data['hora_fin'],
                $data['estado'] ?? 'activo'
            ]);

            $iddisponibilidad = $this->pdo->lastInsertId();

            $this->registroCambioModel->registrarCambio(
                $idusuario,
                $iddisponibilidad,
                'disponibilidad_general',
                'creacion',
                null,
                null,
                null,
                null,
                "Disponibilidad creada"
            );

            return $iddisponibilidad;
        } catch (Exception $e) {
            throw new Exception("Error al crear disponibilidad: " . $e->getMessage());
        }
    }

    public function actualizarDisponibilidad($id, $data, $idusuario)
    {
        try {
            $sql = "UPDATE disponibilidad_general 
                    SET fecha_inicio=?, fecha_fin=?, dia_semana=?, hora_inicio=?, hora_fin=?, estado=? 
                    WHERE iddisponibilidad=?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data['fecha_inicio'] ?? null,
                $data['fecha_fin'] ?? null,
                $data['dia_semana'] ?? null,
                $data['hora_inicio'],
                $data['hora_fin'],
                $data['estado'] ?? 'activo',
                $id
            ]);

            $this->registroCambioModel->registrarCambio(
                $idusuario,
                $id,
                'disponibilidad_general',
                'actualizacion',
                null,
                null,
                null,
                null,
                "Disponibilidad actualizada"
            );

            return true;
        } catch (Exception $e) {
            throw new Exception("Error al actualizar disponibilidad: " . $e->getMessage());
        }
    }

    public function eliminarDisponibilidad($id, $idusuario)
    {
        try {
            $sql = "DELETE FROM disponibilidad_general WHERE iddisponibilidad = ?";
            $stmt = $this->pdo->prepare($sql);
            $resultado = $stmt->execute([$id]);

            $this->registroCambioModel->registrarCambio(
                $idusuario,
                $id,
                'disponibilidad_general',
                'eliminacion',
                null,
                null,
                null,
                null,
                "Disponibilidad eliminada"
            );

            return $resultado;
        } catch (Exception $e) {
            throw new Exception("Error al eliminar disponibilidad: " . $e->getMessage());
        }
    }

    public function obtenerCampos()
    {
        try {
            $sql = "SELECT * FROM campos_extra";
            $stmt = $this->pdo->query($sql);
            $campos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($campos as &$campo) {
                if ($campo['tipo_dato'] === 'booleano') {
                    $campo['valor_inicial'] = $campo['valor_inicial'] === '1' ? 'Sí' : 'No';
                }
                if ($campo['tipo_dato'] === 'fecha' && $campo['valor_inicial']) {
                    $campo['valor_inicial'] = date('Y-m-d', strtotime($campo['valor_inicial']));
                }
                if ($campo['tipo_dato'] === 'numero' && $campo['valor_inicial']) {
                    $campo['valor_inicial'] = (float)$campo['valor_inicial'];
                }
                if ($campo['tipo_dato'] === 'opciones' && $campo['valor_inicial']) {
                    $campo['valor_inicial'] = json_decode($campo['valor_inicial'], true);
                }
            }

            return $campos;
        } catch (Exception $e) {
            throw new Exception("Error al obtener campos extra: " . $e->getMessage());
        }
    }

    public function obtenerCamposPorTipo($idreferencia, $tipo_referencia)
    {
        try {
            $sql = "SELECT * FROM campos_extra WHERE idreferencia = ? OR tipo_referencia = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$idreferencia, $tipo_referencia]);
            $campos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($campos as &$campo) {
                if ($campo['tipo_dato'] === 'booleano') {
                    $campo['valor_inicial'] = $campo['valor_inicial'] === '1' ? 'Sí' : 'No';
                }
                if ($campo['tipo_dato'] === 'fecha' && $campo['valor_inicial']) {
                    $campo['valor_inicial'] = date('Y-m-d', strtotime($campo['valor_inicial']));
                }
                if ($campo['tipo_dato'] === 'numero' && $campo['valor_inicial']) {
                    $campo['valor_inicial'] = (float)$campo['valor_inicial'];
                }
                if ($campo['tipo_dato'] === 'opciones' && $campo['valor_inicial']) {
                    $campo['valor_inicial'] = json_decode($campo['valor_inicial'], true);
                }
            }

            return $campos;
        } catch (Exception $e) {
            throw new Exception("Error al obtener campos extra: " . $e->getMessage());
        }
    }

    public function obtenerCampo($idcampo)
    {
        try {
            $sql = "SELECT * FROM campos_extra WHERE idcampo = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$idcampo]);
            $campo = $stmt->fetch(PDO::FETCH_ASSOC);

            return $campo;
        } catch (Exception $e) {
            throw new Exception("Error al obtener campo extra: " . $e->getMessage());
        }
    }

    public function crearCampo($data, $idusuario)
    {
        try {
            $sql = "INSERT INTO campos_extra (idreferencia, tipo_referencia, nombre, valor_inicial, tipo_dato, longitud) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                !empty($data['idreferencia']) ? $data['idreferencia'] : null,
                $data['tipo_referencia'],
                $data['nombre'],
                $data['valor_inicial'] ?? null,
                $data['tipo_dato'] ?? 'texto',
                $data['longitud'] ?? null
            ]);

            $idcampo = $this->pdo->lastInsertId();

            $this->registroCambioModel->registrarCambio(
                $idusuario,
                $idcampo,
                'campos_extra',
                'creacion',
                null,
                null,
                null,
                null,
                "Campo extra creado: " . $data['nombre']
            );

            return $idcampo;
        } catch (Exception $e) {
            throw new Exception("Error al crear campo extra: " . $e->getMessage());
        }
    }

    public function actualizarCampo($id, $data, $idusuario)
    {
        try {
            $sql = "UPDATE campos_extra 
                    SET idreferencia=?, tipo_referencia=?, nombre=?, valor_inicial=?, tipo_dato=?, longitud=? 
                    WHERE idcampo=?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                !empty($data['idreferencia']) ? $data['idreferencia'] : null,
                $data['tipo_referencia'],
                $data['nombre'],
                $data['valor_inicial'] ?? null,
                $data['tipo_dato'] ?? 'texto',
                $data['longitud'] ?? null,
                $id
            ]);

            $this->registroCambioModel->registrarCambio(
                $idusuario,
                $id,
                'campos_extra',
                'actualizacion',
                null,
                null,
                null,
                null,
                "Campo extra actualizado: " . $data['nombre']
            );

            return true;
        } catch (Exception $e) {
            throw new Exception("Error al actualizar campo extra: " . $e->getMessage());
        }
    }

    public function eliminarCampo($id, $idusuario)
    {
        try {
            // 1. Obtener info del campo
            $sqlCampo = "SELECT * FROM campos_extra WHERE idcampo = ?";
            $stmtCampo = $this->pdo->prepare($sqlCampo);
            $stmtCampo->execute([$id]);
            $campo = $stmtCampo->fetch(PDO::FETCH_ASSOC);

            if (!$campo) {
                throw new Exception("El campo no existe");
            }

            $nombreCampo = $campo['nombre'];
            $tipoRef = $campo['tipo_referencia'];

            // 2. Definir tablas que tienen columna `extra`
            $tablas = [
                'cliente'   => 'clientes',
                'empresa'   => 'empresas',
                'actividad' => 'actividades',
                'proyecto'  => 'proyectos',
                'tarea'     => 'tareas'
            ];

            if (isset($tablas[$tipoRef])) {
                $tabla = $tablas[$tipoRef];

                // 3. Buscar registros con extra
                $sqlSel = "SELECT id{$tipoRef} as id, extra FROM {$tabla} WHERE extra IS NOT NULL AND extra != ''";
                $stmtSel = $this->pdo->prepare($sqlSel);
                $stmtSel->execute();
                $registros = $stmtSel->fetchAll(PDO::FETCH_ASSOC);

                foreach ($registros as $reg) {
                    $extra = json_decode($reg['extra'], true);

                    if (is_array($extra) && array_key_exists($nombreCampo, $extra)) {
                        unset($extra[$nombreCampo]); // eliminar campo del json

                        $nuevoExtra = !empty($extra) ? json_encode($extra) : null;

                        $sqlUpd = "UPDATE {$tabla} SET extra = ? WHERE id{$tipoRef} = ?";
                        $stmtUpd = $this->pdo->prepare($sqlUpd);
                        $stmtUpd->execute([$nuevoExtra, $reg['id']]);
                    }
                }
            }

            // 4. Eliminar el campo de campos_extra
            $sql = "DELETE FROM campos_extra WHERE idcampo = ?";
            $stmt = $this->pdo->prepare($sql);
            $resultado = $stmt->execute([$id]);

            // 5. Registrar cambio
            $this->registroCambioModel->registrarCambio(
                $idusuario,
                $id,
                'campos_extra',
                'eliminacion',
                null,
                null,
                null,
                null,
                "Campo extra eliminado: {$nombreCampo} (referencia: {$tipoRef})"
            );

            return $resultado;
        } catch (Exception $e) {
            throw new Exception("Error al eliminar campo extra: " . $e->getMessage());
        }
    }
}
