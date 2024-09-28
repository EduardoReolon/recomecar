<?php
require_once __DIR__ . '/../models/arquivo.php';
require_once __DIR__ . '/../services/helper.php';

$uri = Helper::getCurrentUri();
preg_match('/\/?storage\/(.*?)\/([^\/]+)$/', $uri, $matches);
$path = $matches[1];
$file = $matches[2];

$filePath = Helper::storagePath($path . '/' . $file);

$nome = $file;
if (key_exists('name', $_GET)) $nome = $_GET['name'];

function get_mime_type($filename) {
    $mime_types = [
        'pdf' => 'application/pdf',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
    ];

    $extensao = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if (array_key_exists($extensao, $mime_types)) {
        return $mime_types[$extensao];
    } else {
        return 'application/octet-stream'; // Fallback para tipo genérico
    }
}

// Verifica se o arquivo solicitado é válido
if (file_exists($filePath)) {
    // Define os cabeçalhos HTTP para informar o navegador sobre o tipo de conteúdo
    header('Content-Type: ' . get_mime_type($filePath));
    header('Content-Disposition: inline; filename="' . $nome . '"');

    // Lê o arquivo e envia seu conteúdo para o navegador
    readfile($filePath);
} else {
    // Se o arquivo não existir, exibe uma mensagem de erro
    header("HTTP/1.0 404 Not Found");
}