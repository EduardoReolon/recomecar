<?php
require_once __DIR__ . '/../config/base_request.php';

class Egresso_request extends Base_request {
    public string $nome;
    public string $sobrenome;
    public string $cpf;
    public ?string $rg;
    public DateTime $data_nascimento;
    public string $cep;
    public int $id_cidade;
    public string $rua;
    public string $numero;
    public ?string $complemento;
    public string $bairro;
    public ?string $email;
    public ?string $telefone1;
    public ?string $telefone2;
    public ?string $telefone3;
    public ?string $nome_contato;
    public ?string $telefone_contato;
    public ?string $observacoes;
}