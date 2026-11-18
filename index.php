<?php
session_start();
require_once 'config.php';
require_once 'autoload.php';

$action = $_GET['action'] ?? 'inicio';

$authController = new LoginController($db);
$lojaController = new LojinhaController($db);
$teamController = new TeamController($db);


if ($action === 'login' || $action === 'autenticar') {
    switch ($action) {
        case 'login':
            $authController->telaLogin();
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
try {
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

        case 'montar_time':
        $teamController->index(); 
        break;

        case 'novo_time':
        $teamController->telaMontarTime();
        break;

        case 'salvar_time':
        $teamController->salvarTime();
        break;

        case 'meus_times':
        $teamController->verTimes(); // Tela de ver times e usar itens
        break;

        case 'usar_item':
        $teamController->usarItem(); // Ação POST para aplicar o item
        break;

        case 'batalha':
        $batalhaController = new BatalhaController($db);
        $batalhaController->index(); // Abre a seleção de times
        break;

        case 'arena':
        $batalhaController = new BatalhaController($db);
        $batalhaController->arena(); // Abre a arena com o time escolhido
        break;

        case 'processar_turno':
        $batalhaController = new BatalhaController($db);
        $batalhaController->processarTurno();
        break;

        case 'usar_item_batalha':
        $batalhaController = new BatalhaController($db);
        $batalhaController->usarItemBatalha();
        break;

        default:
        echo "Página não encontrada!";
        break;
    }
} catch (SemTimeException $e) {
    // Captura a nossa exceção customizada, guarda a mensagem na sessão e redireciona
    $_SESSION['erro_time'] = $e->getMessage();
    header("Location: index.php?action=novo_time");
    exit;
}