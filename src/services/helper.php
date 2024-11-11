<?php
require_once 'log.php';

class Helper {
    const ALERT_PRIMARY = 'primary';
    const ALERT_SECONDARY = 'secondary';
    const ALERT_SUCCESS = 'success';
    const ALERT_DANGER = 'danger';
    const ALERT_WARNING = 'warning';
    const ALERT_INFO = 'info';
    const ALERT_LIGHT = 'light';
    const ALERT_DARK = 'dark';
    /**
     * https://getbootstrap.com/docs/5.3/components/alerts/
     * @param 'primary'|'secondary'|'success'|'danger'|'warning'|'info'|'light'|'dark' $type
     */
    public static function send_alert(string $msg, string $type = Helper::ALERT_WARNING, int $time = 30000) {
        ?>
            <script>
                sendAlert(type = '<?php echo $type ?>', msg = '<?php echo $msg ?>', time = <?php echo $time ?>);
            </script>
        <?php
    }
    public static function array_find(array $arr, $func) {
        foreach ($arr as $i) {
            if ($func($i)) return $i;
        }
        return null;
    }
    public static function array_find_index(array $arr, $func): int|false {
        foreach ($arr as $index => $i) {
            if ($func($i)) return $index;
        }
        return false;
    }
    public static function gen_uuid() {
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
    
            // 16 bits for "time_mid"
            mt_rand( 0, 0xffff ),
    
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand( 0, 0x0fff ) | 0x4000,
    
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand( 0, 0x3fff ) | 0x8000,
    
            // 48 bits for "node"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
    }

    /**
     * return true if null or ""
     */
    public static function isNullOrEmpty(mixed $var): bool {
        return is_null($var) || $var === '';
    }

    /**
     * @param string $type string|int|float|bool
     */
    public static function castValue($value, string $type) {
        if ($value === null) return $value;
        if (is_string($value) && strcasecmp($value, 'null') === 0) return null;
        if ($type === 'bool' || $type === 'boolean') {
            if (is_string($value)) {
                if (empty($value)) return false;
                if (preg_match('/^(0|null|off|false)$/i', $value)) return false;
                return true;
                // if (preg_match('/^(1|on|true)$/i', $value)) return true;
            }
            return (bool) $value;
        }
        if ($type === 'int') return (int) $value;
        if ($type === 'float') return (float) $value;
        if ($type === 'datetime' || $type === 'DateTime') {
            if ($value instanceof DateTime) return $value;
            if ($value instanceof DateTimeImmutable) return $value;
            if (gettype($value) !== 'string') return null;
            if (strlen($value) < 10) return null;
            $time = strtotime($value);
            if ($time === false) return null;
            $date = new DateTime();
            $date->setTimestamp($time);
            return $date;
            // if (strlen($value) === 10) return DateTime::createFromFormat('Y-m-d|', $value) ?: null;
            // return DateTime::createFromFormat('Y-m-d H:i:s.u', $value) ?: null;
        }
        else return $value;
    }

    public static function strToFileSize(string $str): int {
        preg_match('/(\d+(?:\.\d+)?)\s*([KMG]?B)/i', $str, $matches);

        $number = (float)$matches[1];
        $unit = strtoupper($matches[2]);

        switch ($unit) {
            case 'KB':
                return (int) $number * 1024;
            case 'MB':
                return (int) $number * 1024 * 1024;
            case 'GB':
                return (int) $number * 1024 * 1024 * 1024;
            default:
                return (int) $number;
        }
    }

    public static function is_builtin_class($className) {
        if (!class_exists($className)) return false;

        if ($className === 'DateTime' ||
            $className === 'DateTimeImmutable' ||
            $className === 'DateTimeZone' ||
            $className === 'DateInterval' ||
            $className === 'DateTimeInterface' ||
            $className === 'PDO' ||
            $className === 'PDOStatement' ||
            $className === 'Exception' ||
            $className === 'SplFileObject' ||
            $className === 'SimpleXMLElement' ||
            $className === 'DOMDocument' ||
            $className === 'DOMXPath' ||
            $className === 'JsonSerializable' ||
            $className === 'ArrayObject' ||
            $className === 'DirectoryIterator' ||
            $className === 'ReflectionClass'
        ) {
            return false;
        }
        
        return true;
    }

    public static function compareDateTimes(?Datetime $date1, ?DateTime $date2): int {
        if ($date1 === null && $date2 === null) {
            return 0; // Ambas são null, então são consideradas iguais
        } elseif ($date1 === null) {
            return -1; // $date1 é null e $date2 não é, então $date1 é considerado menor
        } elseif ($date2 === null) {
            return 1; // $date2 é null e $date1 não é, então $date1 é considerado maior
        } elseif ($date1 < $date2) {
            return -1; // $date1 é anterior a $date2
        } elseif ($date1 > $date2) {
            return 1; // $date1 é posterior a $date2
        } else {
            return 0; // $date1 é igual a $date2
        }
    }

    /**
     * @param string[] $patterns
     */
    public static function validadeStr(string $value, array $patterns): bool {
        foreach ($patterns as $pattern) {
            if (strcasecmp($pattern, 'cpf') === 0) {
                if (preg_match('/^[0-9]{3}\.?[0-9]{3}\.?[0-9]{3}-?[0-9]{2}$/', $value)) return true;
            } else if (strcasecmp($pattern, 'cnpj') === 0) {
                if (preg_match('/^[0-9]{2}\.?[0-9]{3}\.?[0-9]{3}\/?[0-9]{4}-?[0-9]{2}$/', $value)) return true;
            } else if (strcasecmp($pattern, 'phone') === 0) {
                if (preg_match('/^(\+[0-9]{1,3} *)?\(?[0-9]{2}\)? *[0-9]{4,5}( *|-)[0-9]{4}$/', $value)) return true;
            }
        }

        return false;
    }

    public static function randomStr(string $start = '', int $length = 3) {
        $string = $start;

        for ($i = 0; $i < $length; $i++) {
            $string .= chr(mt_rand(65, 90)); // Intervalo de caracteres ASCII de A-Z
        }

        return $string;
    }

    public static function appRootPath(string $path = '') {
        $root = preg_replace('/(\/|\\\\)src(\/|\\\\)services$/', '', __DIR__);

        if (empty($path)) return $root;
        return preg_replace('/(\/|\\\)+/', '/', $root . '/' . $path);
    }

    public static function uriRoot(string $uri = '/'): string {
        preg_match('/^\/(recomecar\/)?/', $_SERVER["REQUEST_URI"], $match);

        return preg_replace('/(\/|\\\)+/', '/', $match[0] . '/' . $uri);
    }

    public static function getCurrentUri(): string {
        $uri = strtok($_SERVER["REQUEST_URI"], '?');
        $uri = preg_replace('/^\/((recomecar\/)(public)?)?/', '/', $uri);

        if ($uri === '/') return $uri;

        return rtrim($uri, '/');
    }

    public static function uriLogin(): string {
        return Helper::uriRoot('/login');
    }

    public static function apiPath(string $uri = '/'): string {
        return Helper::uriRoot("/api/v1/{$uri}");
    }

    public static function storagePath(string $path = ''): string {
        if (!defined('__STORAGE_PATH__')) define('__STORAGE_PATH__', Helper::appRootPath('storage'));
        $storage_path = __STORAGE_PATH__;

        if (strlen($path) === 0) return $storage_path;

        return preg_replace('/(\/|\\\)+/', '/', $storage_path . '/' . $path);
    }

    public static function pathExistsCreate(string $path) {
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }
    }

    /**
     * Parses GET and POST form input like $_GET and $_POST, but without requiring multiple select inputs to end the name
     * in a pair of brackets.
     * 
     * @param  string $method      The input method to use 'GET' or 'POST'.
     * @param  string $querystring A custom form input in the query string format.
     * @return array  $output      Returns an array containing the input keys and values.
     */
    public static function bracketless_input( $method, $querystring=null ) {
        // Create empty array to 
        $output = array();
        // Get query string from function call
        if( $querystring !== null ) {
            $query = $querystring;
        // Get raw POST data
        } elseif ($method == 'POST') {
            $query = file_get_contents('php://input');
        // Get raw GET data
        } elseif ($method == 'GET') {
            $query = $_SERVER['QUERY_STRING'];
        }
        // Separerate each parameter into key value pairs
        if (!empty($query)) {
            foreach( explode( '&', $query ) as $params ) {
                $parts = explode( '=', $params );
                // Remove any existing brackets and clean up key and value
                $parts[0] = trim(preg_replace( '(\%5B|\%5D|[\[\]])', '', $parts[0] ) );
                $parts[0] = preg_replace( '([^0-9a-zA-Z])', '_', urldecode($parts[0]) );
                $parts[1] = urldecode($parts[1]);
                // Create new key in $output array if param does not exist.
                if( !key_exists( $parts[0], $output ) ) {
                    $output[$parts[0]] = $parts[1];
                // Add param to array if param key already exists in $output
                } elseif( is_array( $output[$parts[0]] ) ) {
                    array_push( $output[$parts[0]], $parts[1] );
                    // Otherwise turn $output param into array and append current param
                } else {
                    $output[$parts[0]] = array( $output[$parts[0]], $parts[1] );
                }
            }
        }
        return $output;
    }
}