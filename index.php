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
    $teamController->telaMontarTime();
    break;

    case 'salvar_time':
    $teamController->salvarTime();
    break;

    case 'batalha': // Exemplo de página protegida que exige time
            // $batalhaController->index();
            
            // Simulação da verificação direto no index ou via controller:
            $timeModel = new Time($db);
            if ($timeModel->contarTimesDoUsuario($_SESSION['idUsuario']) === 0) {
                throw new SemTimeException("Atenção: Monte pelo menos um time antes de ir para a batalha!");
            }
            require_once __DIR__ . '/View/batalha/arena.php';
            break;

    default:
    echo "Página não encontrada!";
    break;
    }
} catch (SemTimeException $e) {
    // Captura a nossa exceção customizada, guarda a mensagem na sessão e redireciona
    $_SESSION['erro_time'] = $e->getMessage();
    header("Location: index.php?action=montar_time");
    exit;
}