<?php
require_once __DIR__ . '/config/entity.php';

class Status extends Entity {
    protected static $table = 'status';
    /** @var static[] */
    public static $statuses;
    
    /**
     * @column
     * primary
     * */
    public int $id;
    /** @column */
    public string $nome;
    /** @column */
    public string $slug;

    /** @return static[] */
    public static function fetchSimpler($conditions = array()): array {
        if (!isset(self::$statuses)) {
            self::$statuses = [];
            $values = [
                [ 'id'=>1, 'nome'=>'cadastro_incompleto', 'slug'=>'Cadstro incompleto' ],
            ];
            foreach ($values as $value) {
                $status = new self();
                $status->id = $value['id'];
                $status->nome = $value['nome'];
                $status->slug = $value['slug'];
                self::$statuses[] = $status;
            }
        }

        return self::$statuses;
    }
}