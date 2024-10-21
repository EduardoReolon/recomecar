<?php
require_once __DIR__ . '/config/entity.php';
require_once 'status.php';
require_once 'acompanhamento.php';

class Egresso extends Entity {
    protected static $table = 'egresso';
    public Status $status;
    /** @var Acompanhamento[] */
    public array $acompanhamentos;
    
    /**
     * @column
     * primary
     * */
    public int $id;
    /** @column */
    public string $nome;
    /** @column */
    public string $sobrenome;
    /** @column */
    public string $cpf;
    /** @column */
    public int $id_status;
    /** @column */
    public ?string $rg;
    /** @column */
    public DateTime $data_nascimento;
    /** @column */
    public int $id_cidade;
    /** @column */
    public ?string $cep;
    /** @column */
    public ?string $rua;
    /** @column */
    public ?string $numero;
    /** @column */
    public ?string $bairro;
    /** @column */
    public ?string $complemento;
    /** @column */
    public ?string $email;
    /** @column */
    public ?string $telefone1;
    /** @column */
    public ?string $telefone2;
    /** @column */
    public ?string $telefone3;
    /** @column */
    public ?string $nome_contato;
    /** @column */
    public ?string $telefone_contato;
    /** @column */
    public ?string $observacoes;
    /** @column */
    public DateTime $updated_at;
    /** @column */
    public DateTime $created_at;

    public function loadStatus() {
        if (isset($this->status)) return;

        $this->status = Status::findBy('id', $this->id_status);
    }

    public function loadAcompanhamentos() {
        if (isset($this->acompanhamentos)) return;

        $this->acompanhamentos = Acompanhamento::fetchSimpler([['id_egresso', '=', $this->id]]);
    }

    protected function beforeSave() {
        if (!isset($this->id_status)) $this->id_status = 1;
    }
}