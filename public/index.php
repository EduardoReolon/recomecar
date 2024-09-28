<?php
require_once __DIR__ . '/../src/views/config/view_main.php';
require_once __DIR__ . '/../src/views/home_view.php';
require_once __DIR__ . '/../src/views/login_view.php';

$uri = Helper::getCurrentUri();
Log::new(Log::TYPE_CONTROL)->setMessage($_SERVER["REQUEST_METHOD"] . '-' . $uri);
if (Helper::uriRoot($uri) !== Helper::uriLogin()) {
    Auth::verificarAutenticacao();
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
}

new View_main();
return;

?>