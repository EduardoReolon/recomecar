<?php
require_once __DIR__ . '/../src/views/config/view_main.php';
require_once __DIR__ . '/../src/views/home_view.php';
require_once __DIR__ . '/../src/views/egresso_view.php';
require_once __DIR__ . '/../src/views/egressos_view.php';
require_once __DIR__ . '/../src/views/usuarios_view.php';
require_once __DIR__ . '/../src/views/login_view.php';

$uri = Helper::getCurrentUri();
Log::new(Log::TYPE_CONTROL)->setMessage($_SERVER["REQUEST_METHOD"] . '-' . $uri);
if (Helper::uriRoot($uri) !== Helper::uriLogin()) {
    Auth::verificarAutenticacao();
}

function isCurrent(string $option): bool {
    global $uri;
    if (preg_match($option, $uri)) return true;
    return false;
}

if (preg_match('/^\/storage/', $uri)) {
    require_once __DIR__ . '/../src/services/storage.php';
    return;
}

if (Helper::uriRoot($uri) === Helper::uriLogin()) {
    new Login_view();
    return;
} else if ($uri === '/') {
    new Home_view();
    return;
} else if ($uri === '/usuarios') {
    new Usuarios_view();
    return;
} else if ($uri === '/egressos') {
    new Egressos_view();
    return;
} else if (isCurrent('/^\/egresso\/[0-9]+$/')) {
    new Egresso_view();
    return;
}

new View_main();
return;

?>