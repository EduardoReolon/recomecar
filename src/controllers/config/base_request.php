<?php

class Property {
    public string $prop_name;
    public string $prop_name_income;
    public bool $json = false;
    public bool $allowsNull = true;
    public string $type = 'string';
    public string $type_chield = 'string'; // whenever type is array
    public string $pattern; // cpf|cnpj|phone|email
    public int $floor;
    public int $ceiling;
    /** @var string[] */
    public array $mimes = ['pdf', 'jpg', 'png', 'bmp', 'webp', 'tif', 'svg', 'ico'];
    public int $file_max_size;
    public int $file_min_size;

    public function __construct(ReflectionProperty $property) {
        $comments = $property->getDocComment();
        
        $this->prop_name = $property->getName();
        $this->prop_name_income = $this->prop_name;
        if ($property->getType()) {
            $this->type = $property->getType()->getName();
            $this->allowsNull = $property->getType()->allowsNull();
        }

        if (gettype($comments) !== 'string') return;

        $linhas = explode("\n", trim($comments));

        foreach ($linhas as $linha) {
            $partes = preg_split('/(?<=\w)(( *\| *)| +|=)/', $linha);
            $chave = trim($partes[0]);
            $valor = isset($partes[1]) ? trim($partes[1]) : null;

            if (preg_match('/prop.*(name)?.*in(come)?/i', $chave)) {
                if (!empty($valor)) $this->prop_name_income = $valor;
            } elseif (preg_match('/json/i', $chave)) {
                $this->json = true;
            } elseif (preg_match('/@var/i', $chave)) {
                if (!empty($valor)) {
                    if ($this->type === 'array') {
                        $this->type_chield = preg_replace('/\[\].*$/', '', $valor);
                    } else $this->type = $valor;
                }
            } elseif (preg_match('/pattern/i', $chave)) {
                $this->pattern = preg_replace('/\[\].*$/', '', $valor);
            } elseif (preg_match('/floor/i', $chave)) {
                $this->floor = (int) $valor;
            } elseif (preg_match('/ceiling/i', $chave)) {
                $this->ceiling = (int) $valor;
            } elseif (preg_match('/mimes/i', $chave)) {
                $this->mimes = preg_split('/(\s*\|\s*)|(\s+)|(\s*,\s*)/', $valor);
            } elseif (preg_match('/file_max_size/i', $chave)) {
                $this->file_max_size = Helper::strToFileSize($valor);
            } elseif (preg_match('/file_min_size/i', $chave)) {
                $this->file_min_size = Helper::strToFileSize($valor);
            }
        }
    }
}

class Base_request {
    private array $source = [];

    private function validate(Property $property, mixed $value, Http_response $response) {
        if ($property->type === 'string') {
            if ((isset($property->floor) && strlen($value) < $property->floor) || (isset($property->ceiling) && strlen($value) > $property->ceiling)) {
                $response->status(400)->sendAlert("{$property->prop_name_income} não atendeu ao padrão");
            } else if (isset($property->pattern)) {
                // it has to attend at least one pattern
                $attended = Helper::validadeStr($value, preg_split('/\s*\|\s*/', $property->pattern));
                if (!$attended) $response->status(400)->sendAlert("{$property->prop_name_income} não atendeu ao padrão");
            }
        } else if ($property->type === 'int' || $property->type === 'float' || $property->type === 'double') {
            if ((isset($property->floor) && $value < $property->floor) || (isset($property->ceiling) && $value > $property->ceiling)) {
                $response->status(400)->sendAlert("{$property->prop_name_income} não atendeu ao padrão");
            }
        }
    }

    /**
     * @return File_request[]|File_request
     */
    private function file_handler(Http_response $response, Property $property) {
        /** @var File_request[] */
        $files_request = [];

        if (!key_exists($property->prop_name_income, $_FILES) || !is_array($_FILES[$property->prop_name_income])) {
            if (!$property->allowsNull) {
                $response->status(400)->sendAlert('Erro no envio do(s) arquivo(s)!');
            }
            if ($property->type === 'array') return $files_request;
            return new File_request();
        }

        if (is_array($_FILES[$property->prop_name_income]['name'])) {
            foreach ($_FILES[$property->prop_name_income]['name'] as $i => $_file) {
                if ($_FILES[$property->prop_name_income]['error'][$i] > 0) continue;
                $files_request[] = new File_request(
                    $_FILES[$property->prop_name_income]['name'][$i],
                    $_FILES[$property->prop_name_income]['type'][$i],
                    $_FILES[$property->prop_name_income]['full_path'][$i],
                    $_FILES[$property->prop_name_income]['tmp_name'][$i],
                    $_FILES[$property->prop_name_income]['error'][$i],
                    $_FILES[$property->prop_name_income]['size'][$i],
                );
            }
        } else if ($_FILES[$property->prop_name_income]['error'] === 0) {
            $files_request[] = new File_request(
                $_FILES[$property->prop_name_income]['name'],
                $_FILES[$property->prop_name_income]['type'],
                $_FILES[$property->prop_name_income]['full_path'],
                $_FILES[$property->prop_name_income]['tmp_name'],
                $_FILES[$property->prop_name_income]['error'],
                $_FILES[$property->prop_name_income]['size'],
            );
        }

        if (count($files_request) === 0) {
            if (!$property->allowsNull) {
                $response->status(400)->sendAlert('Necessário enviar o(s) arquivo(s)');
            }
            if ($property->type === 'array') return $files_request;
            return new File_request();
        }

        foreach ($files_request as $file_request) {
            $extensao = pathinfo($file_request->name, PATHINFO_EXTENSION);
            if (!in_array($extensao, $property->mimes) && !in_array($file_request->type, $property->mimes)) {
                $response->status(400)->sendAlert("Extensão de arquivo não aceita ({$file_request->name})");
            }

            if (isset($property->file_max_size) && $file_request->size > $property->file_max_size) {
                $response->status(400)->sendAlert("Arquivo de tamanho superior ao permitido ({$file_request->name})");
            } else if (isset($property->file_min_size) && $file_request->size < $property->file_min_size) {
                $response->status(400)->sendAlert("Arquivo de tamanho inferior ao permitido ({$file_request->name})");
            }

            if ($property->type !== 'array') return $file_request;
        }

        return $files_request;
    }

    public function __construct(Http_response $response, mixed $source = null) {
        $properties = static::getProperties();

        if ($source === null) $this->source = $this->getSource();
        else $this->source = $source;

        foreach ($properties as $property) {
            $prop_name = $property->prop_name;

            if ($property->type === 'File_request' || $property->type_chield === 'File_request') {
                $this->$prop_name = $this->file_handler($response, $property);
                continue;
            }

            if (!key_exists($property->prop_name_income, $this->source) || Helper::isNullOrEmpty($this->source[$property->prop_name_income])) {
                if ($property->allowsNull) {
                    $this->$prop_name = null;
                    continue;
                }
                if ($property->type === 'bool' || $property->type === 'boolean') {
                    $this->$prop_name = false;
                    continue;
                } else {
                    $response->status(400)->sendAlert("{$property->prop_name_income} deve ser informado");
                    continue;
                }
            }

            if ($property->json) {
                if (!empty($this->source[$property->prop_name_income])) {
                    $this->$prop_name = json_decode($this->source[$property->prop_name_income], true);
                }
            } else {
                if ($property->type === 'array') {
                    if (!isset($this->$prop_name)) $this->$prop_name = [];
                    if (Helper::is_builtin_class($property->type_chield)) {
                        $type_chield = $property->type_chield;
                        foreach ($this->source[$property->prop_name_income] as $data) {
                            $this->$prop_name[] = new $type_chield($response, $data);
                        }
                    } else {
                        foreach ($this->source[$property->prop_name_income] as $data) {
                            $this->$prop_name[] = Helper::castValue($data, $property->type_chield);
                            $this->validate($property, $this->$prop_name[array_key_last($this->$prop_name)], $response);
                        }
                    }
                } else if (Helper::is_builtin_class($property->type)) {
                    $type = $property->type;
                    $this->$prop_name = new $type($response, $this->source[$property->prop_name_income]);
                } else {
                    $this->$prop_name = Helper::castValue($this->source[$property->prop_name_income], $property->type);
                    $this->validate($property, $this->$prop_name, $response);
                }
            }
        }
    }

    /**
     * $_POST
     * $_GET
     * $rawData = file_get_contents("php://input");
     * parse_str($rawData, $patchParams);
     * $patchParams
     */
    private function getSource(): array {
        return $_POST;
    }

    /**
     * @return Property[]
     */
    private static function getProperties(): array {
        $reflexao = new ReflectionClass(static::class);
        $properties = $reflexao->getProperties();

        /** @var Property[] */
        $props = [];
        foreach ($properties as $property) {
            $prop = new Property($property);
            $props[] = $prop;
        }

        return $props;
    }
}