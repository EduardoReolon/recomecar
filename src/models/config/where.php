<?php
require_once __DIR__ . '/../../services/log.php';
require_once __DIR__ . '/../../services/helper.php';

class Where {
    /** @var string */
    private $field;
    /** @var string */
    private $condition;
    private $value;
    /** @var string */
    private $collate;

    /** @var string */
    private $group_clause;
    /** @var self[] */
    private $clauses = [];

    private function __construct() {}

    static function clause(string $field, string $condition, $value = null, bool $insensitive_accent_case = false) {
        if (preg_match('/^(in|not in)$/i', $condition) && !is_array($value)) throw new Exception("Esparado um array", 1);
        
        $where = new self();

        $where->field = $field;
        $where->condition = $condition;
        if ($value !== null) $where->value = $value;
        if ($insensitive_accent_case) $where->collate = ' COLLATE Latin1_general_CI_AI';
        return $where;
    }

    /** @return self */
    static function and() {
        $where = new self();
        $where->group_clause = 'and';
        return $where;
    }
    /** @return self */
    static function or() {
        $where = new self();
        $where->group_clause = 'or';
        return $where;
    }
    /**
     * @param self
     * @return self
     */
    function add($clause) {
        if (!isset($this->group_clause)) throw new Exception("Not a group", 1);
        $this->clauses[] = $clause;
        return $this;
    }

    private function getParamName(array $params): string {
        $name = '';
        do {
            $name = Helper::randomStr();
        } while (array_key_exists($name, $params));
        return $name;
    }

    public function getString(array &$params) {
        if (isset($this->group_clause)) {
            $statements = [];
            foreach ($this->clauses as $clause) {
                $str = $clause->getString($params);
                if (!empty($str) && !preg_match('/^\(+\)+$/', $str)) $statements[] = $str;
            }
            return '(' . implode(' ' . $this->group_clause . ' ', $statements) . ')';
        } else {
            $str = $this->field . ' ' . $this->condition;
            if (preg_match('/^(in|not in)$/i', $this->condition)) {
                $inValues = [];
                foreach ($this->value as $value) {
                    if (gettype($value) === 'integer' || gettype($value) === 'double') $inValues[] = $value;
                    else {
                        if ($value === null) continue;
                        $paramName = $this->getParamName($params);
                        $inValues[] = ':' . $paramName;
                        $params[$paramName] = $value;
                    }
                }
                if (count($inValues) === 0) return '';
                return $str . ' (' . implode(',', $inValues) . ')';
            }
            if (isset($this->value)) {
                $paramName = $this->getParamName($params);
                $params[$paramName] = $this->value;

                return $str . ' :' . $paramName . $this->collate;
            }
            return $str;
        }
    }
}