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
    public function listarPorUsuario($idUsuario) {
        $stmt = $this->db->prepare("
            SELECT inv.idInventario, inv.idItem, inv.quantidade, i.nome, i.descricao
            FROM inventario inv
            INNER JOIN item i ON inv.idItem = i.idItem
            WHERE inv.idUsuario = ? AND inv.quantidade > 0
            ORDER BY i.idItem ASC
        ");
        $stmt->execute([$idUsuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarItemUsuario($idUsuario, $idItem) {
        $stmt = $this->db->prepare("
            SELECT inv.idInventario, inv.idItem, inv.quantidade, i.nome, i.descricao
            FROM inventario inv
            INNER JOIN item i ON inv.idItem = i.idItem
            WHERE inv.idUsuario = ? AND inv.idItem = ? AND inv.quantidade > 0
        ");
        $stmt->execute([$idUsuario, $idItem]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function consumirItem($idUsuario, $idItem) {
        $stmt = $this->db->prepare("
            UPDATE inventario 
            SET quantidade = quantidade - 1 
            WHERE idUsuario = ? AND idItem = ? AND quantidade > 0
        ");
        return $stmt->execute([$idUsuario, $idItem]);
    }
}