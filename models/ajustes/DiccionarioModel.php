<?php
class DiccionarioModel
{
    private $pdo;
    private $registroCambioModel;

    public function __construct(PDO $pdo, $registroCambioModel = null)
    {
        $this->pdo = $pdo;
        $this->registroCambioModel = $registroCambioModel;
    }

    public function listar($tabla = null, $contexto = null)
    {
        $params = [];
        $wheres = [];

        if ($tabla) {
            $wheres[] = "tabla = :tabla";
            $params[':tabla'] = $tabla;
        }
        if ($contexto) {
            $wheres[] = "contexto = :contexto";
            $params[':contexto'] = $contexto;
        }

        // WHERE dinámico para cada SELECT
        $whereSql = !empty($wheres) ? (" WHERE " . implode(" AND ", $wheres)) : "";
        $cols = "campo, contexto, descripcion, fecha_creacion, idcampo, longitud, meta, nombre, orden, requerido, tabla, tipo_dato, valor_inicial, visible";

        // UNION con origen diferenciado
        $sql = "
        SELECT {$cols}, 'normal' AS origen
        FROM diccionario_campos
        {$whereSql}
        UNION ALL
        SELECT {$cols}, 'extra' AS origen
        FROM campos_extra
        {$whereSql}
        ORDER BY COALESCE(`orden`, 9999) ASC, campo ASC
    ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Decodificar meta si está en formato JSON
        foreach ($data as &$row) {
            if (!empty($row['meta'])) {
                $decoded = json_decode($row['meta'], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $row['meta'] = $decoded;
                }
            } else {
                $row['meta'] = null;
            }
        }

        return $data;
    }

    public function obtenerPorId($idcampo)
    {
        $cols = "campo, contexto, descripcion, fecha_creacion, idcampo, longitud, meta, nombre, orden, requerido, tabla, tipo_dato, valor_inicial, visible";
        $sql = "
        SELECT {$cols}, 'normal' AS origen FROM diccionario_campos WHERE idcampo = :id
        UNION ALL
        SELECT {$cols}, 'extra' AS origen FROM campos_extra WHERE idcampo = :id
        LIMIT 1
    ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $idcampo]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return null;

        if (!empty($row['meta'])) {
            $decoded = json_decode($row['meta'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $row['meta'] = $decoded;
            }
        } else {
            $row['meta'] = null;
        }

        return $row;
    }

    /**
     * Guarda la configuración de columnas en la tabla correspondiente (diccionario_campos o campos_extra)
     */
    public function guardarPorTabla($tabla, array $columnas)
    {
        try {
            $this->pdo->beginTransaction();

            // Agrupar por origen: normal o extra
            $agrupados = [
                'normal' => [],
                'extra'  => []
            ];

            foreach ($columnas as $col) {
                $origen = isset($col['origen']) && $col['origen'] === 'extra' ? 'extra' : 'normal';
                $agrupados[$origen][] = $col;
            }

            foreach ($agrupados as $origen => $cols) {
                if (empty($cols)) continue;

                $tablaDestino = ($origen === 'extra') ? 'campos_extra' : 'diccionario_campos';

                // Eliminar anteriores
                $stmtDel = $this->pdo->prepare("DELETE FROM {$tablaDestino} WHERE tabla = :tabla");
                $stmtDel->execute([':tabla' => $tabla]);

                // Insertar nuevos
                $sql = "INSERT INTO {$tablaDestino}
                (tabla, campo, nombre, descripcion, tipo_dato, longitud, requerido, valor_inicial, contexto, meta, visible, `orden`, fecha_creacion)
                VALUES (:tabla, :campo, :nombre, :descripcion, :tipo_dato, :longitud, :requerido, :valor_inicial, :contexto, :meta, :visible, :orden, NOW())";
                $stmtIns = $this->pdo->prepare($sql);
                $orden = count($cols) ?? 0;

                foreach ($cols as $c) {
                    $meta = isset($c['meta'])
                        ? (is_array($c['meta']) ? json_encode($c['meta'], JSON_UNESCAPED_UNICODE) : $c['meta'])
                        : null;

                    $stmtIns->execute([
                        ':tabla'         => $tabla,
                        ':campo'         => $c['campo'] ?? null,
                        ':nombre'        => $c['nombre'] ?? ($c['campo'] ?? ''),
                        ':descripcion'   => $c['descripcion'] ?? '',
                        ':tipo_dato'     => $c['tipo_dato'] ?? 'texto',
                        ':longitud'      => isset($c['longitud']) ? (int)$c['longitud'] : null,
                        ':requerido'     => !empty($c['requerido']) ? 1 : 0,
                        ':valor_inicial' => $c['valor_inicial'] ?? null,
                        ':contexto'      => $c['contexto'] ?? 'general',
                        ':meta'          => $meta,
                        ':visible'       => !empty($c['visible']) ? 1 : 0,
                        ':orden'         => isset($c['orden']) ? (int)$c['orden'] : $orden + 1
                    ]);
                }
            }



            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function actualizarCampo($idcampo, array $data)
    {
        // Determinar origen
        $origen = isset($data['origen']) && $data['origen'] === 'extra' ? 'extra' : 'normal';
        $tablaDestino = ($origen === 'extra') ? 'campos_extra' : 'diccionario_campos';
        $campoId = 'idcampo'; // ambos usan el mismo identificador

        $allowed = [
            'campo',
            'nombre',
            'descripcion',
            'tipo_dato',
            'longitud',
            'requerido',
            'valor_inicial',
            'contexto',
            'meta',
            'visible',
            'orden',
            'tabla'
        ];

        $sets = [];
        $params = [':idcampo' => $idcampo];

        foreach ($data as $k => $v) {
            if (!in_array($k, $allowed, true)) continue;

            if ($k === 'requerido' || $k === 'visible') {
                $v = !empty($v) ? 1 : 0;
            } elseif ($k === 'longitud' || $k === 'orden') {
                $v = ($v !== null && $v !== '') ? (int)$v : null;
            } elseif ($k === 'meta') {
                if (is_array($v) || is_object($v)) {
                    $v = json_encode($v, JSON_UNESCAPED_UNICODE);
                }
            }

            $sets[] = "`$k` = :$k";
            $params[":$k"] = $v;
        }

        if (empty($sets)) return false;

        $sql = "UPDATE {$tablaDestino} SET " . implode(", ", $sets) . " WHERE {$campoId} = :idcampo";
        $stmt = $this->pdo->prepare($sql);
        $res = $stmt->execute($params);

        // Registrar cambio si aplica
        if ($res && $this->registroCambioModel && isset($_SESSION['idusuario'])) {
            $this->registroCambioModel->registrarCambio(
                $_SESSION['idusuario'],
                $idcampo,
                $tablaDestino,
                'actualizacion',
                null,
                json_encode($data, JSON_UNESCAPED_UNICODE),
                null,
                "Actualización de campo ({$origen}) en {$tablaDestino}"
            );
        }

        return $res;
    }

    /**
     * Elimina todos los campos de una tabla específica
     * Puede recibir origen = 'normal', 'extra' o 'todos'
     */
    public function eliminarPorTabla($tabla, $campo, $origen = 'normal')
    {
        try {
            $this->pdo->beginTransaction();

            $columnasExistentes = $this->registroCambioModel->obtenerCamposTabla($tabla);
            $totalEliminados = 0;

            // Si se pide eliminar ambos orígenes
            if ($origen === 'todos' || $origen === 'normal') {
                $stmt = $this->pdo->prepare("DELETE FROM diccionario_campos WHERE tabla = ? AND campo = ?");
                $stmt->execute([$tabla, $campo]);
                $totalEliminados += $stmt->rowCount();
            }

            if ($origen === 'todos' || $origen === 'extra') {
                $stmt = $this->pdo->prepare("DELETE FROM campos_extra WHERE tabla = ? AND campo = ?");
                $stmt->execute([$tabla, $campo]);
                $totalEliminados += $stmt->rowCount();
            }

            if (in_array($campo, $columnasExistentes)) {
                $sqlAlter = "ALTER TABLE `{$tabla}` DROP COLUMN `{$campo}`";
                $this->pdo->exec($sqlAlter);
            }

            // Registrar cambio si se eliminaron registros
            if ($totalEliminados > 0 && $this->registroCambioModel && isset($_SESSION['idusuario'])) {
                $this->registroCambioModel->registrarCambio(
                    $_SESSION['idusuario'],
                    null,
                    ($origen === 'extra') ? 'campos_extra' : 'diccionario_campos',
                    'eliminacion',
                    'tabla',
                    $tabla,
                    null,
                    "Eliminación de campos ({$origen}) para la tabla {$tabla}"
                );
            }

            $this->pdo->commit();
            return $totalEliminados;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Error al eliminar campos por tabla: " . $e->getMessage());
        }
    }
}
