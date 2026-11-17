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

    public function cadastrar($nome, $senha) {
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO usuario (nome, senha, dinheiro) VALUES (?, ?, 1000)");
        return $stmt->execute([$nome, $senhaHash]);
    }

    public function atualizarDinheiro($idUsuario, $novoDinheiro) {
        $stmt = $this->db->prepare("UPDATE usuario SET dinheiro = ? WHERE idUsuario = ?");
        return $stmt->execute([$novoDinheiro, $idUsuario]);
    }
}