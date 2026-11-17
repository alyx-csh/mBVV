<?php
session_start();
require_once 'config.php';
require_once 'autoload.php';

$action = $_GET['action'] ?? 'inicio';

$authController = new LoginController($db);
$lojaController = new LojinhaController($db);


if ($action === 'login' || $action === 'autenticar') {
    switch ($action) {
        case 'login':
            $authController->telaLogin(); // <--- ISTO FALTAVA! Chama a view de login
            break;
            
        case 'autenticar':
            $authController->autenticar(); 
            break;
    }
    exit;
}

// bloqueia acessos diretos via URL sem sessão
if (!isset($_SESSION['idUsuario'])) {
    header("Location: index.php?action=login");
    exit;
}

//rotas que só podem ser acessadas com sessão ativa
switch ($action) {
    case 'inicio':
        require_once __DIR__ . '/View/inicio.php';
        break;

    case 'loja':
        $lojaController->index();
        break;

    case 'comprar':
        $lojaController->comprar();
        break;

    case 'logout':
        $authController->logout(); // Destrói a sessão e manda para o login
        break;

    default:
        echo "Página não encontrada!";
        break;
}