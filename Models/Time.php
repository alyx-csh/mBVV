<?php
class Time {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // busca os times do usuário logado
    public function buscarTimesDoUsuario($idUsuario) {
        $stmt = $this->db->prepare("SELECT * FROM times WHERE idUsuario = ?");
        $stmt->execute([$idUsuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // contagem dos times do usuário logado para a exceção
    public function contarTimesDoUsuario($idUsuario) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM times WHERE idUsuario = ?");
        $stmt->execute([$idUsuario]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return intval($resultado['total'] ?? 0);
    }

    public function criarTimeComSlots($idUsuario, $nomeTime, $slotsIds) { //cria time, caso o nome passado não exista, só anula eles
        $s1 = $slotsIds[0] ?? null;
        $s2 = $slotsIds[1] ?? null;
        $s3 = $slotsIds[2] ?? null;
        $s4 = $slotsIds[3] ?? null;
        $s5 = $slotsIds[4] ?? null;
        $s6 = $slotsIds[5] ?? null;

        $stmt = $this->db->prepare("
            INSERT INTO times (idUsuario, nome, idSlot1, idSlot2, idSlot3, idSlot4, idSlot5, idSlot6)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$idUsuario, $nomeTime, $s1, $s2, $s3, $s4, $s5, $s6]);
    }

    public function buscarTimesCompletos($idUsuario) { //retorna os dados completos dos times pertencentes ao usuário
        $times = $this->buscarTimesDoUsuario($idUsuario);
        $resultado = [];

        foreach ($times as $time) {
            $slotIds = array_values(array_filter([
                $time['idSlot1'], $time['idSlot2'], $time['idSlot3'],
                $time['idSlot4'], $time['idSlot5'], $time['idSlot6']
            ]));

            $pokemons = [];
            if (!empty($slotIds)) {
                $placeholders = implode(',', array_fill(0, count($slotIds), '?'));
                $stmt = $this->db->prepare("
                    SELECT s.*, COALESCE(p.hp, s.hp) AS hpMax
                    FROM slot s
                    LEFT JOIN pokemon p ON s.idPokemon = p.idPokemon
                    WHERE s.idSlot IN ($placeholders)
                ");
                $stmt->execute($slotIds);
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Mantém a ordem dos slots 1 a 6
                $porId = [];
                foreach ($rows as $r) {
                    $porId[$r['idSlot']] = $r;
                }
                foreach ($slotIds as $id) {
                    if (isset($porId[$id])) {
                        $pokemons[] = $porId[$id];
                    }
                }
            }

            $resultado[] = [
                'idTimes' => $time['idTimes'],
                'nome' => $time['nome'],
                'pokemons' => $pokemons
            ];
        }
            return $resultado;
    }

    public function buscarSlotDoUsuario($idSlot, $idUsuario) { //busca um slot específico e garante que seja do usuário logado
        $stmt = $this->db->prepare("
            SELECT s.*, COALESCE(p.hp, s.hp) AS hpMax
            FROM slot s
            LEFT JOIN pokemon p ON s.idPokemon = p.idPokemon
            WHERE s.idSlot = ? AND s.idUsuario = ?
        ");
        $stmt->execute([$idSlot, $idUsuario]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}