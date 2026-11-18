<?php
// Controllers/BatalhaController.php

class BatalhaController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function index() {
        if (!isset($_SESSION['idUsuario'])) {
            header("Location: index.php?action=login");
            exit;
        }

        try {
            $timeModel = new Time($this->db);
            $timesBrutos = $timeModel->buscarTimesDoUsuario($_SESSION['idUsuario']);

            if (empty($timesBrutos)) {
                throw new SemTimeException("Você precisa montar pelo menos um time antes de entrar na arena de batalha!");
            }

            $timesCompletos = [];
            foreach ($timesBrutos as $t) {
                $detalhes = $this->buscarDetalhesTime($t['idTimes']);
                if (!empty($detalhes)) {
                    $timesCompletos[] = $detalhes;
                }
            }

            require_once __DIR__ . '/../View/batalha/selecionarTime.php';

        } catch (SemTimeException $e) {
            $_SESSION['erro_time'] = $e->getMessage();
            header("Location: index.php?action=montar_time");
            exit;
        } catch (Exception $e) {
            $_SESSION['erro_batalha'] = "Erro ao carregar times: " . $e->getMessage();
            header("Location: index.php?action=inicio");
            exit;
        }
    }

    public function arena() {
        if (!isset($_SESSION['idUsuario'])) {
            header("Location: index.php?action=login");
            exit;
        }

        $idTimeSelecionado = $_GET['idTime'] ?? null;

        if (!$idTimeSelecionado) {
            header("Location: index.php?action=batalha");
            exit;
        }

        $timeAtual = $this->buscarDetalhesTime($idTimeSelecionado);

        if (empty($timeAtual) || $timeAtual['idUsuario'] != $_SESSION['idUsuario']) {
            header("Location: index.php?action=batalha");
            exit;
        }

        $oponente = $this->gerarOponenteAleatorio();
        require_once __DIR__ . '/../View/batalha/arena.php';
    }

    private function buscarDetalhesTime($idTimes) {
        $stmt = $this->db->prepare("SELECT * FROM times WHERE idTimes = ?");
        $stmt->execute([$idTimes]);
        $time = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$time) return [];

        $slotIds = array_filter([
            $time['idSlot1'], $time['idSlot2'], $time['idSlot3'],
            $time['idSlot4'], $time['idSlot5'], $time['idSlot6']
        ]);

        if (empty($slotIds)) return [];

        $placeholders = implode(',', array_fill(0, count($slotIds), '?'));
        $stmtSlots = $this->db->prepare("
            SELECT s.*, COALESCE(p.hp, s.hp) AS hpMax
            FROM slot s
            LEFT JOIN pokemon p ON s.idPokemon = p.idPokemon
            WHERE s.idSlot IN ($placeholders)
        ");
        $stmtSlots->execute(array_values($slotIds));
        $slots = $stmtSlots->fetchAll(PDO::FETCH_ASSOC);

        return [
            'idTimes' => $time['idTimes'],
            'idUsuario' => $time['idUsuario'],
            'nome' => $time['nome'],
            'pokemons' => $slots
        ];
    }

    private function gerarOponenteAleatorio() {
        $idAleatorio = rand(1, 151);
        $dadosApi = PokeApiService::buscarPokemonDaApi($idAleatorio);

        if (!$dadosApi) {
            return [
                'nome' => 'Wild Pikachu',
                'tipo1' => 'Electric',
                'hpMax' => 120, 'hp' => 120,
                'atk' => 100, 'def' => 90, 'spe' => 110,
                'ataque1' => 'Thunderbolt', 'ataque2' => 'Quick Attack',
                'img' => 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/25.png'
            ];
        }

        $nome = ucfirst($dadosApi['name']);
        $tipo1 = ucfirst($dadosApi['types'][0]['type']['name']);
        
        $calcStat = function($baseStat, $isHp = false) {
            if ($isHp) {
                return floor(((2 * $baseStat + 31) * 50) / 100) + 50 + 10;
            } else {
                return floor((floor(((2 * $baseStat + 31) * 50) / 100) + 5));
            }
        };

        $hp = $calcStat($dadosApi['stats'][0]['base_stat'], true);
        $atk = $calcStat($dadosApi['stats'][1]['base_stat']);
        $def = $calcStat($dadosApi['stats'][2]['base_stat']);
        $spe = $calcStat($dadosApi['stats'][5]['base_stat']);

        $ataque1 = PokeApiService::buscarAtaquePorTipo($tipo1);
        $ataque2 = "Tackle";
        $img = $dadosApi['sprites']['front_default'] ?? '';

        return [
            'nome' => $nome,
            'tipo1' => $tipo1,
            'hpMax' => $hp,
            'hp' => $hp,
            'atk' => $atk,
            'def' => $def,
            'spe' => $spe,
            'ataque1' => $ataque1,
            'ataque2' => $ataque2,
            'img' => $img
        ];
    }

    public function processarTurno() {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        
        $tipoAtaque = $input['tipoAtaque'] ?? 'ataque2';
        $pkJog = $input['pokemonAtivo'] ?? [];
        $pkOpo = $input['oponente'] ?? [];
        $idSlotAtivo = $pkJog['idSlot'] ?? null;

        $stmtAtivo = $this->db->prepare("
            SELECT s.*, COALESCE(p.hp, s.hp) AS hpMax
            FROM slot s
            LEFT JOIN pokemon p ON s.idPokemon = p.idPokemon
            WHERE s.idSlot = ? AND s.idUsuario = ?
        ");
        $stmtAtivo->execute([$idSlotAtivo, $_SESSION['idUsuario']]);
        $slotAtivo = $stmtAtivo->fetch(PDO::FETCH_ASSOC);

        if (!$slotAtivo) {
            http_response_code(400);
            echo json_encode(['mensagem' => 'Pokémon ativo inválido.']);
            exit;
        }

        if (intval($pkJog['hp'] ?? 0) <= 0) {
            echo json_encode([
                'danoJogador' => 0,
                'danoOponente' => 0,
                'hpOponente' => intval($pkOpo['hp'] ?? 0),
                'hpJogador' => 0,
                'statusJogador' => $slotAtivo['status'],
                'statusOponente' => $pkOpo['status'] ?? null,
                'vitoria' => false,
                'derrota' => true,
                'mensagem' => "Seu pokémon desmaiou e não pode mais lutar!"
            ]);
            exit;
        }

        $danoJogador = 0;
        $danoOponente = 0;
        $logTurno = "";

        $calcularDano = function($atk, $def, $isTipo1) {
            $base = (($atk / max(1, $def)) * 22) + 2;
            if ($isTipo1) $base *= 1.5;
            return max(5, intval($base * (rand(85, 100) / 100)));
        };

        $hpOpoAtual = intval($pkOpo['hp']);
        $hpJogAtual = intval($pkJog['hp']);
        $statusJogador = $slotAtivo['status'];
        $statusOponente = $pkOpo['status'] ?? null;
        $tipoGolpeJogador = ($tipoAtaque === 'ataque1') ? ucfirst($pkJog['tipo1'] ?? 'Normal') : 'Normal';

        $velocidadeJogador = intval($pkJog['spe'] ?? 100);
        $velocidadeOponente = intval($pkOpo['spe'] ?? 100);
        if ($this->statusEh($statusJogador, 'paralysis')) {
            $velocidadeJogador = (int) floor($velocidadeJogador / 2);
        }
        if ($this->statusEh($statusOponente, 'paralysis')) {
            $velocidadeOponente = (int) floor($velocidadeOponente / 2);
        }

        $jogadorAtacaPrimeiro = ($velocidadeJogador >= $velocidadeOponente);
        $acoes = $jogadorAtacaPrimeiro ? ['jogador', 'oponente'] : ['oponente', 'jogador'];

        foreach ($acoes as $atacante) {
            if (($atacante === 'jogador' && $hpJogAtual <= 0) ||
                ($atacante === 'oponente' && $hpOpoAtual <= 0)) {
                continue;
            }

            if ($atacante === 'jogador') {
                $status = &$statusJogador;
                $hpDefensor = $hpOpoAtual;
                $atk = intval($pkJog['atk'] ?? 1);
                $def = intval($pkOpo['def'] ?? 1);
                $isTipo1 = ($tipoAtaque === 'ataque1');
                $tipoGolpe = $tipoGolpeJogador;
            } else {
                $status = &$statusOponente;
                $hpDefensor = $hpJogAtual;
                $atk = intval($pkOpo['atk'] ?? 1);
                $def = intval($pkJog['def'] ?? 1);
                $isTipo1 = (rand(0, 1) === 1);
                $tipoGolpe = $isTipo1 ? ucfirst($pkOpo['tipo1'] ?? 'Normal') : 'Normal';
            }

            $bloqueio = $this->verificarBloqueio($status);
            if ($bloqueio !== null) {
                $logTurno .= ($atacante === 'jogador' ? 'Seu Pokémon ' : 'O oponente ') . $bloqueio . ' ';
                unset($status);
                continue;
            }

            if ($this->statusEh($status, 'paralysis') && rand(1, 100) <= 25) {
                $logTurno .= ($atacante === 'jogador' ? 'Seu Pokémon está paralisado e ' : 'O oponente está paralisado e ') . 'não conseguiu atacar! ';
                unset($status);
                continue;
            }

            if ($this->statusEh($status, 'burn')) {
                $atk = (int) floor($atk / 2);
            }

            $dano = $calcularDano($atk, $def, $isTipo1);
            $hpDefensor = max(0, $hpDefensor - $dano);

            if ($atacante === 'jogador') {
                $danoJogador = $dano;
                $hpOpoAtual = $hpDefensor;
            } else {
                $danoOponente = $dano;
                $hpJogAtual = $hpDefensor;
            }

            $logTurno .= ($atacante === 'jogador' ? 'Usaste o ataque e causaste ' : 'O oponente atacou e causou ') . "{$dano} de dano. ";

            $statusDefensor = ($atacante === 'jogador') ? $statusOponente : $statusJogador;
            if ($hpDefensor > 0 && empty($statusDefensor)) {
                $novaCondicao = $this->condicaoPorTipo($tipoGolpe);
                if ($novaCondicao !== null && rand(1, 100) <= 10) {
                    if ($atacante === 'jogador') {
                        $statusOponente = $novaCondicao;
                    } else {
                        $statusJogador = $novaCondicao;
                    }
                    $logTurno .= 'O golpe causou ' . $this->nomeStatus($novaCondicao) . '! ';
                }
            }

            unset($status);
        }

        if ($hpJogAtual > 0) {
            $this->aplicarDanoResidual($statusJogador, $hpJogAtual, intval($slotAtivo['hpMax'] ?? $slotAtivo['hp']), $hpJogAtual, $logTurno, 'Seu Pokémon');
        }
        if ($hpOpoAtual > 0) {
            $this->aplicarDanoResidual($statusOponente, $hpOpoAtual, intval($pkOpo['hpMax'] ?? $pkOpo['hp']), $hpOpoAtual, $logTurno, 'O oponente');
        }
        if ($hpJogAtual <= 0) {
            $statusJogador = null;
        }
        if ($hpOpoAtual <= 0) {
            $statusOponente = null;
        }

        $stmtSlot = $this->db->prepare("UPDATE slot SET hp = ?, status = ? WHERE idSlot = ? AND idUsuario = ?");
        $stmtSlot->execute([max(0, $hpJogAtual), $statusJogador, $idSlotAtivo, $_SESSION['idUsuario']]);

        $vitoria = ($hpOpoAtual <= 0);
        $derrota = ($hpJogAtual <= 0);

        if ($vitoria) {
            $stmt = $this->db->prepare("UPDATE usuario SET dinheiro = dinheiro + 500, wins = wins + 1 WHERE idUsuario = ?");
            $stmt->execute([$_SESSION['idUsuario']]);
            $logTurno .= "Derrotou o pokémon adversário! E ganhou 500PkD$!";
        } elseif ($derrota) {
            $stmt = $this->db->prepare("UPDATE usuario SET loss = loss + 1 WHERE idUsuario = ?");
            $stmt->execute([$_SESSION['idUsuario']]);
            $logTurno .= "Seu pokémon desmaiou!";
        }

        echo json_encode([
            'danoJogador' => $danoJogador,
            'danoOponente' => $danoOponente,
            'hpOponente' => $hpOpoAtual,
            'hpJogador' => $hpJogAtual,
            'statusJogador' => $statusJogador,
            'statusOponente' => $statusOponente,
            'vitoria' => $vitoria,
            'derrota' => $derrota,
            'mensagem' => $logTurno
        ]);
        exit;
    }

    public function usarItemBatalha() {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        $idSlot = $input['idSlot'] ?? null;
        $idItem = $input['idItem'] ?? null;

        $timeModel = new Time($this->db);
        $inventarioModel = new Inventario($this->db);
        $slot = $timeModel->buscarSlotDoUsuario($idSlot, $_SESSION['idUsuario']);
        $item = $inventarioModel->buscarItemUsuario($_SESSION['idUsuario'], $idItem);

        if (!$slot || !$item || intval($slot['hp']) <= 0) {
            http_response_code(400);
            echo json_encode(['mensagem' => 'Pokémon ou item inválido.']);
            exit;
        }

        $status = $slot['status'] ?? '';
        $nomeItem = $item['nome'];
        $statusItem = [
            'Antidote' => 'poison',
            'Paralyze Heal' => 'paralysis',
            'Burn Heal' => 'burn',
            'Ice Heal' => 'freeze',
            'Awakening' => 'sleep'
        ];
        $curaStatus = ($nomeItem === 'Full Heal' || $nomeItem === 'Full Restore');

        if (isset($statusItem[$nomeItem])) {
            $curaStatus = $this->statusEh($status, $statusItem[$nomeItem]);
        }

        $recuperaHp = ($nomeItem === 'Full Restore');
        if (!$curaStatus && !$recuperaHp) {
            http_response_code(400);
            echo json_encode(['mensagem' => 'Esse item não pode ser usado nessa situação.']);
            exit;
        }

        if ($nomeItem === 'Full Heal' && empty($status)) {
            http_response_code(400);
            echo json_encode(['mensagem' => 'O Pokémon não possui condição de status.']);
            exit;
        }

        $novoHp = $recuperaHp ? intval($slot['hpMax']) : intval($slot['hp']);
        if ($recuperaHp && $novoHp === intval($slot['hp']) && empty($status)) {
            http_response_code(400);
            echo json_encode(['mensagem' => 'O Pokémon já está com HP cheio e sem condições de status.']);
            exit;
        }
        $novoStatus = $curaStatus ? null : $status;
        $stmt = $this->db->prepare("UPDATE slot SET hp = ?, status = ? WHERE idSlot = ? AND idUsuario = ?");
        $stmt->execute([$novoHp, $novoStatus, $idSlot, $_SESSION['idUsuario']]);
        $inventarioModel->consumirItem($_SESSION['idUsuario'], $idItem);

        echo json_encode([
            'mensagem' => "{$nomeItem} usado em {$slot['nome']}!",
            'hp' => $novoHp,
            'status' => $novoStatus
        ]);
        exit;
    }

    private function statusEh($status, $condicao) {
        if (!$status) {
            return false;
        }
        $statusBase = explode(':', strtolower($status))[0];
        $sinonimos = [
            'burn' => ['burn', 'burned', 'queimado', 'brn'],
            'freeze' => ['freeze', 'frozen', 'congelado', 'frz'],
            'paralysis' => ['paralysis', 'paralyzed', 'paralisado', 'par'],
            'poison' => ['poison', 'poisoned', 'envenenado', 'psn'],
            'sleep' => ['sleep', 'asleep', 'dormindo', 'slp']
        ];
        return in_array($statusBase, $sinonimos[$condicao] ?? [$condicao], true);
    }

    private function condicaoPorTipo($tipo) {
        $condicoes = [
            'Fire' => 'burn',
            'Ice' => 'freeze',
            'Electric' => 'paralysis',
            'Poison' => 'poison',
            'Psychic' => 'sleep:' . rand(2, 4)
        ];
        return $condicoes[$tipo] ?? null;
    }

    private function nomeStatus($status) {
        $nomes = [
            'burn' => 'queimadura',
            'freeze' => 'congelamento',
            'paralysis' => 'paralisia',
            'poison' => 'envenenamento',
            'sleep' => 'sono'
        ];
        $tipo = explode(':', $status)[0];
        return $nomes[$tipo] ?? $tipo;
    }

    private function verificarBloqueio(&$status) {
        if ($this->statusEh($status, 'freeze')) {
            if (rand(1, 100) <= 20) {
                $status = null;
                return 'descongelou e pode agir!';
            }
            return 'está congelado e não pode agir!';
        }

        if ($this->statusEh($status, 'sleep')) {
            $partes = explode(':', $status);
            $turnosRestantes = max(0, intval($partes[1] ?? 2) - 1);
            $status = $turnosRestantes > 0 ? 'sleep:' . $turnosRestantes : null;
            return 'está dormindo e não pode agir!';
        }

        return null;
    }

    private function aplicarDanoResidual($status, $hpAntes, $hpMax, &$hp, &$log, $nomePokemon) {
        if (!$this->statusEh($status, 'burn') && !$this->statusEh($status, 'poison')) {
            return;
        }

        $dano = max(1, (int) floor($hpMax / 16));
        $hp = max(0, $hpAntes - $dano);
        $log .= "{$nomePokemon} sofreu {$dano} de dano por status! ";
    }
}