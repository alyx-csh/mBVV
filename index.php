<?php
session_start();
require_once 'config.php';
require_once 'autoload.php';

$action = $_GET['action'] ?? 'inicio';

$batalhaController = new BatalhaController($db);
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
        require_once __DIR__ . '/View/inicio.php'; //mostra tela com o menu pra ir pros outros lugares
        break;

        case 'loja':
        $lojaController->index(); //mostra a tela da loja
        break;

        case 'comprar':
        $lojaController->comprar(); //usado na loja para comprar itens
        break;

        case 'logout':
        $authController->logout(); // destrói a sessão e manda para o login
        break;

        case 'montar_time':
        $teamController->index(); //mostra a tela de montar times
        break;

        case 'novo_time':
        $teamController->telaMontarTime(); //mostra a tela de escolher entre montar um time e mexer no seu time
        break;

        case 'salvar_time':
        $teamController->salvarTime(); //função que salva o time usado em montar time
        break;

        case 'meus_times':
        $teamController->verTimes(); // Tela de ver times e usar itens
        break;

        case 'usar_item':
        $teamController->usarItem(); // Ação POST para aplicar o item
        break;

        case 'batalha':
        $batalhaController->index(); // abrir a tela para escolher entre times
        break;

        case 'arena':
        $batalhaController->arena(); // abrir a tela de batalha
        break;

        case 'processar_turno':
        $batalhaController->processarTurno(); // processa o turno, faz os calculos
        break;

        case 'criar_log':
        $resultado = $_POST['resultado'] ?? 'Desconhecido';
        $this->CriarLog($resultado);
        echo json_encode(['sucesso' => true]);
        exit;

        default:
        echo "Página não encontrada!";
        break;
    }
} catch (SemTimeException $e) { //exceção personalizada para quando o usuário não tem time
    $_SESSION['erro_time'] = $e->getMessage();
    header("Location: index.php?action=novo_time");
    exit;
}