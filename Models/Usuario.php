<?php
class Usuario {
    private $db;
    public function __construct($db) {
        $this->db = $db;
    }

    public function buscarPorId($idUsuario) {
        $stmt = $this->db->prepare("SELECT * FROM usuario WHERE idUsuario = ?");
        $stmt->execute([$idUsuario]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function buscarPorNome($nome) {
        $stmt = $this->db->prepare("SELECT * FROM usuario WHERE nome = ?");
        $stmt->execute([$nome]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function cadastrar($nome, $senha, $img) { //usada para fazer o login do novo usuário
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO usuario (nome, senha, dinheiro, img) VALUES (?, ?, 5000, ?)");
        return $stmt->execute([$nome, $senhaHash, $img]);
    }

    public function atualizarImagem($idUsuario, $img) {
        $stmt = $this->db->prepare("UPDATE usuario SET img = ? WHERE idUsuario = ?");
        return $stmt->execute([$img, $idUsuario]);
    }

    public function atualizarDinheiro($idUsuario, $novoDinheiro) { //atualiza o dinheiro (basicamente um set só q em php)
        $stmt = $this->db->prepare("UPDATE usuario SET dinheiro = ? WHERE idUsuario = ?");
        return $stmt->execute([$novoDinheiro, $idUsuario]);
    }
}