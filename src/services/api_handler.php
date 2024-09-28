<?php
require_once 'helper.php';
require_once 'route_params.php';
require_once __DIR__ . '/../controllers/config/base_controller.php';
require_once __DIR__ . '/../controllers/config/http_response.php';

class Api_handler {
    /** @var string[] */
    private array $routeRequested = [];
    private string $methodRequested;

    /** @var Route_map[] */
    private array $routeMapping = [];

    public function __construct() {
        $requestUri = $_SERVER['REQUEST_URI'];
        $base = preg_replace('/\//', '\/', Helper::uriRoot());
        $this->routeRequested = explode("/", preg_replace('/^' . $base . '(.*)/', '$1', $requestUri));
        $this->methodRequested = strtolower($_SERVER["REQUEST_METHOD"]);
        
        $this->mapRoutes();
    }
    
    private function mapRoutes() {
        // Função para ler os arquivos de uma pasta
        function readFolder($folder, &$files = []) {
            $items = scandir($folder);
            
            foreach ($items as $item) {
                // Ignora os itens especiais '.' e '..'
                if ($item == '.' || $item == '..') {
                    continue;
                }
                
                // Caminho completo para o arquivo
                $path = $folder . DIRECTORY_SEPARATOR . $item;
                
                // Verifica se é um arquivo PHP
                if (is_dir($path)) readFolder($path, $files);
                else if (is_file($path) && pathinfo($path, PATHINFO_EXTENSION) == 'php') {
                    $files[] = $path;
                }
            }
        }
        
        // Obtém a lista de arquivos PHP na pasta
        readFolder(__DIR__ . '/../controllers', $files);
        
        // Loop pelos arquivos
        foreach ($files as $file) {
            // Inclui o arquivo
            require_once $file;
        }

        // Loop pelas classes
        foreach (get_declared_classes() as $classe) {
            // Verifica se a classe é uma subclasse de Base_controller
            if (is_subclass_of($classe, 'Base_controller')) {
                // Executa o método 'metodoPadrao' se existir na classe
                if (method_exists($classe, 'read_methods')) {
                    $this->routeMapping = array_merge($this->routeMapping, $classe::read_methods());
                }
            }
        }
    }

    public function handler() {
        $response = new Http_response();
        foreach ($this->routeMapping as $map) {
            if ($map->match($this->routeRequested, $this->methodRequested)) {
                if ($map->auth_required) Auth::verificarAutenticacao();
                $map->invoke($response);
                return;
            }
        }
        $response->status(404)->sendAlert('Rota não encontrada');
        Log::new(Log::TYPE_CONTROL)->setMessage('Api not found: ' . $this->methodRequested . '-' . json_encode($this->routeRequested, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}