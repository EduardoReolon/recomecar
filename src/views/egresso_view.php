<?php
require_once 'config/view_main.php';
require_once __DIR__ . '/../models/egresso.php';
require_once __DIR__ . '/../models/status.php';

class Egresso_view extends View_main {
    private Egresso $egresso;
    /** @var Status[] */
    private array $statuses;

    public function __construct() {
        $uri = Helper::getCurrentUri();
        preg_match('/\/egresso\/(\d+)/', $uri, $matches);
        $id_egresso = (int) $matches[1];
        if ($id_egresso === 0) {
            $this->egresso = new Egresso();
            $this->egresso->id = 0;
        } else $this->egresso = Egresso::findBy('id', $id_egresso);

        $this->statuses = Status::fetchSimpler();
        
        parent::__construct();
    }

    private function formDadosPessoais() {
        ?>
            <form action="<?php echo Helper::apiPath("egresso/{$this->egresso->id}") ?>" method="POST">
                <div class="row">
                    <label for="inputNome" class="col-sm-1 col-form-label">Nome:</label>
                    <div class="col-4">
                        <input name="nome" class="form-control" id="inputNome" type='text' value="<?php echo isset($this->egresso->nome) ? $this->egresso->nome : ''; ?>"/>
                    </div>
                    <label for="inputSobrenome" class="col-sm-1 col-form-label">Sobrenome:</label>
                    <div class="col-4">
                        <input name="sobrenome" class="form-control" id="inputSobrenome" type='text' value="<?php echo isset($this->egresso->sobrenome) ? $this->egresso->sobrenome : ''; ?>"/>
                    </div>
                </div>
                <div class="row">
                    <label for="inputCpf" class="col-sm-1 col-form-label">CPF:</label>
                    <div class="col-4">
                        <input name="cpf" class="form-control" id="inputCpf" type='text' value="<?php echo isset($this->egresso->cpf) ? $this->egresso->cpf : ''; ?>"/>
                    </div>
                    <label for="inputRg" class="col-sm-1 col-form-label">RG:</label>
                    <div class="col-4">
                        <input name="rg" class="form-control" id="inputRg" type='text' value="<?php echo isset($this->egresso->rg) ? $this->egresso->rg : ''; ?>"/>
                    </div>
                </div>
                <div class="row">
                    <label for="inputDataNascimento" class="col-sm-1 col-form-label">Data Nascimento:</label>
                    <div class="col-4">
                        <input name="data_nascimento" class="form-control" id="inputDataNascimento" type='date' value="<?php echo isset($this->egresso->data_nascimento) ? $this->egresso->data_nascimento->format('Y-m-d') : ''; ?>"/>
                    </div>
                    <label for="inputCep" class="col-sm-1 col-form-label">CEP:</label>
                    <div class="col-4">
                        <input name="cep" onchange="cepChange(event)" class="form-control" id="inputCep" type='text' value="<?php echo isset($this->egresso->cep) ? $this->egresso->cep : ''; ?>"/>
                    </div>
                </div>
                <div class="row">
                    <label for="inputIdEstado" class="col-sm-1 col-form-label">Estado:</label>
                    <div class="col-4 d-inline-block">
                        <select onchange="estadoChange(event)" name="id_estado" class="form-select" aria-label="Default select example" id="inputIdEstado">
                            <option value=""></option>
                        </select>
                    </div>
                    <label for="inputIdCidade" class="col-sm-1 col-form-label">Cidade:</label>
                    <div class="col-4 d-inline-block">
                        <select name="id_cidade" class="form-select" aria-label="Default select example" id="inputIdCidade">
                            <option value=""></option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <label for="inputRua" class="col-sm-1 col-form-label">Rua:</label>
                    <div class="col-4">
                        <input name="rua" class="form-control" id="inputRua" type='text' value="<?php echo isset($this->egresso->rua) ? $this->egresso->rua : ''; ?>"/>
                    </div>
                    <label for="inputNumero" class="col-sm-1 col-form-label">Número:</label>
                    <div class="col-1">
                        <input name="numero" class="form-control" id="inputNumero" type='text' value="<?php echo isset($this->egresso->numero) ? $this->egresso->numero : ''; ?>"/>
                    </div>
                </div>
                <div class="row">
                    <label for="inputComplemento" class="col-sm-1 col-form-label">Complem.:</label>
                    <div class="col-4">
                        <input name="complemento" class="form-control" id="inputComplemento" type='text' value="<?php echo isset($this->egresso->complemento) ? $this->egresso->complemento : ''; ?>"/>
                    </div>
                    <label for="inputBairro" class="col-sm-1 col-form-label">Bairro:</label>
                    <div class="col-4">
                        <input name="bairro" class="form-control" id="inputBairro" type='text' value="<?php echo isset($this->egresso->bairro) ? $this->egresso->bairro : ''; ?>"/>
                    </div>
                </div>
                <div class="row">
                    <label for="inputEmail" class="col-sm-1 col-form-label">Email:</label>
                    <div class="col-4">
                        <input name="email" class="form-control" id="inputEmail" type='text' value="<?php echo isset($this->egresso->email) ? $this->egresso->email : ''; ?>"/>
                    </div>
                    <label for="inputTelefone1" class="col-sm-1 col-form-label">Telef. 1:</label>
                    <div class="col-4">
                        <input name="telefone1" class="form-control" id="inputTelefone1" type='text' value="<?php echo isset($this->egresso->telefone1) ? $this->egresso->telefone1 : ''; ?>"/>
                    </div>
                </div>
                <div class="row">
                    <label for="inputTelefone2" class="col-sm-1 col-form-label">Telef. 2:</label>
                    <div class="col-4">
                        <input name="telefone2" class="form-control" id="inputTelefone2" type='text' value="<?php echo isset($this->egresso->telefone2) ? $this->egresso->telefone2 : ''; ?>"/>
                    </div>
                    <label for="inputTelefone3" class="col-sm-1 col-form-label">Telef. 3:</label>
                    <div class="col-4">
                        <input name="telefone3" class="form-control" id="inputTelefone3" type='text' value="<?php echo isset($this->egresso->telefone3) ? $this->egresso->telefone3 : ''; ?>"/>
                    </div>
                </div>
                <div class="row">
                    <label for="inputNomeContato" class="col-sm-1 col-form-label">Nome Contato:</label>
                    <div class="col-4">
                        <input name="nome_contato" class="form-control" id="inputNomeContato" type='text' value="<?php echo isset($this->egresso->nome_contato) ? $this->egresso->nome_contato : ''; ?>"/>
                    </div>
                    <label for="inputTelefoneContato" class="col-sm-1 col-form-label">Telefone Contato:</label>
                    <div class="col-4">
                        <input name="telefone_contato" class="form-control" id="inputTelefoneContato" type='text' value="<?php echo isset($this->egresso->telefone_contato) ? $this->egresso->telefone_contato : ''; ?>"/>
                    </div>
                </div>
                <div class="row">
                    <label for="inputObservacoes" class="col-sm-1 col-form-label">Observações:</label>
                    <div class="col-4">
                        <input name="observacoes" class="form-control" id="inputObservacoes" type='text' value="<?php echo isset($this->egresso->observacoes) ? $this->egresso->observacoes : ''; ?>"/>
                    </div>
                </div>
                <div class="row">
                    <input type="submit" value="Salvar" class="col-sm-1 btn btn-primary">
                </div>
            </form>
            <script>
                function populateSelect(selectId, list) {
                    // Obtém o elemento select pelo ID
                    const selectElement = document.getElementById(selectId);

                    // Limpa as opções atuais (se houver)
                    selectElement.innerHTML = '';

                    // Adiciona uma opção padrão vazia
                    const defaultOption = document.createElement('option');
                    defaultOption.value = '';
                    defaultOption.text = 'Selecione uma opção';
                    selectElement.appendChild(defaultOption);

                    // Ordena a lista alfabeticamente pelo nome
                    list.sort((a, b) => a.nome.localeCompare(b.nome));

                    // Itera pela lista e cria opções dinamicamente
                    list.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.id; // Atribui o ID do item como valor
                        option.text = item.nome; // Usa o nome como texto da opção
                        selectElement.appendChild(option);
                    });
                }
                (async () => {
                    populateSelect('inputIdEstado', await getEstados());
                    populateSelect('inputIdCidade', []);
                    <?php
                        if (isset($this->egresso->id_cidade)) {
                            ?>
                                const idEstado = <?php echo substr($this->egresso->id_cidade, 0, 2) ?>;
                                document.getElementById('inputIdEstado').value = idEstado;
                                populateSelect('inputIdCidade', await getCidadesByEstado(idEstado));
                                document.getElementById('inputIdCidade').value = <?php echo $this->egresso->id_cidade ?>;
                            <?php
                        }
                    ?>
                })();
                async function estadoChange(e) {
                    if (!e.target.value) return populateSelect('inputIdCidade', []);
                    const idEstado = parseInt(e.target.value);
                    populateSelect('inputIdCidade', await getCidadesByEstado(idEstado));
                }
                async function cepChange(e) {
                    try {
                        const info = await getEnderecoByCep(e.target.value);
                        
                        if (!info) return;

                        const idEstado = info.ibge.substring(0, 2);
                        document.getElementById('inputIdEstado').value = idEstado;
                        populateSelect('inputIdCidade', await getCidadesByEstado(idEstado));
                        document.getElementById('inputIdCidade').value = info.ibge;

                        document.getElementById('inputBairro').value = info.bairro;
                        document.getElementById('inputRua').value = info.logradouro;
                    } catch (error) {
                        console.log(error);
                    }
                }
            </script>
        <?php
    }

    private function formAcompanhamento() {
        ?>
            <h1>acompanhamento</h1>
        <?php
    }

    protected function body_content() {
        $nome = '';
        if (isset($this->egresso->nome)) $nome = $this->egresso->nome;
        if (isset($this->egresso->sobrenome)) $nome .= ' ' . $this->egresso->sobrenome;
        ?>
            <h1><?php echo $nome ?></h1>
            <?php
                if (!$this->egresso->isLocal) {
                    ?>
                        <label for="inputIdStatus" class="col-sm-1 col-form-label">Status:</label>
                        <div class="col-4 d-inline-block">
                            <select name="id_status" class="form-select" aria-label="Default select example" id="inputIdStatus">
                                <?php
                                    foreach ($this->statuses as $status) {
                                        ?>
                                            <option value="<?php echo $status->id ?>" <?php echo $status->id === $this->egresso->id_status ? 'selected' : '' ?>><?php echo $status->slug ?></option>
                                        <?php
                                    }
                                ?>
                            </select>
                        </div>
                    <?php
                }
            ?>
            <div class="container">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button onclick="showTab('Content-main', 'dados_pessoais')" class="nav-link"
                        id="dados_pessoais-tab" data-bs-toggle="tab" data-bs-target="#home-tab-pane"
                        type="button" role="tab" aria-controls="contact-tab-pane" aria-selected="true">
                        Dados pessoais
                    </button>
                </li>
                <?php
                    if (!$this->egresso->isLocal) {
                        ?>
                            <li class="nav-item" role="presentation">
                                <button onclick="showTab('Content-main', 'acompanhamento')" class="nav-link" id="acompanhamento-tab" data-bs-toggle="tab"
                                    data-bs-target="#dossie-tab-pane" type="button" role="tab" aria-controls="contact-tab-pane"
                                    aria-selected="false">
                                    Acompanhamento
                                </button>
                            </li>
                        <?php
                    }
                ?>
            </ul>
            <div class="tab-content" id="Content-main">
                <div class="tab-pane fade" id="dados_pessoais-tab-pane" role="tabpanel" aria-labelledby="home-tab" tabindex="0">
                    <?php $this->formDadosPessoais(); ?>
                </div>
                <?php
                    if (!$this->egresso->isLocal) {
                        ?>
                            <div class="tab-pane fade" id="acompanhamento-tab-pane" role="tabpanel" aria-labelledby="profile-tab" tabindex="0">
                                <?php $this->formAcompanhamento(); ?>
                            </div>
                        <?php
                    }
                ?>
            </div>
            <script>
                setHash(0, "<?php echo $this->egresso->isLocal ? 'dados_pessoais' : 'acompanhamento' ?>", true);
                
                const hashLevels = getHash();

                showTab('Content-main', hashLevels[0]);
            </script>
        </div>
        <?php
    }
}