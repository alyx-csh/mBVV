<?php
class Inventario {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function adicionarItem($idUsuario, $idItem) {
        $stmt = $this->db->prepare("SELECT * FROM inventario WHERE idUsuario = ? AND idItem = ?");
        $stmt->execute([$idUsuario, $idItem]);
        $itemInventario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($itemInventario) {
            $stmtUp = $this->db->prepare("UPDATE inventario SET quantidade = quantidade + 1 WHERE idUsuario = ? AND idItem = ?");
            return $stmtUp->execute([$idUsuario, $idItem]);
        } else {
            $stmtIns = $this->db->prepare("INSERT INTO inventario (idUsuario, idItem, quantidade) VALUES (?, ?, 1)");
            return $stmtIns->execute([$idUsuario, $idItem]);
        }
    }
}