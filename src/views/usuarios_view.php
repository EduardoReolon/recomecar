<?php
require_once 'config/view_main.php';
require_once __DIR__ . '/../models/user.php';

class Usuarios_view extends View_main {
    private function formLista() {
        $query = User::query();
        $query->order_by('name');
        $query->where(Where::clause('id', '<>', 1));

        $usuarios = User::fetch($query);
        
        $novo = new User();
        $novo->id = 0;
        $usuarios[] = $novo;


        ?>
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">E-mail</th>
                        <th scope="col">Nome</th>
                        <th scope="col">Sobrenome</th>
                        <th scope="col">Senha<br>*Deixe em branco para não alterar</th>
                        <th scope="col">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        foreach ($usuarios as $usuario) {
                            ?>
                                <tr>
                                    <form <?php echo $usuario->id === 0 ? 'refresh-page' : '' ?> action="<?php echo Helper::apiPath("auth/{$usuario->id}") ?>" method="POST">
                                        <td><?php echo $usuario->id === 0 ? 'novo' : $usuario->id ?></td>
                                        <td>
                                            <input type="text" style="width: 170px;" name="username" value="<?php echo isset($usuario->username) ? $usuario->username : '' ?>">
                                        </td>
                                        <td>
                                            <input type="text" style="width: 170px;" name="name" value="<?php echo isset($usuario->name) ? $usuario->name : '' ?>">
                                        </td>
                                        <td>
                                            <input type="text" style="width: 170px;" name="surname" value="<?php echo isset($usuario->surname) ? $usuario->surname : '' ?>">
                                        </td>
                                        <td>
                                            <input type="password" style="width: 170px;" name="password" placeholder="senha"><br>
                                            <input type="password" style="width: 170px;" name="password_repeat" placeholder="repetir a senha">
                                        </td>
                                        <td>
                                            <input type="submit" value="Salvar" class="btn btn-primary">
                                        </td>
                                    </form>
                                </tr>
                            <?php
                        }
                    ?>
                </tbody>
            </table>
        <?php
    }

    protected function body_content() {
        $this->formLista();
    }
}