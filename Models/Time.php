<?php
class Time {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function contarTimesDoUsuario($idUsuario) {
    // Procura na tabela batalha os times associados ao idUser1 deste usuário
    $stmt = $this->db->prepare("
        SELECT COUNT(DISTINCT t.idTimes) as total 
        FROM times t
        JOIN batalha b ON (t.idTimes = b.idTimes1 OR t.idTimes = b.idTimes2)
        WHERE b.idUser1 = ? OR b.idUser2 = ?
    ");
    $stmt->execute([$idUsuario, $idUsuario]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return intval($resultado['total']);
}

    public function criarTimeComSlots($idUsuario, $nomeTime, $slotsIds) {
    $s1 = $slotsIds[0] ?? null;
    $s2 = $slotsIds[1] ?? null;
    $s3 = $slotsIds[2] ?? null;
    $s4 = $slotsIds[3] ?? null;
    $s5 = $slotsIds[4] ?? null;
    $s6 = $slotsIds[5] ?? null;

    // Inclui idUsuario na query de inserção do time
    $stmt = $this->db->prepare("
        INSERT INTO times (idUsuario, nome, idSlot1, idSlot2, idSlot3, idSlot4, idSlot5, idSlot6)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
        return $stmt->execute([$idUsuario, $nomeTime, $s1, $s2, $s3, $s4, $s5, $s6]);
    }
}