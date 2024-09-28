<?php

class Parameter implements JsonSerializable {
    private string $name;
    private ReflectionNamedType $type;
    private string $class_name;

    public function __construct(ReflectionParameter $parameter) {
        $this->name = $parameter->getName();
        $this->type = $parameter->getType();
        if ($this->type !== null && !$this->type->isBuiltin()) {
            $this->class_name = $this->type->getName();
        }
    }

    public function get(Http_response $http_response) {
        if (isset($this->class_name)) {
            if ($this->class_name === 'Http_response') return $http_response;
            $class_name = $this->class_name;
            return new $class_name($http_response);
        } else return null;
    }

    public function jsonSerialize(): mixed {
        return [
            'name' => $this->name,
            'class_name' => $this->class_name,
        ];
    }
}

class Route_map implements JsonSerializable {
    /** @var string[] */
    private array $map = [];
    public bool $auth_required = true;
    /** @var 'post'|'patch'|'delete'[] lowercased */
    private array $http_methods = [];
    private string $name;
    public bool $is_route = false;
    private ReflectionMethod $class_method; // $class_method->invokeArgs($args);
    /** @var Parameter[] */
    private array $parameters = [];

    public function __construct(ReflectionMethod $method, array $map) {
        $this->map = $map;
        $this->name = $method->getName();
        $this->class_method = $method;

        $coments = $method->getDocComment();
        preg_match('/@request.*/s', $coments, $matches);
        
        if (empty($matches[0])) return;
        else {
            $this->is_route = true;
            $parameters = $method->getParameters();
            foreach ($parameters as $parameter) {
                $this->parameters[] = new Parameter($parameter);
            }

            $linhas = explode("\n", trim($matches[0]));
            array_shift($linhas); // a primeira linha é @column

            foreach ($linhas as $linha) {
                $partes = preg_split('/(?<=\w)(( *\| *)| +|=)/', $linha);
                $chave = trim($partes[0]);
                $valor = isset($partes[1]) ? trim($partes[1]) : null;

                if (preg_match('/(map|path)/i', $chave)) {
                    if (!empty($valor)) $this->map = array_merge($this->map, explode("/", preg_replace('/^\/(.*)/', '$1', $valor)));
                } else if (preg_match('/(http)?.*method/i', $chave)) {
                    if (!empty($valor)) $this->http_methods[] = strtolower($valor);
                } else if (preg_match('/no_auth/i', $chave)) {
                    $this->auth_required = false;
                }
            }
        }
    }

    public function invoke(Http_response $response) {
        $args = [];

        foreach ($this->parameters as $parameter) {
            $args[] = $parameter->get($response);
        }

        if ($response->getStatus() > 205) return;

        try {
            $this->class_method->invokeArgs(null, $args);
            if ($response->getStatus() > 205) User::rollBackTransaction();
            else $response->sendAlert('Salvo com sucesso!', Helper::ALERT_SUCCESS);
        } catch (\Throwable $th) {
            $response->status(500)->sendAlert('Erro interno!');
            User::rollBackTransaction();
            throw $th;
        }
    }

    /** @param string[] $route */
    public function match(array $route, string $method) {
        $method = strtolower($method);

        if (!in_array($method, $this->http_methods)) return false;

        $route_params = [];
        foreach ($route as $i => $piece) {
            if (count($this->map) - 1 < $i) return false;
            $in_map = $this->map[$i];
            if (substr($in_map, 0, 1) === ':') {
                $route_params[substr($in_map, 1)] = $piece;
                continue;
            }
            if ($piece !== $in_map) return false;
        }

        foreach ($route_params as $key => $value) {
            RouteParams::set($key, $value);
        }

        return true;
    }

    // for maintenance
    public function jsonSerialize(): mixed {
        return [
            'map' => $this->map,
            'name' => $this->name,
            'parameters' => $this->parameters,
        ];
    }
}

class Base_controller {
    /** @var string[] */
    static protected array $map = [];

    /** @return Route_map[] */
    static function read_methods() {
        /** @var Route_map[] */
        $route_mapping = [];

        // Obtém uma instância da classe Reflection para a classe chamada
        $reflection = new ReflectionClass(get_called_class());

        // Obtém todos os métodos da classe
        $methods = $reflection->getMethods();

        // Itera sobre os métodos e armazena seus nomes no array
        foreach ($methods as $method) {
            $route = new Route_map($method, static::$map);
            if ($route->is_route) $route_mapping[] = $route;
        }

        return $route_mapping;
    }
}