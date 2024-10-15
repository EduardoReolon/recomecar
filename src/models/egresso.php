<?php
require_once __DIR__ . '/config/entity.php';

class Egresso extends Entity {
    protected static $table = 'egresso';
    
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
    public int $status;
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
}