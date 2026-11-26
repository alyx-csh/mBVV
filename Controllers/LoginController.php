<?php
require_once __DIR__ . '/../Models/Usuario.php';
class LoginController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function telaLogin() {
        if (isset($_SESSION['idUsuario'])) { // se já tá logado, manda pro início
            header("Location: index.php?action=inicio");
            exit;
        }
        require_once __DIR__ . '/../View/login/loginInicio.php';
    }
    public function telaMudarPerfil() {
        require_once __DIR__ . '/../View/login/mudarPerfil.php'; 
    }

    public function salvarPerfil() { //função pra atualizar a foto de perfil
        if (!isset($_SESSION['idUsuario'])) {
            header("Location: index.php?action=login");
            exit;
        }

        $caminhoImagem = $this->salvarImagemUpload();

        if ($caminhoImagem !== null) {
            $usuarioModel = new Usuario($this->db);
            $usuarioModel->atualizarImagem($_SESSION['idUsuario'], $caminhoImagem);
            
            // volta para o início já mostrando a foto nova
            header("Location: index.php?action=inicio");
            exit;
        } else {
            $_SESSION['erro_login'] = "Selecione uma imagem válida (JPG, JPEG, PNG ou WEBP).";
            header("Location: index.php?action=mudar_perfil");
            exit;
        }
    }

    private function salvarImagemUpload() { //função pra salvar a imagem numa pasta 
        if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        $arquivoTmp = $_FILES['foto']['tmp_name'];
        $nomeOriginal = $_FILES['foto']['name'];
        $extensao = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));
        $permitidas = ['jpg', 'jpeg', 'png', 'webp'];
        //pega a imagem, põe numa pasta temporária, 
        //pega a extensão da imagem e checa pra ver se é uma das permitidas

        if (!in_array($extensao, $permitidas)) { //checagem se é uma extensão que pode
            return null;
        }

        $pastaDestino = __DIR__ . '/../uploads/perfil/';
        if (!is_dir($pastaDestino)) {
            mkdir($pastaDestino, 0777, true);
        } //vê se tem uma pasta pras fotos de perfil, se não tiver cria ela

        $novoNome = uniqid('perfil_', true) . '.' . $extensao;
        $caminhoCompleto = $pastaDestino . $novoNome; //mudar o nome da imagem pra um id único e salvar o caminho dela completo (pasta+arquivo)

        if (move_uploaded_file($arquivoTmp, $caminhoCompleto)) {
            return 'uploads/perfil/' . $novoNome;
        } //retira o arquivo do lugar temporário e bota ele no diretório real

        return null;
    }

    public function autenticar() { //função usada pra fazer o login no jogo
        $nome = $_POST['nome'] ?? '';
        $senha = $_POST['senha'] ?? '';
        $caminhoImagem = $this->salvarImagemUpload();

        $usuarioModel = new Usuario($this->db);
        $usuario = $usuarioModel->buscarPorNome($nome);
        //busca pelo nome pra ver se já é usado

        if ($usuario) {
            // se usuário existe checa a senha para ver se confere
            if (password_verify($senha, $usuario['senha'])) {
                if ($caminhoImagem !== null) {
                    $usuarioModel->atualizarImagem($usuario['idUsuario'], $caminhoImagem);
                }
                $_SESSION['idUsuario'] = $usuario['idUsuario'];
                $_SESSION['nomeUsuario'] = $usuario['nome'];
                $this->CriarLogUsuario($nome);
                header("Location: index.php?action=inicio"); //se o usuário tá certo e a senha também, cria o log e leva pro menu principal
                exit;
            } else {
                $_SESSION['erro_login'] = "Senha ou usuário incorretos, tente novamente"; //caso a senha para aquele usuário esteja errada, joga essa mensagem na tela 
                header("Location: index.php?action=login");
                exit;
            }
        } else {
            // se usuário não existe cria um login pra ele e faz um log mostrando q ele logou
            $usuarioModel->cadastrar($nome, $senha, $caminhoImagem);
            $novoUsuario = $usuarioModel->buscarPorNome($nome);
            $_SESSION['idUsuario'] = $novoUsuario['idUsuario'];
            $_SESSION['nomeUsuario'] = $novoUsuario['nome'];
            $this->CriarLogUsuario($nome);
            header("Location: index.php?action=inicio");
            exit;
        }
    }
    public function logout() { //acaba com a sessão e desloga o player
        session_destroy();
        header("Location: index.php?action=login");
        exit;
    }

    public function CriarLogUsuario($nome) { //logs pro usuário, pede o nome pra mostrar quem é e mostra a hora que o usuário logou

        $diretorioLog = __DIR__ . '/../logs/logs_usuario/';

        $arquivoLog = $diretorioLog . 'logUsuario.txt';
        $dataHora = date("Y-m-d H:i:s");
        $entradaLog = "[$dataHora] - USUÁRIO LOGADO = $nome" . PHP_EOL;

        file_put_contents($arquivoLog, $entradaLog, FILE_APPEND);
    }
}