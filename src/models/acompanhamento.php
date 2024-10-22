<?php
require_once 'config/entity.php';
require_once 'user.php';

class Acompanhamento extends Entity {
    protected static $table = 'acompanhamento';
    public User $user;

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
    public int $id_usuario;
    /** @column */
    public string $mensagem;
    /** @column */
    public DateTime $created_at;

    public function loadUser() {
        if (isset($this->user)) return;

        $this->user = User::fetchSimpler([['id', '=', $this->id_usuario]]);
    }
}