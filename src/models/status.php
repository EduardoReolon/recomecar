<?php
require_once __DIR__ . '/config/entity.php';

class Status extends Entity {
    protected static $table = 'status';
    /** @var static[] */
    public static $statuses;
    
    /**
     * @column
     * primary
     * */
    public int $id;
    /** @column */
    public string $nome;
    /** @column */
    public string $slug;
    /** @column */
    public bool $ativo = true;

    public static function findBy(string $field, $value): ?static {
        static::fetchSimpler();

        foreach (self::$statuses as $status) {
            if ($status->$field === $value) return $status;
        }

        return null;
    }

    /** @return static[] */
    public static function fetchSimpler($conditions = array()): array {
        if (!isset(self::$statuses)) {
            self::$statuses = [];
            $values = [
                // apenas externo
                [ 'id'=>1, 'nome'=>'cadastro_externo', 'slug'=>'Cadastro externo' ],
                
                // inativo
                [ 'id'=>2, 'nome'=>'nao_elegivel', 'slug'=>'Não elegível', 'ativo'=>false ],
                [ 'id'=>3, 'nome'=>'duplicidade', 'slug'=>'Duplicidade', 'ativo'=>false ],
                [ 'id'=>4, 'nome'=>'desistencia_previa', 'slug'=>'Desistência prévia', 'ativo'=>false ],
                [ 'id'=>5, 'nome'=>'desistencia', 'slug'=>'Desistência', 'ativo'=>false ],
                [ 'id'=>6, 'nome'=>'auto_suficiente', 'slug'=>'Auto-suficiente', 'ativo'=>false ],
                
                // agendamentos
                [ 'id'=>7, 'nome'=>'agendado_confirmacao_dados', 'slug'=>'Agendado para confirmação dos dados' ],
                [ 'id'=>8, 'nome'=>'agendado_curso_reabilitacao', 'slug'=>'Agendado curso de reabilitação' ],

                // processos
                [ 'id'=>9, 'nome'=>'em_curso_reabilitacao', 'slug'=>'Em curso de reabilitação' ],
                [ 'id'=>10, 'nome'=>'assessoria_acompanhamento', 'slug'=>'Em assessoria/acompanhamento' ],
            ];
            foreach ($values as $value) {
                $status = new self();
                $status->id = $value['id'];
                $status->nome = $value['nome'];
                $status->slug = $value['slug'];
                if (key_exists('ativo', $value)) $status->ativo = $value['ativo'];
                self::$statuses[] = $status;
            }
        }

        return self::$statuses;
    }
}