<?php
class BatalhaController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function index() { //se usuário não estiver logado, é levado pro login
        if (!isset($_SESSION['idUsuario'])) {
            header("Location: index.php?action=login");
            exit;
        }

        try { //checa pra ver se o usuário tem time
            $timeModel = new Time($this->db);
            $timesBrutos = $timeModel->buscarTimesDoUsuario($_SESSION['idUsuario']);

            if (empty($timesBrutos)) {
                throw new SemTimeException("Você precisa de um time para batalhar!");
            }

            $timesCompletos = [];
            foreach ($timesBrutos as $t) {
                $detalhes = $this->buscarDetalhesTime($t['idTimes']);
                if (!empty($detalhes)) {
                    $timesCompletos[] = $detalhes;
                }
            }

            require_once __DIR__ . '/../View/batalha/selecionarTime.php';

        } catch (SemTimeException $e) { // exception para caso o usuário não tenha time, botar ele direto na página de carregar time
            $_SESSION['erro_time'] = $e->getMessage();
            header("Location: index.php?action=montar_time");
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
        } //caso usuário não esteja logado leva ele pra login

        $idTimeSelecionado = $_GET['idTime'] ?? null; //cria a variável para permitir a seleção de diferentes times

        if (!$idTimeSelecionado) {
            header("Location: index.php?action=batalha");
            exit; //ao selecionar o time leva ele pra batalha
        }

        $timeAtual = $this->buscarDetalhesTime($idTimeSelecionado);

        if (empty($timeAtual) || $timeAtual['idUsuario'] != $_SESSION['idUsuario']) {
            header("Location: index.php?action=batalha");
            exit;
        }

        $oponente = $this->gerarOponenteAleatorio();
        require_once __DIR__ . '/../View/batalha/arena.php';
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
        $stmtSlots = $this->db->prepare("SELECT * FROM slot WHERE idSlot IN ($placeholders)");
        $stmtSlots->execute(array_values($slotIds));
        $slots = $stmtSlots->fetchAll(PDO::FETCH_ASSOC);

        foreach ($slots as &$slot) {
            // Guardamos o hpMax fixo e mantemos o 'hp' atual vindo diretamente do banco
            if (!isset($slot['hpMax'])) {
                // Se precisar calcular o teto máximo com base nos stats originais, podes manter uma coluna ou estimar
                $slot['hpMax'] = $slot['hp'] > 0 ? max($slot['hp'], 100) : 100; 
            }
        }

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

        if (intval($pkJog['hp']) <= 0) { //condição para caso o pokémon desmaie ele te notifica
            echo json_encode([
                'danoJogador' => 0,
                'danoOponente' => 0,
                'hpOponente' => $pkOpo['hp'],
                'hpJogador' => 0,
                'vitoria' => false,
                'derrota' => true,
                'mensagem' => "Seu pokémon desmaiou e não pode mais lutar!"
            ]);
            exit;
        }

        //seta os danos para zero e bota um null no logTurno
        $danoJogador = 0;
        $danoOponente = 0;
        $logTurno = "";

        $velocidadeJogador = intval($pkJog['spe'] ?? 100);
        $velocidadeOponente = intval($pkOpo['spe'] ?? 100);
        $jogadorAtacaPrimeiro = ($velocidadeJogador >= $velocidadeOponente);

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
            
            $logTurno .= "seu pokemon atacou o oponente e deu {$danoJogador} de dano ";

            if ($hpOpoAtual > 0) {
                $atqOpoTipo1 = (rand(0, 1) == 1);
                $danoOponente = $calcularDano($pkOpo['atk'], $pkJog['def'], $atqOpoTipo1);
                $hpJogAtual = max(0, $hpJogAtual - $danoOponente);
                $logTurno .= "o oponente atacou e causou {$danoOponente} de dano";
            }
        } else {
            $atqOpoTipo1 = (rand(0, 1) == 1);
            $danoOponente = $calcularDano($pkOpo['atk'], $pkJog['def'], $atqOpoTipo1);
            $hpJogAtual = max(0, $hpJogAtual - $danoOponente);
            
            $logTurno .= "o oponente atacou e causou {$danoOponente} de dano ";

            if ($hpJogAtual > 0) {
                $isTipo1 = ($tipoAtaque === 'ataque1');
                $danoJogador = $calcularDano($pkJog['atk'], $pkOpo['def'], $isTipo1);
                $hpOpoAtual = max(0, $hpOpoAtual - $danoJogador);
                $logTurno .= "seu pokemon atacou o oponente e deu {$danoJogador} de dano";
            }
        }

        // mudar o hp no php
        if ($idSlotAtivo) {
            try {
                $hpParaSalvar = max(0, $hpJogAtual);
                $stmtSlot = $this->db->prepare("UPDATE slot SET hp = ? WHERE idSlot = ? AND idUsuario = ?");
                $stmtSlot->execute([$hpParaSalvar, $idSlotAtivo, $_SESSION['idUsuario']]);
            } catch (PDOException $e) {
                error_log("Erro transferir hp para o banco " . $e->getMessage());
            }
        }

        $vitoria = ($hpOpoAtual <= 0);
        $derrota = ($hpJogAtual <= 0);

        if ($vitoria) {
            $stmt = $this->db->prepare("UPDATE usuario SET dinheiro = dinheiro + 500, wins = wins + 1 WHERE idUsuario = ?");
            $stmt->execute([$_SESSION['idUsuario']]);
            $logTurno .= " você derrotou o pokémon adversário e ganhou 500PkD$!";
            $this->CriarLog("VITÓRIA");
        } elseif ($derrota) {
            $stmt = $this->db->prepare("UPDATE usuario SET loss = loss + 1 WHERE idUsuario = ?");
            $stmt->execute([$_SESSION['idUsuario']]);
            $logTurno .= " seu pokémon desmaiou!";
            $this->CriarLog("DERROTA");
        }

        echo json_encode([
            'danoJogador' => $danoJogador,
            'danoOponente' => $danoOponente,
            'hpOponente' => $hpOpoAtual,
            'hpJogador' => $hpJogAtual,
            'vitoria' => $vitoria,
            'derrota' => $derrota,
            'mensagem' => $logTurno
        ]);
        exit;
    }

    public function CriarLog($resultadoBatalha) {

            $diretorioLog = __DIR__ . '/../logs/logs_batalha/';

            $arquivoLog = $diretorioLog . 'logBatalha.txt';
            $dataHora = date("Y-m-d H:i:s");
            $entradaLog = "[$dataHora] - LOG DE BATALHA = $resultadoBatalha" . PHP_EOL;

            file_put_contents($arquivoLog, $entradaLog, FILE_APPEND);
            }
}