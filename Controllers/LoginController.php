<?php
require_once __DIR__ . '/../Models/Usuario.php';
class LoginController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function telaLogin() {
        // Se já tá logado, manda pro início
        if (isset($_SESSION['idUsuario'])) {
            header("Location: index.php?action=inicio");
            exit;
        }
        require_once __DIR__ . '/../View/login/loginInicio.php';
    }

    public function autenticar() {
    $nome = $_POST['nome'] ?? '';
    $senha = $_POST['senha'] ?? '';

    $usuarioModel = new Usuario($this->db);
    $usuario = $usuarioModel->buscarPorNome($nome);

    if ($usuario) {
        // se usuário existe checa a senha para ver se confere
        if (password_verify($senha, $usuario['senha'])) {
            $_SESSION['idUsuario'] = $usuario['idUsuario'];
            $_SESSION['nomeUsuario'] = $usuario['nome'];
            header("Location: index.php?action=inicio");
            exit;
        } else {
            $_SESSION['erro_login'] = "Senha incorreta para este usuário!";
            header("Location: index.php?action=login");
            exit;
        }
    } else {
            // se suário não existe cria um login automaticamente 
            $usuarioModel->cadastrar($nome, $senha);
            
            // busca o novo usário para pegar seu id e logar
            $novoUsuario = $usuarioModel->buscarPorNome($nome);
            $_SESSION['idUsuario'] = $novoUsuario['idUsuario'];
            $_SESSION['nomeUsuario'] = $novoUsuario['nome'];
            
            header("Location: index.php?action=inicio");
            exit;
        }
    }
    public function logout() { //acaba com a sessão e desloga o player
        session_destroy();
        header("Location: index.php?action=login");
        exit;
    }
}