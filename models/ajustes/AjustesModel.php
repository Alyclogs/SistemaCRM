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

    public function obtenerCamposPorTipo($idreferencia = null, $tipo_referencia = null)
    {
        try {
            $sql = "SELECT * FROM campos_extra WHERE 1=1"; // base
            $params = [];

            if ($idreferencia !== null) {
                $sql .= " AND idreferencia = ?";
                $params[] = $idreferencia;
            }

            if ($tipo_referencia !== null) {
                $sql .= " AND tipo_referencia = ?";
                $params[] = $tipo_referencia;
            }

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
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
            $this->pdo->beginTransaction();

            // 1. Insertar metadata en campos_extra
            $sql = "INSERT INTO campos_extra (idreferencia, tabla, campo, nombre, valor_inicial, tipo_dato, longitud) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                !empty($data['idreferencia']) ? $data['idreferencia'] : null,
                $data['tabla'],
                $data['campo'],
                $data['nombre'],
                $data['valor_inicial'] ?? null,
                $data['tipo_dato'] ?? 'texto',
                $data['longitud'] ?? null
            ]);

            $idcampo = $this->pdo->lastInsertId();

            // 2. Resolver contexto (dónde se debe aplicar el campo)
            $contexto = $this->registroCambioModel->resolverContextoEntidad($data['tabla']);
            $tablaDestino = $contexto['tabla'];
            $pk = $contexto['pk'];

            // 3. Alter table para agregar la columna
            $tipoSQL = $this->mapearTipoDato($data['tipo_dato'], $data['longitud']);
            $sqlAlter = "ALTER TABLE {$tablaDestino} ADD COLUMN {$data['campo']} {$tipoSQL}";
            $this->pdo->exec($sqlAlter);

            // 4. Registrar cambio
            $this->registroCambioModel->registrarCambio(
                $idusuario,
                $idcampo,
                'campos_extra',
                'creacion',
                null,
                null,
                null,
                null,
                "Campo extra creado en {$tablaDestino}: " . $data['nombre']
            );

            $this->pdo->commit();
            return $idcampo;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw new Exception("Error al crear campo extra: " . $e->getMessage());
        }
    }

    public function actualizarCampo($id, $data, $idusuario)
    {
        try {
            $this->pdo->beginTransaction();

            // 1. Obtener campo actual
            $stmt = $this->pdo->prepare("SELECT * FROM campos_extra WHERE idcampo=?");
            $stmt->execute([$id]);
            $campoAnterior = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$campoAnterior) {
                throw new Exception("Campo no encontrado");
            }

            // 2. Actualizar metadata
            $sql = "UPDATE campos_extra 
                SET idreferencia=?, tabla=?, campo=?, nombre=?, valor_inicial=?, tipo_dato=?, longitud=? 
                WHERE idcampo=?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                !empty($data['idreferencia']) ? $data['idreferencia'] : null,
                $data['tabla'],
                $data['campo'],
                $data['nombre'],
                $data['valor_inicial'] ?? null,
                $data['tipo_dato'] ?? 'texto',
                $data['longitud'] ?? null,
                $id
            ]);

            // 3. Alterar tabla si cambió el nombre/tipo del campo
            $contexto = $this->registroCambioModel->resolverContextoEntidad($data['tabla']);
            $tablaDestino = $contexto['tabla'];

            if ($campoAnterior['campo'] !== $data['campo'] || $campoAnterior['tipo_dato'] !== $data['tipo_dato']) {
                $tipoSQL = $this->mapearTipoDato($data['tipo_dato'], $data['longitud']);
                $sqlAlter = "ALTER TABLE {$tablaDestino} CHANGE {$campoAnterior['campo']} {$data['campo']} {$tipoSQL}";
                $this->pdo->exec($sqlAlter);
            }

            // 4. Registrar cambio
            $this->registroCambioModel->registrarCambio(
                $idusuario,
                $id,
                'campos_extra',
                'actualizacion',
                $campoAnterior,
                $data,
                null,
                null,
                "Campo extra actualizado en {$tablaDestino}: " . $data['nombre']
            );

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw new Exception("Error al actualizar campo extra: " . $e->getMessage());
        }
    }

    public function eliminarCampo($id, $idusuario)
    {
        try {
            $this->pdo->beginTransaction();

            // 1. Obtener info del campo
            $sqlCampo = "SELECT * FROM campos_extra WHERE idcampo = ?";
            $stmtCampo = $this->pdo->prepare($sqlCampo);
            $stmtCampo->execute([$id]);
            $campo = $stmtCampo->fetch(PDO::FETCH_ASSOC);

            if (!$campo) {
                throw new Exception("El campo no existe");
            }

            // 2. Resolver contexto
            $contexto = $this->registroCambioModel->resolverContextoEntidad($campo['tabla']);
            $tablaDestino = $contexto['tabla'];

            // 3. Eliminar la columna en tabla destino
            $sqlAlter = "ALTER TABLE {$tablaDestino} DROP COLUMN {$campo['campo']}";
            $this->pdo->exec($sqlAlter);

            // 4. Eliminar metadata
            $sql = "DELETE FROM campos_extra WHERE idcampo = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);

            // 5. Registrar cambio
            $this->registroCambioModel->registrarCambio(
                $idusuario,
                $id,
                'campos_extra',
                'eliminacion',
                $campo['campo'],
                null,
                null,
                null,
                "Campo extra eliminado en {$tablaDestino}: {$campo['nombre']}"
            );

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw new Exception("Error al eliminar campo extra: " . $e->getMessage());
        }
    }

    /**
     * Mapea tipo_dato → tipo SQL
     */
    private function mapearTipoDato($tipo, $longitud = null)
    {
        return match ($tipo) {
            'texto'    => "VARCHAR(" . ($longitud ?: 255) . ")",
            'numero'   => "INT",
            'booleano' => "TINYINT(1)",
            'fecha'    => "DATE",
            'opciones' => "VARCHAR(" . ($longitud ?: 255) . ")",
            default    => "TEXT"
        };
    }
}
