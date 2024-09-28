<?php
require_once 'config/view_core.php';

class Login_view extends View_core {
    protected function body() {
        ?>
        <body>
            <div id="conteiner_global_alerts"></div>
            <div class="container">
                <div class="row">
                    <div class="col-sm"></div>
                    <div class="col-sm">
                        <h2>Login</h2>
                        <?php if (isset($error)) : ?>
                            <p style="color: red;"><?php echo $error; ?></p>
                        <?php endif; ?>
                        <form method="post" action="<?php echo Helper::apiPath("auth") ?>">
                            <label for="username">Usuário:</label>
                            <input type="text" name="username" required><br>
                            <label for="password">Senha:</label>
                            <input type="password" name="password" required><br>
                            <input type="submit" value="Login">
                        </form>
                    </div>
                    <div class="col-sm"></div>
                </div>
            </div>
        </body>
        <?php
    }
}