<?php

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);


$baseFolder = '/sistemachamados/public';
$rota = str_replace($baseFolder, '', $requestUri);

$rotas = [
    '/' => 'painel.php',
    '/painel' => 'painel.php',
    '/clientes' => 'clientes.php',
    '/chamado' => 'chamado.php',
    '/novo-chamado' => 'novo_chamado.php',
    '/conhecimento' => 'conhecimento.php',
    '/artigo' => 'artigo.php',
    '/relatorios' => 'relatorios.php',
    '/configuracoes' => 'configuracoes.php',
    '/login' => 'login.php',
    '/logout' => 'logout.php',
    '/cliente' => 'cliente.php',
    '/webhook' => 'webhook.php'
];

if (array_key_exists($rota, $rotas)) {
    $arquivoDestino = __DIR__ . '/../../public/' . $rotas[$rota];
    if (file_exists($arquivoDestino)) {
        require_once $arquivoDestino;
        exit;
    }
}
