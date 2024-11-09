<?php
require_once('config/entity.php');

class User extends Entity {
    protected static $table = 'user';

    /**
     * @column
     * primary */
    public int $id;
    /** @column */
    public string $username;
    /**
     * @column
     * log_only_prop_name
     */
    protected ?string $password;
    /** @column */
    public ?string $name;
    /** @column */
    public ?string $surname;
    /** @column */
    public bool $hidden = false;
    /** @column */
    public bool $active = true;
    /**
     * @var string[] | null
     * @column
     * json
     */
    public ?array $roles;

    public function setPassword($password) {
        $this->password = $password;
    }

    protected function beforeSave() {
        if (!empty($this->password) && strlen($this->password) < 50) {
            $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        }
    }

    public function passwordCheck($password) {
        if (empty($password)) return false;
        return password_verify($password, $this->password);
    }
}