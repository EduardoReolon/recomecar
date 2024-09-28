<?php
class Database {
    private static $instance;
    private $connection;

    private $db_driver;
    private $db_host;
    private $db_name;
    private $db_user;
    private $db_password;
    
    private function __construct() {
        $this->db_driver = __SGBD_DRIVER__;
        $this->db_host = __SGBD_SERVER__;
        $this->db_name = __SGBD_DB__;
        $this->db_user = __SGBD_USER__;
        $this->db_password = __SGBD_PASS__;

        // $dsn = $this->db_driver . ":Server=" . $this->db_host . ";Database=" . $this->db_name . '; Encrypt=false; TrustServerCertificate=yes;';
        $dsn = $this->db_driver . ":host=" . $this->db_host . ";dbname=" . $this->db_name;
        try {
            $this->connection = new PDO($dsn, $this->db_user, $this->db_password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Verifica se uma transação já está em andamento
            if (!$this->connection->inTransaction()) {
                // Inicia a transação somente se não houver uma em andamento
                $this->connection->beginTransaction();
            }
        } catch (PDOException $e) {
            Log::new(Log::TYPE_EXCEPTION)->setException($e);
            die();
        }
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    public function commit() {
        // Confirma a transação apenas se foi iniciada por esta instância
        if ($this->connection->inTransaction()) {
            $this->connection->commit();
        }
    }

    public function rollBack() {
        // Reverte a transação apenas se foi iniciada por esta instância
        if ($this->connection->inTransaction()) {
            $this->connection->rollBack();
        }
    }

    public function __destruct() {
        $this->commit();
    }
}