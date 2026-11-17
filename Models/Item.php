<?php
class Item {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function listarTodos() {
        $stmt = $this->db->query("SELECT idItem, nome, descricao, preco FROM item");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId($idItem) {
        $stmt = $this->db->prepare("SELECT * FROM item WHERE idItem = ?");
        $stmt->execute([$idItem]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}