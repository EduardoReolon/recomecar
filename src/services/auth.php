<?php
require_once __DIR__ . '/../models/user.php';
require_once __DIR__ . '/../models/refresh_token.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth {
    private static $roles = [];
    private static $setor;
    private static $user_id;
    /** @var User $user */
    private static $user;
    private static string $uniqueLogonId;

    public static function hasRole($role, $throwError = false) {
        $found = in_array($role, self::$roles);

        if (!$found && $throwError) {
            throw new Error("Error with user role", 1);
        }

        return $found;
    }

    public static function uniqueLogonId() {
        if (!isset(self::$uniqueLogonId)) self::$uniqueLogonId = Helper::randomStr(length: 12);
        return self::$uniqueLogonId;
    }

    public static function getFirstRoleNoAdmin(): string {
        if (isset(self::$setor)) return self::$setor;

        $role = self::$roles[0];
        $index = 1;
        while ($role === 'admin' && count(self::$roles) > $index) {
            $role = self::$roles[$index];
            $index ++;
        }

        return $role;
    }

    public static function getUserId(): int {
        if (isset(self::$user_id)) return self::$user_id;
        if (isset(self::$user)) return self::$user->id;
        return 0;
    }

    /** @return User */
    public static function getUser() {
        if (isset(self::$user)) return self::$user;

        self::$user = User::findBy('id', self::$user_id);

        if (!isset(self::$user)) throw new Exception('Usuário não encontrado',1);

        return self::$user;
    }
    
    private static function token_get_data(string $token) {
        try {
            return JWT::decode($token, new Key(__JWT_SECRET__, 'HS256'));
        } catch (\Throwable $th) {
            return false;
        }
    }

    public static function verificarAutenticacao() {
        // Verifica se o usuário está autenticado usando o token de acesso
        try {
            if (isset($_SESSION["access_token"])) {

                $token_data = self::token_get_data($_SESSION["access_token"]);

                if ($token_data !== false) {
                    self::$roles = $token_data->roles;
                    self::$user_id = $token_data->user_id;
                    return;
                }
            }

            if (!key_exists('refresh_token', $_COOKIE)) {
                header("Location: " . Helper::uriLogin());
                return;
            }
    
            try {
                self::refresh_token(null, $_COOKIE['refresh_token']);
            } catch (\Throwable $th) {
                header("Location: " . Helper::uriLogin());
                return;
            }
        } catch (\Throwable $th) {
            // O refresh token não é válido, redireciona para a página de login
            header("Location: " . Helper::uriLogin());
            exit();
        }
    }
    
    private static function token_get_payload(User $user) {
        return array(
            "user_id"=> $user->id,
            "username" => $user->username,
            "roles" => $user->roles ?: [],
        );
    }
    
    private static function token(User $user) {
        self::$user = $user;
        $payload = self::token_get_payload($user);
    
        $payload['exp'] = time() + (15 * 60); // 15 minutos em segundos
    
        return JWT::encode($payload, __JWT_SECRET__, 'HS256');
    }
    
    public static function refresh_token(User $user = null, string $old_refresh_token = null) {
        if (empty($user) && empty($old_refresh_token)) {
            throw new Exception("user or old_refresh_token is needed", 1);
        }

        $expire_in = time() + (7 * 24 * 60 * 60);
    
        if (!empty($old_refresh_token)) {
            // deleta o old_refresh_token do banco
            // se não encontrado no banco deve retornar um erro
            // quando há um old_refresh_token, buscar no banco os dados do $user
    
            $refreshTokens = Refresh_token::fetchSimpler([['token', '=', $old_refresh_token]]);

            if (empty($refreshTokens)) {
                throw new Exception("Refresh token não encontrado", 1);
            }
    
            $refreshTokenOld = $refreshTokens[0];
            $user = $refreshTokenOld->getUser();
    
            if ($user === false) throw new Exception("Usuario não encontrado", 1);
    
            $refreshTokenOld->delete();
        }
    
        $refreshToken = Helper::gen_uuid();
    
        setcookie('refresh_token', $refreshToken, $expire_in, '/', '', true, true);
    
        // escreve o novo token no banco, atribuido ao usuário informado
        $refreshTokenNew = new refresh_token();
        $refreshTokenNew->token = $refreshToken;
        $refreshTokenNew->user_id = $user->id;
    
        $refreshTokenNew->save();
        // Refresh_token::commitTransaction();
    
        $token = self::token($user);
        $_SESSION["access_token"] = $token;

        self::verificarAutenticacao();
    }
}

