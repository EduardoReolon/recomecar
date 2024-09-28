<?php
require_once('database.php');
require_once('query.php');
require_once('page.php');
require_once __DIR__ . '/../../services/log.php';

class Column {
    public bool $isColumn = false;
    public string $prop_name;
    public string $col_name;
    public bool $primary = false;
    public bool $json = false;
    public string $type = 'string';
    public bool $log_only_prop_name = false; // only the property name goes to log
    public bool $skip_log = false; // do not go to log at all
    public bool $private;

    public function __construct(ReflectionProperty $property) {
        $comentarios = $property->getDocComment();
        
        preg_match('/@column.*/s', $comentarios, $matches);
        
        if (empty($matches[0])) return;
        else {
            $this->isColumn = true;

            $this->prop_name = $property->getName();
            $this->col_name = $this->prop_name;
            if ($property->getType()) $this->type = $property->getType()->getName();

            // Verificar se a propriedade é publica ou privada
            if ($property->getModifiers() & ReflectionProperty::IS_PUBLIC) {
                $this->private = false;
            } else if ($property->getModifiers() & ReflectionProperty::IS_PRIVATE) {
                $this->private = true;
            } else if ($property->getModifiers() & ReflectionProperty::IS_PROTECTED) {
                $this->private = true;
            }

            $linhas = explode("\n", trim($matches[0]));
            array_shift($linhas); // a primeira linha é @column

            foreach ($linhas as $linha) {
                $partes = preg_split('/(?<=\w)(( *\| *)| +|=)/', $linha);
                $chave = trim($partes[0]);
                $valor = isset($partes[1]) ? trim($partes[1]) : null;

                if (preg_match('/col.*name/i', $chave)) {
                    if (!empty($valor)) $this->col_name = $valor;
                } elseif (preg_match('/primary/i', $chave)) {
                    $this->primary = true;
                } elseif (preg_match('/json/i', $chave)) {
                    $this->json = true;
                } elseif (preg_match('/log_only_prop_name/i', $chave)) {
                    $this->log_only_prop_name = true;
                } elseif (preg_match('/skip_log/i', $chave)) {
                    $this->skip_log = true;
                } elseif (preg_match('/@var/i', $chave)) {
                    if (!empty($valor)) $this->type = $valor;
                }
            }
        }
    }
}

// classe para os valores que sofreram alteração
class KeyValue {
    public Column $col;
    public $value;

    public function __construct(Column $col, $value) {
        $this->col = $col;
        $this->value = $value;
    }
}

class Entity {
    // propriedades que podem/devem estar nos models
    protected static $table = '';
    protected $start_values = array();
    protected $log = []; // 
    protected $change_logs = true; // se true, as alterações nessa tabela vao para os logs

    // nos models, é possível utilizar os seguintes comentários
    /**
     * @column
     * @var string|int|float|bool|datetime
     */
    // public $id;
    // protected $id;
    // não utilizar private
    
    // propriedades só em Entity
    /** @var array  */
    protected static $_columns = [];
    public $isLocal = true;
    protected static $logs = array();
    protected static $db;
    public static $rollback = false;

    public function __construct() {
        self::$db = Database::getInstance()->getConnection();
    }

    public static function commitTransaction() {
        try {
            // Confirma a transação
            if (isset(self::$db)) self::$db->commit();
        } catch (PDOException $e) {
            self::$rollback = true;
            Log::new(Log::TYPE_EXCEPTION)->setException($e);
        }
    }

    public static function rollBackTransaction() {
        self::$rollback = true;
        try {
            // Reverte a transação
            if (isset(self::$db)) self::$db->rollBack();
        } catch (PDOException $e) {
            Log::new(Log::TYPE_EXCEPTION)->setException($e);
        }
    }

    /** @return Query */
    public static function query() {
        return new Query(static::$table);
    }

    /**
     * @param Query $query
     */
    public static function fetchNotIntoModel($query): array {
        new self();
        return $query->execQuery(self::$db);
    }

    /**
     * @param Query $query
     * @return static[]
     */
    public static function fetch($query): array {
        try {
            new self();
            $resultados = $query->execQuery(self::$db);
    
            $entidades = [];
    
            foreach ($resultados as $registro) {
                // Criar uma instância da classe Entity para cada registro
                $entidade = new static();
                $entidade->setProperties($registro); // Método para definir as propriedades da classe Entity
                $entidades[] = $entidade;
            }
    
            return $entidades;
        } catch (\Throwable $th) {
            Log::new(Log::TYPE_ERROR)->setThrowable($th);
            return [];
        }
    }

    /**
     * @param Query $query
     * @param Page $page_info
     * @return static[]
     */
    public static function fetchPaged($query, int $page, int $per_page, &$page_info = 0) {
        new self();
        $query->rows_count = true;
        $rows_count = (int) $query->execQuery(self::$db)[0]['rows_count'];
        $query->rows_count = false;

        if (!$query->ordered()) {
            $primary = '';
            $columns = static::getColumns();
            foreach ($columns as $col) {
                if ($col->primary) {
                    $primary = $col->col_name;
                    break;
                }
            }
            $query->order_by($primary);
        }

        $query->paged($page, $per_page);

        $page_info = new Page(
            $page,
            $per_page,
            $rows_count
        );
        return static::fetch($query);
    }

    /**
     * @return static|null
     */
    public static function findBy(string $field, $value) {
        try {
            $query = Static::query();
            $query->where(Where::clause($field, '=', $value));

            $entities = Static::fetch($query);
            if (empty($entities)) return null;
            return $entities[0];
        } catch (\Throwable $th) {
            return null;
        }
    }

    /**
     * @param (string|array)[][] $conditions
     * @return static[]
     */
    public static function fetchSimpler($conditions = []): array {
        try {
            $query = Static::query();
            if (!empty($conditions)) {
                $where = Where::and();
                    
                foreach ($conditions as $condition) {
                    if (count($condition) > 2) $where->add(Where::clause($condition[0], $condition[1], $condition[2]));
                    else $where->add(Where::clause($condition[0], $condition[1]));
                }
    
                $query->where($where);
            }
            return Static::fetch($query);
        } catch (\Throwable $th) {
            Log::new(Log::TYPE_ERROR)->setThrowable($th);
            return [];
        }
    }

    protected function setProperties($properties) {
        $cols = static::getColumns();
        foreach ($cols as $col) {
            $property_name = $col->prop_name;
            if (array_key_exists($col->col_name, $properties)) {
                $value = null;
                if ($col->json) {
                    if (!empty($properties[$col->col_name])) {
                        $value = json_decode($properties[$col->col_name], true);
                    }
                } else $value = Helper::castValue($properties[$col->col_name], $col->type);

                $this->start_values[$property_name] = $value;
                $this->$property_name = $value;
            }
        }
        $this->isLocal = false;
    }

    // Função estática para obter propriedades com a anotação @column
    /**
     * @return Column[]
     */
    private static function getColumns(): array {
        $static_name = static::class;
        if (array_key_exists($static_name, self::$_columns)) return self::$_columns[$static_name];

        $reflexao = new ReflectionClass(static::class);
        $properties = $reflexao->getProperties();

        $columns = [];
        foreach ($properties as $property) {
            $column = new Column($property);
            if ($column->isColumn) $columns[] = $column;
        }

        self::$_columns[$static_name] = $columns;
        return $columns;
    }

    /** @return keyValue[] */
    protected function keysValues() {
        $keysValues = [];

        $logChangesPublic = array();
        $logChangesPrivate = array();

        $cols = static::getColumns();
        foreach ($cols as $col) {
            $valueBefore = null;
            $property_name = $col->prop_name;
            $value = isset($this->$property_name) ? Helper::castValue($this->$property_name, $col->type) : null;

            if (array_key_exists($property_name, $this->start_values)) {
                if (!$col->primary && $value === $this->start_values[$property_name]) continue;
                if ($value instanceof DateTime && $this->start_values[$property_name] instanceof Datetime) {
                    $diff = $value->diff($this->start_values[$property_name], true);
                    if ($diff->y === 0 && $diff->m === 0 && $diff->d === 0 && $diff->h === 0 && $diff->i === 0 && $diff->s === 0) continue;
                }
                $valueBefore = $this->start_values[$property_name];
            } else if (!$col->primary && !isset($value)) continue;

            if ($col->json && $value !== null) $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            $keysValues[] = new KeyValue($col, $value);
            if (!$col->primary && !$col->skip_log) {
                if ($col->log_only_prop_name) $logChangesPrivate[] = $col->col_name;
                else $logChangesPublic[$col->col_name] = $valueBefore;
            }
        }

        $this->log = [];
        $this->log['private'] = $logChangesPrivate;
        $this->log['public'] = $logChangesPublic;

        return $keysValues;
    }

    protected function beforeSave() {}

    /** @return bool true para persistido, false para quando não a necessidade de persistir */
    public function save(): bool {
        static::beforeSave();

        $keysValues = static::keysValues();

        $toInsert = $this->isLocal;

        if (empty($keysValues) && !$toInsert) return false;

        $primaryKeyDB = '';
        $primaryKeyPropName = '';
        $primaryKeyType = 'string';

        $query = '';
        $keysArray = [];

        foreach ($keysValues as $keyValue) {
            if ($keyValue->col->primary) {
                $primaryKeyDB = $keyValue->col->col_name;
                $primaryKeyPropName = $keyValue->col->prop_name;
                $primaryKeyType = $keyValue->col->type;
            }

            if (!$keyValue->col->primary || (!empty($keyValue->value) && $toInsert)) {
                $keysArray[] = "{$keyValue->col->col_name}";
            }
        }
        if (empty($keysArray) && !$toInsert) return false;

        $method = '';

        if ($toInsert) {
            $method = 'insert';
            if (empty($keysArray)) {
                $query = "INSERT INTO " . static::$table . " default values";
            } else {
                $query = "INSERT INTO " . static::$table . " (";
    
                $query .= implode(', ', $keysArray);
                $query .= ") VALUES (";
                $query .= implode(', ', array_map(function ($key) {
                    return ":{$key}";
                }, $keysArray));
                $query .= ")";
            }
        } else {
            $method = 'update';
            $query = "UPDATE " . static::$table . " SET ";

            $query .= implode(', ', array_map(function ($key) {
                return "{$key} = :{$key}";
            }, $keysArray));
            $query .= " WHERE " . $primaryKeyDB . " = :" . $primaryKeyDB;
            $keysArray[] = $primaryKeyDB;
        }

        $statement = self::$db->prepare($query);

        foreach ($keysValues as $keyValue) {
            if ($toInsert && $keyValue->col->primary && empty($keyValue->value)) continue;
            try {
                if ($keyValue->value instanceof DateTime) {
                    $statement->bindValue(":{$keyValue->col->col_name}", $keyValue->value->format('Y-m-d H:i:s'));
                } else $statement->bindValue(":{$keyValue->col->col_name}", $keyValue->value);
            } catch (\Throwable $th) {}
        }

        try {
            $statement->execute();
        } catch (\Throwable $th) {
            $params = [];
            if (__ENV__ === 'dev') {
                foreach ($keysValues as $keyValue) {
                    $params[$keyValue->col->col_name] = $keyValue->value;
                }
            }
            $params = json_encode($params);
            Log::new(Log::TYPE_ERROR)->setThrowable($th)->setMessage(" - Query: {$query} - Values: {$params}");
            throw $th;
        }

        if ($toInsert) {
            $primaryNew = self::$db->lastInsertId();
    
            if (!empty($primaryNew)) {
                $this->$primaryKeyPropName = Helper::castValue($primaryNew, $primaryKeyType);
            }
        }
        $this->isLocal = false;
        
        if ($this->change_logs) {
            $log = Log::new(Log::TYPE_CHANGE)->setTableName(static::$table)->setMethod($method);
            $log->setPrimary($this->$primaryKeyPropName);
            self::$logs[] = $log;
            $this->log['primary'] = $this->$primaryKeyPropName;
            $log->setValueChanged(json_encode($this->log, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }

        // atualiza os valores iniciais, para o caso de chamar a função save novamente no código
        foreach ($keysValues as $keyValue) {
            $this->start_values[$keyValue->col->prop_name] = $keyValue->value;
        }

        return true; // Indica que a operação foi bem-sucedida
    }

    public function delete(): bool {
        if ($this->isLocal) return true;

        $keysValues = static::keysValues();

        $primaryKeyDB = '';
        $primaryKeyPropName = '';
        $primaryValue = null;

        foreach ($keysValues as $keyValue) {
            if ($keyValue->col->primary) {
                $primaryKeyDB = $keyValue->col->col_name;
                $primaryKeyPropName = $keyValue->col->prop_name;
                $primaryValue = $keyValue->value;
            }
        }

        $query = "DELETE FROM " . static::$table . " WHERE {$primaryKeyDB} = :{$primaryKeyDB}";

        $statement = self::$db->prepare($query);
        $statement->bindParam(":{$primaryKeyDB}", $primaryValue);

        $statement->execute();

        if ($this->change_logs) {
            $log = Log::new(Log::TYPE_CHANGE)->setTableName(static::$table)->setMethod('delete');
            $log->setPrimary($this->$primaryKeyPropName);
            self::$logs[] = $log;
            $log->setValueChanged(json_encode([
                'primary'=>$this->$primaryKeyPropName,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }

        return true; // Indica que a operação foi bem-sucedida
    }
}