<?php
require_once __DIR__ . '/../../services/log.php';
require_once('where.php');

class Query {
    /** @var bool */
    public $rows_count = false;

    /** @var string[] */
    private $selects = [];

    /** @var string[] */
    private $joins = [];

    /** @var string */
    private $table;

    /** @var Where */
    private $where;

    /** @var string[] */
    private $order_by = [];

    /** @var bool */
    private $paged = false;
    /** @var int */
    private $page;
    /** @var int */
    private $per_page;
    private array $params = [];

    /** @param Entity $model */
    public function __construct(string $table){
        $this->table = $table;
    }

    public function select(string $str) {
        $this->selects[] = $str;
    }

    private function addJoin(string $equijoin, string $table_target, string $leftJoin, string $rightJoin) {
        $this->joins[] = " {$equijoin} join {$table_target} on {$leftJoin} = {$rightJoin}";
    }

    public function leftJoin(string $table_target, string $leftJoin, string $rightJoin) {
        $this->addJoin('left', $table_target, $leftJoin, $rightJoin);
    }

    public function innerJoin(string $table_target, string $leftJoin, string $rightJoin) {
        $this->addJoin('inner', $table_target, $leftJoin, $rightJoin);
    }
    
    public function getQuery() {
        $query = "SELECT ";

        if ($this->rows_count) $query .= 'COUNT(*) as rows_count';
        else if (!empty($this->selects)) {
            $query .= implode(', ', $this->selects);
        } else {
            $query .= "{$this->table}.*";
        }
        $query .= " FROM " . $this->table . implode('', $this->joins);
        
        $this->params = [];

        if (isset($this->where)) {
            $strWhere = $this->where->getString($this->params);
            if (strlen($strWhere) > 2) $query .= ' where ' . $strWhere;
        }

        if ($this->rows_count === false) {
            if (!empty($this->order_by)) $query .= ' ORDER BY ' . implode(',', $this->order_by);

            if ($this->paged) {
                // ORDER BY id OFFSET 10 ROWS FETCH NEXT 10 ROWS ONLY;
                // $query .= ' OFFSET ' . ($this->page - 1) * $this->per_page . ' ROWS FETCH NEXT ' . $this->per_page . ' ROWS ONLY';
                $query .= ' LIMIT ' . $this->per_page . ' OFFSET ' . ($this->page - 1) * $this->per_page;
            }
        }
        return $query;
    }
    
    public function execQuery($db): array {
        $statement = $db->prepare($this->getQuery());
        foreach ($this->params as $key => $param) {
            if ($param instanceof DateTime) {
                $statement->bindValue(":{$key}", $param->format('Y-m-d H:i:s'));
            } else $statement->bindValue(":{$key}", $param);
        }
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function order_by(string $field, string $direction = 'ASC') {
        if (empty($field)) throw new Exception("Necessário informar o campo a ser ordenado", 1);
        if (empty($direction) || strcasecmp($direction, 'ASC') === 0) $this->order_by[] = $field;
        else if (strcasecmp($direction ?: '', 'DESC') === 0) $this->order_by[] = $field . ' DESC';
        else throw new Exception("Direção de ordenação incorreta: " . $direction, 1);
    }
    public function ordered(): bool {
        return !empty($this->order_by);
    }

    public function paged(int $page, int $per_page) {
        $this->paged = true;
        $this->page = $page;
        $this->per_page = $per_page;
    }

    /** @param Where */
    public function where($where) {
        $this->where = $where;
        return $this;
    }
}