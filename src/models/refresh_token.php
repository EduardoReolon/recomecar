<?php
require_once('config/entity.php');
require_once('user.php');

class Refresh_token extends Entity {
    protected static $table = 'refresh_token';
    protected $change_logs = false;

    /**
     * @column
     * primary */
    public string $token;
    /** @column */
    public int $user_id;
    /**
     * @column
     * custom
     */
    public datetime $created_at;

    public function getUser() {
        $usuarios = User::fetchSimpler([['id', '=', $this->user_id]]);

        if (empty($usuarios)) return false;

        return $usuarios[0];
    }
}