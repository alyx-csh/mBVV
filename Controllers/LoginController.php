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

    private function salvarImagemUpload() {
        if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        $arquivoTmp = $_FILES['foto']['tmp_name'];
        $nomeOriginal = $_FILES['foto']['name'];
        $extensao = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));
        $permitidas = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($extensao, $permitidas)) {
            return null;
        }

        $pastaDestino = __DIR__ . '/../uploads/perfil/';
        if (!is_dir($pastaDestino)) {
            mkdir($pastaDestino, 0777, true);
        }

        $novoNome = uniqid('perfil_', true) . '.' . $extensao;
        $caminhoCompleto = $pastaDestino . $novoNome;

        if (move_uploaded_file($arquivoTmp, $caminhoCompleto)) {
            return 'uploads/perfil/' . $novoNome;
        }

        return null;
    }

    public function autenticar() {
        $nome = $_POST['nome'] ?? '';
        $senha = $_POST['senha'] ?? '';
        $caminhoImagem = $this->salvarImagemUpload();

        $usuarioModel = new Usuario($this->db);
        $usuario = $usuarioModel->buscarPorNome($nome);

        if ($usuario) {
            // se usuário existe checa a senha para ver se confere
            if (password_verify($senha, $usuario['senha'])) {
                if ($caminhoImagem !== null) {
                    $usuarioModel->atualizarImagem($usuario['idUsuario'], $caminhoImagem);
                }
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
            // se usuário não existe cria um login automaticamente 
            $usuarioModel->cadastrar($nome, $senha, $caminhoImagem);
            
            // busca o novo usuário para pegar seu id e logar
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