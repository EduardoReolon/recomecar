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
            $usuario = User::findBy('username', $request->username);

            if (!isset($usuario)) {
                return $response->status(400)->sendAlert('User not found!');
            }
            if (!$usuario->passwordCheck($request->password)) {
                return $response->status(400)->sendAlert('Wrong password!');
            }
    
            Auth::refresh_token($usuario);
    
            $response->redirectUser(Helper::uriRoot());
            exit();
        } catch (\Throwable $th) {
            //throw $th;
            throw new Exception("Invalid credentials", 1);
        }
    }
}