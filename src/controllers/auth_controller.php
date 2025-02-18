<?php
require_once __DIR__ . '/../services/auth.php';

class Auth_controller extends Base_controller {
    static protected array $map = ['api', 'v1', 'auth'];
    /**
     * @request
     * map /
     * method post
     * no_auth
     */
    static public function login(Login_request $request, Http_response $response) {
        if (!isset($request->username) || !isset($request->password)) {
            return $response->status(400)->sendAlert('Nome de usuário e senha são obrigatórios!');
        }
        
        try {
            Log::new()->setMessage('1');
            $usuario = User::findBy('username', $request->username);
            Log::new()->setMessage('2');

            if (!isset($usuario)) {
                return $response->status(400)->sendAlert('User not found!');
            }
            Log::new()->setMessage('3');
            if (!$usuario->passwordCheck($request->password)) {
                return $response->status(400)->sendAlert('Wrong password!');
            }
    
            Log::new()->setMessage('4');
            Auth::refresh_token($usuario);
            Log::new()->setMessage('5');
    
            $response->redirectUser(Helper::uriRoot());
            exit();
        } catch (\Throwable $th) {
            //throw $th;
            throw new Exception("Invalid credentials", 1);
        }
    }

    /**
     * @request
     * map /:id_usuario
     * method post
     * no_auth
     */
    static public function register(register_request $request, Http_response $response) {
        $id_usuario = (int) RouteParams::get('id_usuario');

        $user = null;
        if ($id_usuario === 0) {
            if (!isset($request->password) || !isset($request->password_repeat)) return $response->status(400)->sendAlert('É necessário informar a senha para criar um usuário');
            $user = new User();
        } else {
            $user = User::findBy('id', $id_usuario);
            if ($user === null) return $response->status(404)->sendAlert('Usuário não encontrado');
        }
        
        if (isset($request->password)) {
            if (!isset($request->password_repeat) || $request->password !== $request->password_repeat) return $response->status(400)->sendAlert('As senhas não coincidem');
        }

        if ($id_usuario === 0 || $user->username !== $request->username) {
            $outro = User::findBy('username', $request->username);
            if ($outro !== null) {
                if ($id_usuario === 0 || $outro->id !== $id_usuario) return $response->status(400)->sendAlert('Há outro usuário com esse e-mail');
            }
        }

        $user->username = $request->username;
        $user->name = $request->name;
        $user->surname = $request->surname;
        if (isset($request->password)) $user->setPassword($request->password);

        $user->save();
    }
}