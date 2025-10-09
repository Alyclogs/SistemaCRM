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
        $sql = "SELECT * FROM diccionario_campos";
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

        if (!empty($wheres)) {
            $sql .= " WHERE " . implode(" AND ", $wheres);
        }

        $sql .= " ORDER BY COALESCE(`orden`, 9999) ASC, campo ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($data as &$row) {
            if (isset($row['meta']) && $row['meta'] !== null && $row['meta'] !== '') {
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

    /**
     * Obtener un registro por id
     */
    public function obtenerPorId($iddiccionario)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM diccionario_campos WHERE iddiccionario = ?");
        $stmt->execute([$iddiccionario]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (isset($row['meta']) && $row['meta'] !== null && $row['meta'] !== '') {
            $decoded = json_decode($row['meta'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $row['meta'] = $decoded;
            }
        } else {
            $row['meta'] = null;
        }
    }

    /**
     * Guardar (reemplazar) configuraciones para una tabla.
     * $tabla: string
     * $columnas: array de arrays con keys: campo, nombre, descripcion, tipo_dato, longitud, requerido, valor_inicial, contexto, visible, orden
     */
    public function guardarPorTabla($tabla, array $columnas)
    {
        try {
            $this->pdo->beginTransaction();

            // Borrar registros previos de esa tabla
            $stmtDel = $this->pdo->prepare("DELETE FROM diccionario_campos WHERE tabla = :tabla");
            $stmtDel->execute([':tabla' => $tabla]);

            // Nueva versión del INSERT con campo meta
            $sql = "INSERT INTO diccionario_campos
            (tabla, campo, nombre, descripcion, tipo_dato, longitud, requerido, valor_inicial, contexto, meta, visible, `orden`, fecha_creacion)
            VALUES (:tabla, :campo, :nombre, :descripcion, :tipo_dato, :longitud, :requerido, :valor_inicial, :contexto, :meta, :visible, :orden, NOW())";
            $stmtIns = $this->pdo->prepare($sql);

            foreach ($columnas as $c) {
                // Asegurar formato de meta
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
                    ':orden'         => isset($c['orden']) ? (int)$c['orden'] : 0
                ]);
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Actualiza un registro por id (campos dinámicos)
     * $data: array (keys: nombre, descripcion, visible, orden, tipo_dato, longitud, requerido, valor_inicial, contexto)
     */
    public function actualizar($iddiccionario, array $data)
    {
        // Incluir 'meta' en los campos permitidos
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
        $params = [':iddiccionario' => $iddiccionario];

        foreach ($data as $k => $v) {
            if (!in_array($k, $allowed, true)) continue;

            // Normalización según tipo
            if ($k === 'requerido' || $k === 'visible') {
                $v = !empty($v) ? 1 : 0;
            } elseif ($k === 'longitud' || $k === 'orden') {
                $v = ($v !== null && $v !== '') ? (int)$v : null;
            } elseif ($k === 'meta') {
                // Convertir array o stdClass a JSON
                if (is_array($v) || is_object($v)) {
                    $v = json_encode($v, JSON_UNESCAPED_UNICODE);
                }
            }

            $sets[] = "`$k` = :$k";
            $params[":$k"] = $v;
        }

        if (empty($sets)) return false;

        $sql = "UPDATE diccionario_campos SET " . implode(", ", $sets) . " WHERE iddiccionario = :iddiccionario";
        $stmt = $this->pdo->prepare($sql);
        $res = $stmt->execute($params);

        // registrar cambio (si el modelo de registro existe)
        if ($res && $this->registroCambioModel && isset($_SESSION['idusuario'])) {
            $this->registroCambioModel->registrarCambio(
                $_SESSION['idusuario'],
                $iddiccionario,
                'diccionario_campos',
                'actualizacion',
                null,
                json_encode($data, JSON_UNESCAPED_UNICODE),
                null,
                "Actualización diccionario_campos"
            );
        }

        return $res;
    }

    /**
     * Eliminar por id
     */
    public function eliminarPorId($iddiccionario)
    {
        $stmt = $this->pdo->prepare("DELETE FROM diccionario_campos WHERE iddiccionario = ?");
        return $stmt->execute([$iddiccionario]);
    }

    /**
     * Eliminar por tabla
     */
    public function eliminarPorTabla($tabla)
    {
        $stmt = $this->pdo->prepare("DELETE FROM diccionario_campos WHERE tabla = ?");
        return $stmt->execute([$tabla]);
    }
}
