<?php
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../cambios/RegistroCambio.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
            $sql = "SELECT * FROM diccionario_campos";
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

    public function obtenerCamposPorTabla($tabla)
    {
        $sql = "SELECT * FROM diccionario_campos WHERE 1=1"; // base
        $params = [];

        if ($tabla !== null) {
            $sql .= " AND tabla = ?";
            $params[] = $tabla;
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
    }

    public function obtenerCamposExtra()
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

    public function obtenerCamposExtraPorTabla($idreferencia = null, $tabla = null)
    {
        try {
            $sql = "SELECT * FROM campos_extra WHERE 1=1"; // base
            $params = [];

            if ($idreferencia !== null) {
                $sql .= " AND idreferencia = ?";
                $params[] = $idreferencia;
            }

            if ($tabla !== null) {
                $sql .= " AND tabla = ?";
                $params[] = $tabla;
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

    public function obtenerCampoExtra($idcampo)
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

    public function obtenerCampoExtraPorTabla($tabla, $columna)
    {
        try {
            $sql = "SELECT * FROM campos_extra WHERE tabla = ? AND campo = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$tabla, $columna]);
            $campo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$campo) {
                return null; // evita errores si no hay resultado
            }

            switch ($campo['tipo_dato']) {
                case 'booleano':
                    $campo['valor_inicial'] = $campo['valor_inicial'] === '1' ? 'Sí' : 'No';
                    break;
                case 'fecha':
                    if ($campo['valor_inicial']) {
                        $campo['valor_inicial'] = date('Y-m-d', strtotime($campo['valor_inicial']));
                    }
                    break;
                case 'numero':
                    if ($campo['valor_inicial']) {
                        $campo['valor_inicial'] = (float)$campo['valor_inicial'];
                    }
                    break;
                case 'opciones':
                    if ($campo['valor_inicial']) {
                        $campo['valor_inicial'] = json_decode($campo['valor_inicial'], true);
                    }
                    break;
            }

            return $campo;
        } catch (Exception $e) {
            throw new Exception("Error al obtener campo extra: " . $e->getMessage());
        }
    }

    public function crearCampoExtra($data)
    {
        try {
            $tabla = $data['tabla'];
            $columna = $data['campo'];

            // 1. Verificar en metadata
            $existente = $this->obtenerCampoExtraPorTabla($tabla, $columna);
            $columnasExistentes = $this->registroCambioModel->obtenerCamposTabla($tabla);

            if (in_array($columna, $columnasExistentes)) {
                throw new Exception("El campo ya existe en la tabla " . $tabla);
            }

            if (empty($existente)) {
                // Insertar metadata
                $sql = "INSERT INTO campos_extra (idreferencia, tabla, campo, nombre, valor_inicial, tipo_dato, longitud) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    !empty($data['idreferencia']) ? $data['idreferencia'] : null,
                    $tabla,
                    $columna,
                    $data['nombre'],
                    $data['valor_inicial'] ?? null,
                    $data['tipo_dato'] ?? 'texto',
                    !empty($data['longitud']) ? $data['longitud'] : null
                ]);
                $idcampo = $this->pdo->lastInsertId();

                if (!empty($_SESSION['idusuario'])) {
                    $this->registroCambioModel->registrarCambio(
                        $_SESSION['idusuario'],
                        $idcampo,
                        'campo extra',
                        'creacion',
                        $columna,
                        null,
                        null,
                        "Campo extra creado en {$tabla}: " . $data['nombre']
                    );
                }
            } else {
                // Recuperar metadata existente
                $campo = $this->obtenerCampoExtraPorTabla($tabla, $columna);
                $idcampo = $campo['idcampo'];
            }

            $tipoSQL = $this->mapearTipoDato($data['tipo_dato'], $data['longitud']);
            $sqlAlter = "ALTER TABLE `{$tabla}` ADD COLUMN `{$columna}` {$tipoSQL}";
            $this->pdo->exec($sqlAlter);

            return $idcampo ?? null;
        } catch (Exception $e) {
            throw new Exception("Error al crear campo extra: " . $e->getMessage());
        }
    }

    public function actualizarCampoExtra($id, $data)
    {
        try {
            // 1. Obtener metadata actual
            $sqlCampo = "SELECT * FROM campos_extra WHERE idcampo = ?";
            $stmtCampo = $this->pdo->prepare($sqlCampo);
            $stmtCampo->execute([$id]);
            $campoAnterior = $stmtCampo->fetch(PDO::FETCH_ASSOC);

            if (!$campoAnterior) {
                throw new Exception("Campo extra no encontrado en metadata");
            }

            $tablaDestino = $data['tabla'];
            $columnasExistentes = $this->registroCambioModel->obtenerCamposTabla($tablaDestino);

            if (in_array($campoAnterior['campo'], $columnasExistentes)) {
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
                    !empty($data['longitud']) ? $data['longitud'] : null,
                    $id
                ]);

                // Alterar si cambió el nombre o tipo
                if ($campoAnterior['campo'] !== $data['campo'] || $campoAnterior['tipo_dato'] !== $data['tipo_dato']) {
                    $tipoSQL = $this->mapearTipoDato($data['tipo_dato'], $data['longitud']);
                    $sqlAlter = "ALTER TABLE `{$tablaDestino}` CHANGE `{$campoAnterior['campo']}` `{$data['campo']}` {$tipoSQL}";
                    $this->pdo->exec($sqlAlter);
                }
            } else {
                $this->crearCampoExtra($data);
            }

            // 5. Registrar cambio
            if (!empty($_SESSION['idusuario'])) {
                $this->registroCambioModel->registrarCambiosAutomaticos(
                    $_SESSION['idusuario'],
                    $id,
                    'campo extra',
                    'actualizacion',
                    $campoAnterior,
                    $data
                );
            }

            return true;
        } catch (Exception $e) {
            throw new Exception("Error al actualizar campo extra: " . $e->getMessage());
        }
    }

    public function eliminarCampo($id)
    {
        try {
            // 1. Obtener info del campo
            $campo = $this->registroCambioModel->resolverContextoEntidad("campo extra", $id);

            if (!$campo) {
                throw new Exception("El campo no existe en la metadata");
            }

            // 2. Resolver contexto
            $contexto = $this->registroCambioModel->resolverContextoEntidad($campo['tabla']);
            $tablaDestino = $contexto['tabla'];
            $columna = $campo['campo'];

            // 3. Verificar si la columna existe
            $columnasExistentes = $this->registroCambioModel->obtenerCamposTabla($tablaDestino);

            if (in_array($columna, $columnasExistentes)) {
                $sqlAlter = "ALTER TABLE `{$tablaDestino}` DROP COLUMN `{$columna}`";
                $this->pdo->exec($sqlAlter);
            }

            // 4. Eliminar metadata
            $sql = "DELETE FROM campos_extra WHERE idcampo = ?";
            $stmt = $this->pdo->prepare($sql);

            // 5. Registrar cambio
            if (!empty($_SESSION['idusuario'])) {
                $this->registroCambioModel->registrarCambio(
                    $_SESSION['idusuario'],
                    $id,
                    'campo extra',
                    'eliminacion',
                    null,
                    null,
                    null,
                    "Campo extra eliminado de tabla {$tablaDestino}: {$campo['nombre']}"
                );
            }

            $stmt->execute([$id]);
            return true;
        } catch (Exception $e) {
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
