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

    public function loadAcompanhamentos(bool $loadUsuers = false) {
        if (isset($this->acompanhamentos)) return;

        $query = Acompanhamento::query();
        $query->order_by('created_at', 'DESC');
        $query->where(Where::clause('id_egresso', '=', $this->id));
        $this->acompanhamentos = Acompanhamento::fetch($query);

        if ($loadUsuers) {
            $idsUsuarios = [];
            foreach($this->acompanhamentos as $acompanhamento) {
                $idsUsuarios[] = $acompanhamento->id_usuario;
            }

            $users = User::fetchSimpler([['id', 'in', $idsUsuarios]]);

            foreach ($this->acompanhamentos as $acompanhamento) {
                foreach ($users as $user) {
                    if ($user->id !== $acompanhamento->id_usuario) continue;
                    $acompanhamento->user = $user;
                    break;
                }
            }
        }
    }

    protected function beforeSave() {
        if (!isset($this->id_status)) $this->id_status = 1;
    }
}