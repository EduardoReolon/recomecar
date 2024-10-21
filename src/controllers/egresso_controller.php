<?php
require_once __DIR__ . '/../services/auth.php';
require_once __DIR__ . '/../models/egresso.php';

class Egresso_controller extends Base_controller {
    static protected array $map = ['api', 'v1', 'egresso'];
    /**
     * @request
     * map /:id_egresso
     * method post
     */
    static public function store(Egresso_request $request, Http_response $response) {
        $id_egresso = (int) RouteParams::get('id_egresso');

        $egresso = null;
        if ($id_egresso === 0) {
            $egresso = new Egresso();
        } else {
            $egresso = Egresso::findBy('id', $id_egresso);
            if (!isset($egresso)) return $response->status(404)->sendAlert('Cadastro não encontrado');
        }

        $egresso->nome = $request->nome;
        $egresso->sobrenome = $request->sobrenome;
        $egresso->cpf = $request->cpf;
        $egresso->rg = $request->rg;
        $egresso->data_nascimento = $request->data_nascimento;
        $egresso->cep = $request->cep;
        $egresso->id_cidade = $request->id_cidade;
        
        $egresso->rua = $request->rua;
        $egresso->numero = $request->numero;
        $egresso->complemento = $request->complemento;
        $egresso->bairro = $request->bairro;
        $egresso->email = $request->email;
        $egresso->telefone1 = $request->telefone1;
        $egresso->telefone2 = $request->telefone2;
        $egresso->telefone3 = $request->telefone3;
        $egresso->nome_contato = $request->nome_contato;
        $egresso->telefone_contato = $request->telefone_contato;
        $egresso->observacoes = $request->observacoes;

        $egresso->save();

        if ($id_egresso === 0) $response->redirectUser(Helper::uriRoot("egresso/{$egresso->id}"));
    }

    /**
     * @request
     * map /:id_egresso/status
     * method post
     */
    static public function storeStatus(Egresso_status_request $request, Http_response $response) {
        $id_egresso = (int) RouteParams::get('id_egresso');

        $egresso = Egresso::findBy('id', $id_egresso);
        if (!isset($egresso)) return $response->status(404)->sendAlert('Cadastro não encontrado');

        $egresso->id_status = $request->id_status;

        $egresso->save();
    }
}