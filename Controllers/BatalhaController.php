<?php
class BatalhaController {
    private $db;
    public function __construct($db) {
        $this->db = $db;
    }

    public function index() { 
        if (!isset($_SESSION['idUsuario'])) { //se usuário não estiver logado, é levado pro login
            header("Location: index.php?action=login");
            exit;
        }

        try { //checa pra ver se o usuário tem time
            $timeModel = new Time($this->db);
            $timesBrutos = $timeModel->buscarTimesDoUsuario($_SESSION['idUsuario']);

            if (empty($timesBrutos)) {
                throw new SemTimeException("Você precisa de um time para batalhar");
            }

            $timesCompletos = [];
            $temPokemonVivo = false;

            foreach ($timesBrutos as $t) {
                $detalhes = $this->buscarDetalhesTime($t['idTimes']);
                if (!empty($detalhes)) {
                    $timesCompletos[] = $detalhes;

                    foreach ($detalhes['pokemons'] as $pokemon) {
                        if (isset($pokemon['hp']) && $pokemon['hp'] > 0) {
                            $temPokemonVivo = true;
                            break;
                        }
                    }
                }
            }

            if (!$temPokemonVivo) {
                throw new SemPokVivoException("Time incapaz de lutar, compre revive(s) para lutar com este time");
            }
            require_once __DIR__ . '/../View/batalha/selecionarTime.php';

        } catch (SemTimeException $e) { // exception para caso o usuário não tenha time, botar ele direto na página de carregar time
            $_SESSION['erro_time'] = $e->getMessage();
            header("Location: index.php?action=novo_time");
            exit;
        } catch (SemPokVivoException $e) { //exception para caso o usuário tenha time, porém os pokémons estejam mortos
            $_SESSION['erro_vida'] = $e->getMessage();
            header("Location: index.php?action=loja");
            exit; 
        } catch (Exception $e) { // exception pra caso os times não carreguem
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

        try {
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

            $temPokemonVivo = false;
            foreach ($timeAtual['pokemons'] as $pokemon) {
                if (isset($pokemon['hp']) && (int)$pokemon['hp'] > 0) {
                    $temPokemonVivo = true;
                    break;
                }
            }
            if (!$temPokemonVivo) {
                throw new SemPokVivoException("Time escolhido incapaz de lutar, compre revive(s)");
            }

            $inventarioModel = new Inventario($this->db);
            $itensUsuario = $inventarioModel->listarPorUsuario($_SESSION['idUsuario']);
            $oponente = $this->gerarOponenteAleatorio();

            require_once __DIR__ . '/../View/batalha/arena.php';

        } catch (SemPokVivoException $e) {
            $_SESSION['erro_vida'] = $e->getMessage();
            header("Location: index.php?action=loja");
            exit;
        }
    }

    private function buscarDetalhesTime($idTimes) { //função pra pegar os detalhes do time, quem tá nele assim como seu HP
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

    private function gerarOponenteAleatorio() { //vai na API e pega um pokémon para batalhar contigo, podendo ser modificado via rand, dando a opção de limitar a alguma geração
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

        $statusJogador = $pkJog['status'] ?? null;
        $statusOponente = $pkOpo['status'] ?? null;
        $jogadorAtacaPrimeiro = (intval($pkJog['spe'] ?? 0) >= intval($pkOpo['spe'] ?? 0));

        if (intval($pkJog['hp']) <= 0) {
            echo json_encode([
                'danoJogador' => 0,
                'danoOponente' => 0,
                'hpOponente' => intval($pkOpo['hp'] ?? 0),
                'hpJogador' => 0,
                'statusJogador' => $statusJogador,
                'statusOponente' => $statusOponente,
                'vitoria' => false,
                'derrota' => true,
                'mensagem' => "Seu pokémon desmaiou e não pode mais lutar"
            ]);
            exit;
        }

        //seta os danos para zero e bota um null no logTurno
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

        if ($jogadorAtacaPrimeiro) {
            $isTipo1 = ($tipoAtaque === 'ataque1');
            $danoJogador = $calcularDano($pkJog['atk'], $pkOpo['def'], $isTipo1);
            $hpOpoAtual = max(0, $hpOpoAtual - $danoJogador);
            
            $logTurno .= "Seu pokémon atacou e causou {$danoJogador} de dano ";

            if ($hpOpoAtual > 0) {
                $atqOpoTipo1 = (rand(0, 1) == 1);
                $danoOponente = $calcularDano($pkOpo['atk'], $pkJog['def'], $atqOpoTipo1);
                $hpJogAtual = max(0, $hpJogAtual - $danoOponente);
                $logTurno .= "o pokémon oponente atacou e causou {$danoOponente} de dano ";
            }
        } else {
            $atqOpoTipo1 = (rand(0, 1) == 1);
            $danoOponente = $calcularDano($pkOpo['atk'], $pkJog['def'], $atqOpoTipo1);
            $hpJogAtual = max(0, $hpJogAtual - $danoOponente);
            
            $logTurno .= "O pokémon oponente atacou e causou {$danoOponente} de dano ";

            if ($hpJogAtual > 0) {
                $isTipo1 = ($tipoAtaque === 'ataque1');
                $danoJogador = $calcularDano($pkJog['atk'], $pkOpo['def'], $isTipo1);
                $hpOpoAtual = max(0, $hpOpoAtual - $danoJogador);
                $logTurno .= "seu pokémon atacou e causou {$danoJogador} de dano ";
            }
        }

        // mantém o hp q o pokémon tinha no final da batalha no banco de dados
        if ($idSlotAtivo) {
            try {
                $hpParaSalvar = max(0, $hpJogAtual);
                $stmtSlot = $this->db->prepare("UPDATE slot SET hp = ? WHERE idSlot = ? AND idUsuario = ?");
                $stmtSlot->execute([$hpParaSalvar, $idSlotAtivo, $_SESSION['idUsuario']]);
            } catch (PDOException $e) {
                error_log("Erro ao persistir HP do slot: " . $e->getMessage());
            }
        }

        $vitoria = ($hpOpoAtual <= 0);
        $derrota = ($hpJogAtual <= 0);

        if ($vitoria) {
            $stmt = $this->db->prepare("UPDATE usuario SET dinheiro = dinheiro + 500, wins = wins + 1 WHERE idUsuario = ?");
            $stmt->execute([$_SESSION['idUsuario']]);
            $logTurno .= " você derrotou o pokémon adversário e ganhou 500PkD$!";
            $this->CriarLogTurno($danoJogador, $danoOponente, $hpJogAtual, $hpOpoAtual);
            $this->CriarLogBatalha("VITÓRIA", $pkJog, $pkOpo);
        } elseif ($derrota) {
            $stmt = $this->db->prepare("UPDATE usuario SET loss = loss + 1 WHERE idUsuario = ?");
            $stmt->execute([$_SESSION['idUsuario']]);
            $logTurno .= " seu pokémon desmaiou!";
            $this->CriarLogTurno($danoJogador, $danoOponente, $hpJogAtual, $hpOpoAtual);
            $this->CriarLogBatalha("DERROTA", $pkJog, $pkOpo);
        } else {
            $this->CriarLogTurno($danoJogador, $danoOponente, $hpJogAtual, $hpOpoAtual);
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

    public function CriarLogBatalha($resultadoBatalha, $pkJog, $pkOpo) {

        $diretorioLog = __DIR__ . '/../logs/logs_batalha/';
        $nomeUsuario = $_SESSION['nomeUsuario'];

        $arquivoLog = $diretorioLog . 'logBatalha.txt';
        $dataHora = date("Y-m-d H:i:s");
        $entradaLog = "[$dataHora] - LOG DE BATALHA DE $nomeUsuario (UTILIZANDO {$pkJog['nome']}) = $resultadoBatalha CONTRA {$pkOpo['nome']} OPONENTE" . PHP_EOL;

        file_put_contents($arquivoLog, $entradaLog, FILE_APPEND);
        $stmt = $this->db->prepare("INSERT INTO logBatalha (idUser, pokeOp, idTime, textoLog) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['idUsuario'], $pkOpo['nome'], $pkJog['idTimes'], $entradaLog]);
    }

    public function CriarLogTurno($danoJogador, $danoOponente, $hpJogAtual, $hpOpoAtual) {

        $nomeUsuario = $_SESSION['nomeUsuario'];
        $diretorioLog = __DIR__ . '/../logs/logs_batalha/';

        $arquivoLog = $diretorioLog . 'logBatalha.txt';
        $dataHora = date("Y-m-d H:i:s");
        $entradaLog = "[$dataHora] - $nomeUsuario CAUSOU " . $danoJogador . " DE DANO (VIDA ATUAL " . $hpJogAtual . ") - OPONENTE CAUSOU " . $danoOponente . " DE DANO (VIDA ATUAL " . $hpOpoAtual . ")" . PHP_EOL;

        file_put_contents($arquivoLog, $entradaLog, FILE_APPEND);
    }

    
}