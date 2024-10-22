<?php
require_once 'config/view_main.php';
require_once __DIR__ . '/../models/egresso.php';
require_once __DIR__ . '/../models/status.php';
require_once __DIR__ . '/components/components.php';

class Egressos_view extends View_main {
    private function formLista() {
        $statuses = Status::fetchSimpler();
        $ids_status = [];

        $filtroNome = '';
        
        $queryParams = Helper::bracketless_input('GET');
        
        $page = key_exists('page', $queryParams) ? intval($queryParams['page']) : 1;
        $per_page = key_exists('per_page', $queryParams) ? intval($queryParams['per_page']) : 20;
        
        if (key_exists('ids_status', $queryParams)) {
            if (is_array($queryParams['ids_status'])) {
                foreach ($queryParams['ids_status'] as $id_status) {
                    $ids_status[] = (int) $id_status;
                }
            } else $ids_status[] = (int) $queryParams['ids_status'];
        } else {
            foreach ($statuses as $status) {
                if (!$status->ativo) continue;
                $ids_status[] = $status->id;
            }
        }

        if (key_exists('filtroNome', $queryParams)) $filtroNome = $queryParams['filtroNome'];

        $where = Where::and();
        $where->add(Where::clause('id_status', 'in', $ids_status));

        if (!empty($filtroNome)) {
            $where->add(Where::clause('CONCAT(nome, \' \', sobrenome)', 'like', "%{$filtroNome}%"));
        }
        
        $query = Egresso::query();
        $query->order_by('nome');
        $query->where($where);

        $egressos = Egresso::fetchPaged($query, $page, $per_page, $page_info);

        $arr_status = [];
        foreach ($statuses as $status) {
            $arr_status[] = new Obj_multiSelect(in_array($status->id, $ids_status), 'ids_status', $status->id, $status->slug);
        }

        $query_filter = implode('&', array_map(function($id) {return "ids_status={$id}";}, $ids_status));

        ?>
            <form action="" method="get">
                <?php $page_info->hiddenInputs() ?>
                <label>
                    Nome:
                    <div class="d-inline-block">
                        <input type="text" class="form-control" name="filtroNome" value="<?php echo $filtroNome ?>">
                    </div>
                </label>
                <label>
                    Status:
                    <?php Components::multiSelect($arr_status, 'Nenhum selecionado', '<br>') ?>
                </label>
                <input type="submit" value="Buscar" class="btn btn-primary">
                <a class="btn btn-primary" href="<?php echo Helper::uriRoot('/egresso/0') ?>" role="button">Novo cadastro</a>
            </form>
            
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th scope="col"></th>
                        <th scope="col">Nome</th>
                        <th scope="col">CPF</th>
                        <th scope="col">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        foreach ($egressos as $egresso) {
                            $egresso->loadStatus();
                            ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo Helper::uriRoot("egresso/{$egresso->id}") ?>">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16">
                                                <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/>
                                            </svg>
                                        </a>
                                    </td>
                                    <td><?php echo $egresso->nome . ' ' . $egresso->sobrenome ?></td>
                                    <td><?php echo $egresso->cpf ?></td>
                                    <td><?php echo $egresso->status->slug ?></td>
                                </tr>
                            <?php
                        }
                    ?>
                </tbody>
            </table>
        <?php

        // numeros paginação
        $page_info->htmlPagesNumbers($query_filter);
    }

    protected function body_content() {
        $this->formLista();
    }
}