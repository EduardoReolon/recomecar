<?php
require_once __DIR__ . '/../config/base_request.php';

class Register_request extends Base_request {
    public string $username;
    public string $name;
    public string $surname;
    public ?string $password;
    public ?string $password_repeat;
}