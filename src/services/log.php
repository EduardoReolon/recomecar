<?php
require_once __DIR__ . '/../models/config/entity.php';
require_once('auth.php');

Class Log {
    // Constantes de classe para os tipos permitidos
    const TYPE_ERROR = 'error';
    const TYPE_CHANGE = 'change';
    const TYPE_ALERT = 'alert';
    const TYPE_EXCEPTION = 'exception';
    private const TYPE_GENERIC = 'generic';
    const TYPE_CONTROL = 'control';
    // private $level = LOG_ERR;
    private $type;
    private $msg = '';
    private $path;
    private ?string $tableName;
    private mixed $primary;
    private ?string $method;

    private function __construct() {}

    // /**
    //  * @param int $level LOG_EMERG | LOG_ALERT | LOG_CRIT | LOG_ERR | LOG_WARNING | LOG_NOTICE | LOG_INFO | LOG_DEBUG 
    //  */
    // public function setLevel($level) {
    //     $this->level = $level;
    // }

    /**
     * @param string $type self::TIPO_ERROR|self::TIPO_CHANGE
     */
    public static function new($type = self::TYPE_GENERIC) {
        $log = new self();
        $log->type = $type;

        if (!defined('__LOGS_ROOT_FOLDER__')) {
            define('__LOGS_ROOT_FOLDER__', realpath(__DIR__ . '/../../') . '/logs/');
        }
        $logs_root_folder = __LOGS_ROOT_FOLDER__;
        if (substr($logs_root_folder, -1) !== '/') $logs_root_folder .= '/';
        $log->path = $logs_root_folder . $type;

        return $log;
    }

    public function setTableName(string $tableName) {
        $this->tableName = $tableName;
        return $this;
    }
    public function setPrimary(mixed $value) {
        $this->primary = $value;
    }
    public function setMethod(string $method) {
        $this->method = $method;
        return $this;
    }
    public function setValueChanged(string $value) {
        $this->msg = $value;
        return $this;
    }

    public function setMessage(string $msg) {
        $this->msg .= $msg;
        return $this;
    }

    public function setError($errno, $errstr, $errfile, $errline) {
        $this->msg .= '(type=' . $errno . ') ' . $errstr . " em " . $errfile . " na linha " . $errline;
        return $this;
    }

    public function setException(Throwable $exception) {
        $this->msg .= $exception->getMessage() . " em " . $exception->getFile() . " na linha " . $exception->getLine();
        return $this;
    }

    public function setThrowable(Throwable $throwable) {
        $this->msg .= $throwable->getMessage() . " em " . $throwable->getFile() . " na linha " . $throwable->getLine();
        return $this;
    }

    public function __destruct() {
        if (Entity::$rollback && $this->type === self::TYPE_CHANGE) return;

        $now = new DateTime();

        // Definir o fuso horário para o Brasil
        $brasilTimeZone = new DateTimeZone('America/Sao_Paulo');
        $now->setTimezone($brasilTimeZone);

        $msg = $now->format('Y-m-d H:i:s') . ' | ' .
            Auth::uniqueLogonId() . ' | ' .
            (Auth::getUserId() ?: 0) . ' | ';
        if (isset($this->method)) $msg .= $this->method . ' | ';
        if (isset($this->primary)) $msg .= $this->primary . ' | ';
        $msg .= str_replace(["\r", "\n", "\r\n"], ' ', $this->msg) . "\n";

        $log_file = $this->path . '/' . $now->format('Y');
        if (!is_dir($log_file)) {
            mkdir($log_file, 0777, true);
        }
        $log_file .=  '/' . $now->format('m');
        if (isset($this->tableName)) $log_file .= '-' . $this->tableName;
        $log_file .=  '.txt';

        error_log($msg, 3, $log_file);
    }
}