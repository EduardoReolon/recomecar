<?php
require_once 'config/entity.php';

class Acompanhamento extends Entity {
    /**
     * @column
     * primary
     * */
    public int $id;
    /** @column */
    public ?int $id_pai;
    /** @column */
    public int $id_egresso;
    /** @column */
    public string $mensagem;
    /** @column */
    public DateTime $created_at;
}