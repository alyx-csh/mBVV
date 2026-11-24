<?php
class BatalhaController {
    private $db;
    public function __construct($db) {
        $this->db = $db;
    }

    public function index() {
        if (!isset($_SESSION['idUsuario'])) { // se usuário não estiver logado, é levado pro login
            header("Location: index.php?action=login");
            exit;
        }

        try { // checa pra ver se o usuário tem time
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
        } catch (SemPokVivoException $e) { // exception para caso o usuário tenha time, porém os pokémons estejam mortos
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
            foreach ($timeAtual['pokemons'] as &$pokemon) {
                $pokemon['status'] = $this->normalizarStatus($pokemon['status'] ?? null);
                if (isset($pokemon['hp']) && (int)$pokemon['hp'] > 0) {
                    $temPokemonVivo = true;
                }
            }
            unset($pokemon);
            if (!$temPokemonVivo) {
                throw new SemPokVivoException("Time escolhido incapaz de lutar, compre revive(s)");
            }

            $inventarioModel = new Inventario($this->db);
            $itensUsuario = $inventarioModel->listarPorUsuario($_SESSION['idUsuario']);
            $oponente = $this->gerarOponenteAleatorio();

            $_SESSION['turno_batalha'] = 0; // reiniciar contador de turno
            require_once __DIR__ . '/../View/batalha/arena.php';
        } catch (SemPokVivoException $e) {
            $_SESSION['erro_vida'] = $e->getMessage();
            header("Location: index.php?action=loja");
            exit;
        }
    }

    private function buscarDetalhesTime($idTimes) { // função pra pegar os detalhes do time, quem tá nele assim como seu HP
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

    private function gerarOponenteAleatorio() { // vai na API e pega um pokémon para batalhar contigo
        $idAleatorio = rand(1, 1025);
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

    private function normalizarStatus(?string $status): ?string {
        if ($status === null) {
            return null;
        }

        $valor = mb_strtolower(trim($status));
        if ($valor === '' || $valor === 'null' || $valor === 'saudável' || $valor === 'saudavel') {
            return null;
        }

        $mapa = [
            'burn' => 'queimado',
            'burned' => 'queimado',
            'queimado' => 'queimado',
            'brn' => 'queimado',

            'paralysis' => 'paralisado',
            'paralyzed' => 'paralisado',
            'paralisado' => 'paralisado',
            'par' => 'paralisado',

            'freeze' => 'congelado',
            'frozen' => 'congelado',
            'congelado' => 'congelado',
            'frz' => 'congelado',

            'poison' => 'envenenado',
            'poisoned' => 'envenenado',
            'envenenado' => 'envenenado',
            'psn' => 'envenenado',

            'sleep' => 'dormindo',
            'asleep' => 'dormindo',
            'dormindo' => 'dormindo',
            'slp' => 'dormindo',
        ];

        return $mapa[$valor] ?? $valor;
    }

    private function sortearNovoStatus(?string $statusAtual, string $tipoAtaque, string $tipoPokemon, string $nomeAlvo, string &$log): ?string {
        if ($statusAtual !== null) {
            return $statusAtual;
        }

        $tipoLower = mb_strtolower(trim($tipoPokemon));
        $candidato = null;
        $chance = 0;

        if ($tipoAtaque === 'ataque1') {
            if ($tipoLower === 'fire') {
                $candidato = 'queimado';
                $chance = 20;
            } elseif ($tipoLower === 'electric') {
                $candidato = 'paralisado';
                $chance = 20;
            } elseif ($tipoLower === 'ice') {
                $candidato = 'congelado';
                $chance = 15;
            } elseif ($tipoLower === 'poison') {
                $candidato = 'envenenado';
                $chance = 25;
            }
        } elseif ($tipoAtaque === 'ataque2') {
            $candidato = 'dormindo';
            $chance = 15;
        }

        if ($candidato !== null && rand(1, 100) <= $chance) {
            $log .= "{$nomeAlvo} foi afetado por {$candidato}! ";
            return $candidato;
        }

        return null;
    }

    private function podeAgirNoTurno(?string &$status, string $nomePk, string &$log): bool {
        if ($status === 'paralisado') {
            if (rand(1, 100) <= 25) {
                $log .= "{$nomePk} está paralisado e não conseguiu atacar! ";
                return false;
            }
        } elseif ($status === 'congelado') {
            if (rand(1, 100) <= 20) {
                $status = null;
                $log .= "{$nomePk} descongelou! ";
            } else {
                $log .= "{$nomePk} está congelado e não pode se mover! ";
                return false;
            }
        } elseif ($status === 'dormindo') {
            if (rand(1, 100) <= 33) {
                $status = null;
                $log .= "{$nomePk} acordou! ";
            } else {
                $log .= "{$nomePk} está dormindo! ";
                return false;
            }
        }

        return true;
    }

    private function calcularDanoResidualStatus(?string $status, int $hpMax, string $nomePk, string &$log): int {
        if ($hpMax <= 0) {
            return 0;
        }

        if ($status === 'envenenado') {
            $dano = max(1, (int) floor($hpMax / 8));
            $log .= "{$nomePk} sofreu {$dano} de dano por envenenamento! ";
            return $dano;
        }

        if ($status === 'queimado') {
            $dano = max(1, (int) floor($hpMax / 16));
            $log .= "{$nomePk} sofreu {$dano} de dano por queimadura! ";
            return $dano;
        }

        return 0;
    }

    public function processarTurno() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['idUsuario'])) {
            http_response_code(401);
            echo json_encode(['erro' => 'Sessão expirada.']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        $tipoAtaque = $input['tipoAtaque'] ?? 'ataque2';
        $pkJog = $input['pokemonAtivo'] ?? [];
        $pkOpo = $input['oponente'] ?? [];
        $idSlotAtivo = !empty($pkJog['idSlot']) ? (int) $pkJog['idSlot'] : null;

        $statusJogador = $this->normalizarStatus($pkJog['status'] ?? null);
        $statusOponente = $this->normalizarStatus($pkOpo['status'] ?? null);

        $hpOpoAtual = max(0, intval($pkOpo['hp'] ?? 0));
        $hpJogAtual = max(0, intval($pkJog['hp'] ?? 0));
        $hpJogMax = max(1, intval($pkJog['hpMax'] ?? $hpJogAtual));
        $hpOpoMax = max(1, intval($pkOpo['hpMax'] ?? $hpOpoAtual));

        if ($hpJogAtual <= 0) {
            echo json_encode([
                'danoJogador' => 0,
                'danoOponente' => 0,
                'hpOponente' => $hpOpoAtual,
                'hpJogador' => 0,
                'statusJogador' => $statusJogador,
                'statusOponente' => $statusOponente,
                'vitoria' => false,
                'derrota' => true,
                'mensagem' => "Seu pokémon desmaiou e não pode mais lutar"
            ]);
            exit;
        }

        $speJogEfetiva = intval($pkJog['spe'] ?? 0) * ($statusJogador === 'paralisado' ? 0.5 : 1.0);
        $speOpoEfetiva = intval($pkOpo['spe'] ?? 0) * ($statusOponente === 'paralisado' ? 0.5 : 1.0);
        $jogadorAtacaPrimeiro = ($speJogEfetiva >= $speOpoEfetiva);

        $danoJogador = 0;
        $danoOponente = 0;
        $logTurno = "";

        $calcularDano = function($atk, $def, $isTipo1, ?string $statusAtacante) {
            $atkEfetivo = intval($atk);
            if ($statusAtacante === 'queimado') {
                $atkEfetivo = max(1, (int) floor($atkEfetivo * 0.5));
            }
            $base = (($atkEfetivo / max(1, intval($def))) * 22) + 2;
            if ($isTipo1) {
                $base *= 1.5;
            }
            return max(5, intval($base * (rand(85, 100) / 100)));
        };

        $nomeJog = $pkJog['nome'] ?? 'Seu Pokémon';
        $nomeOpo = $pkOpo['nome'] ?? 'Oponente';

        $executarAtaqueJogador = function() use (
            &$danoJogador, &$hpOpoAtual, &$statusOponente, &$statusJogador, &$logTurno,
            $tipoAtaque, $pkJog, $pkOpo, $nomeJog, $nomeOpo, $calcularDano
        ) {
            if (!$this->podeAgirNoTurno($statusJogador, $nomeJog, $logTurno)) {
                return;
            }
            $isTipo1 = ($tipoAtaque === 'ataque1');
            $danoJogador = $calcularDano($pkJog['atk'] ?? 100, $pkOpo['def'] ?? 100, $isTipo1, $statusJogador);
            $hpOpoAtual = max(0, $hpOpoAtual - $danoJogador);
            $logTurno .= "{$nomeJog} atacou e causou {$danoJogador} de dano! ";

            if ($hpOpoAtual > 0) {
                $statusOponente = $this->sortearNovoStatus(
                    $statusOponente,
                    $tipoAtaque,
                    $pkJog['tipo1'] ?? 'Normal',
                    $nomeOpo,
                    $logTurno
                );
            }
        };

        $executarAtaqueOponente = function() use (
            &$danoOponente, &$hpJogAtual, &$statusJogador, &$statusOponente, &$logTurno,
            $pkJog, $pkOpo, $nomeJog, $nomeOpo, $calcularDano
        ) {
            if (!$this->podeAgirNoTurno($statusOponente, $nomeOpo, $logTurno)) {
                return;
            }
            $tipoAtqOpo = (rand(0, 1) == 1) ? 'ataque1' : 'ataque2';
            $isTipo1 = ($tipoAtqOpo === 'ataque1');
            $danoOponente = $calcularDano($pkOpo['atk'] ?? 100, $pkJog['def'] ?? 100, $isTipo1, $statusOponente);
            $hpJogAtual = max(0, $hpJogAtual - $danoOponente);
            $logTurno .= "{$nomeOpo} atacou e causou {$danoOponente} de dano! ";

            if ($hpJogAtual > 0) {
                $statusJogador = $this->sortearNovoStatus(
                    $statusJogador,
                    $tipoAtqOpo,
                    $pkOpo['tipo1'] ?? 'Normal',
                    $nomeJog,
                    $logTurno
                );
            }
        };

        if ($jogadorAtacaPrimeiro) {
            $executarAtaqueJogador();
            if ($hpOpoAtual > 0) {
                $executarAtaqueOponente();
            }
        } else {
            $executarAtaqueOponente();
            if ($hpJogAtual > 0) {
                $executarAtaqueJogador();
            }
        }

        if ($hpJogAtual > 0) {
            $danoStatusJog = $this->calcularDanoResidualStatus($statusJogador, $hpJogMax, $nomeJog, $logTurno);
            $hpJogAtual = max(0, $hpJogAtual - $danoStatusJog);
        }

        if ($hpOpoAtual > 0) {
            $danoStatusOpo = $this->calcularDanoResidualStatus($statusOponente, $hpOpoMax, $nomeOpo, $logTurno);
            $hpOpoAtual = max(0, $hpOpoAtual - $danoStatusOpo);
        }

        $_SESSION['turno_batalha'] = ($_SESSION['turno_batalha'] ?? 0) + 1;
        $numeroTurno = $_SESSION['turno_batalha'];

        if ($idSlotAtivo) {
            try {
                $stmtSlot = $this->db->prepare("UPDATE slot SET hp = ?, status = ? WHERE idSlot = ? AND idUsuario = ?");
                $stmtSlot->execute([$hpJogAtual, $statusJogador, $idSlotAtivo, $_SESSION['idUsuario']]);
            } catch (PDOException $e) {
                error_log("Erro ao persistir HP/Status do slot: " . $e->getMessage());
            }
        }

        $vitoria = ($hpOpoAtual <= 0);
        $derrota = ($hpJogAtual <= 0);

        if ($vitoria) {
            $stmt = $this->db->prepare("UPDATE usuario SET dinheiro = dinheiro + 1500, wins = wins + 1 WHERE idUsuario = ?");
            $stmt->execute([$_SESSION['idUsuario']]);
            $logTurno .= "Você derrotou o pokémon adversário e ganhou 1.500PkD$!";
            $this->CriarLogTurno($numeroTurno, $danoJogador, $danoOponente, $hpJogAtual, $hpOpoAtual);
            $this->CriarLogBatalha("VITÓRIA", $pkJog, $pkOpo);
            unset($_SESSION['turno_batalha']);
        } elseif ($derrota) {
            $stmt = $this->db->prepare("UPDATE usuario SET loss = loss + 1 WHERE idUsuario = ?");
            $stmt->execute([$_SESSION['idUsuario']]);
            $logTurno .= "Seu pokémon desmaiou!";
            $this->CriarLogTurno($numeroTurno, $danoJogador, $danoOponente, $hpJogAtual, $hpOpoAtual);
            $this->CriarLogBatalha("DERROTA", $pkJog, $pkOpo);
            unset($_SESSION['turno_batalha']);
        } else {
            $this->CriarLogTurno($numeroTurno, $danoJogador, $danoOponente, $hpJogAtual, $hpOpoAtual);
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
            'mensagem' => trim($logTurno)
        ]);
        exit;
    }

    public function usarItemBatalha() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['idUsuario'])) {
            http_response_code(401);
            echo json_encode(['sucesso' => false, 'mensagem' => 'Sessão expirada.']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $idSlot = isset($input['idSlot']) ? (int) $input['idSlot'] : 0;
        $idItem = isset($input['idItem']) ? (int) $input['idItem'] : 0;
        $idUsuario = (int) $_SESSION['idUsuario'];

        if ($idSlot <= 0 || $idItem <= 0) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Selecione um item válido.']);
            exit;
        }

        $timeModel = new Time($this->db);
        $inventarioModel = new Inventario($this->db);

        $slot = $timeModel->buscarSlotDoUsuario($idSlot, $idUsuario);
        $itemInv = $inventarioModel->listarPorUsuario2($idUsuario, $idItem);

        if (!$slot || !$itemInv || intval($itemInv['quantidade'] ?? 0) <= 0) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Item ou Pokémon inválido / sem estoque.']);
            exit;
        }

        $hpAtual = intval($slot['hp']);
        $hpMax = max(1, intval($slot['hpMax']));
        if ($hpAtual <= 0) {
            echo json_encode(['sucesso' => false, 'mensagem' => "{$slot['nome']} está desmaiado!"]);
            exit;
        }

        $nomeItem = $itemInv['nome'];
        $statusAtual = $this->normalizarStatus($slot['status'] ?? null);
        $novoHp = $hpAtual;
        $novoStatus = $statusAtual;

        $mapaCura = [
            'Antidote'      => ['envenenado'],
            'Paralyze Heal' => ['paralisado'],
            'Burn Heal'     => ['queimado'],
            'Ice Heal'      => ['congelado'],
            'Awakening'     => ['dormindo'],
            'Full Heal'     => ['envenenado', 'paralisado', 'queimado', 'congelado', 'dormindo'],
            'Full Restore'  => ['envenenado', 'paralisado', 'queimado', 'congelado', 'dormindo'],
        ];

        if (!isset($mapaCura[$nomeItem])) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Esse item não pode ser usado para curar status aqui.']);
            exit;
        }

        if ($nomeItem === 'Full Restore') {
            $precisaCuraStatus = ($statusAtual !== null && in_array($statusAtual, $mapaCura[$nomeItem], true));
            $precisaCuraHp = ($hpAtual < $hpMax);
            if (!$precisaCuraStatus && !$precisaCuraHp) {
                echo json_encode(['sucesso' => false, 'mensagem' => "{$slot['nome']} já está com HP cheio e sem status!"]);
                exit;
            }
            $novoHp = $hpMax;
            if ($precisaCuraStatus) {
                $novoStatus = null;
            }
        } else {
            if ($statusAtual === null || !in_array($statusAtual, $mapaCura[$nomeItem], true)) {
                echo json_encode(['sucesso' => false, 'mensagem' => "{$nomeItem} não tem efeito no status atual de {$slot['nome']}."]);
                exit;
            }
            $novoStatus = null;
        }

        $stmt = $this->db->prepare("UPDATE slot SET hp = ?, status = ? WHERE idSlot = ? AND idUsuario = ?");
        $stmt->execute([$novoHp, $novoStatus, $idSlot, $idUsuario]);
        $inventarioModel->consumirItem($idUsuario, $idItem);

        $novaQuantidade = max(0, intval($itemInv['quantidade']) - 1);

        echo json_encode([
            'sucesso' => true,
            'hpJogador' => $novoHp,
            'statusJogador' => $novoStatus,
            'idItem' => $idItem,
            'quantidadeRestante' => $novaQuantidade,
            'mensagem' => "{$nomeItem} usado em {$slot['nome']}!"
        ]);
        exit;
    }

    public function CriarLogBatalha($resultadoBatalha, $pkJog, $pkOpo) {
        $diretorioLog = __DIR__ . '/../logs/logs_batalha/';
        if (!is_dir($diretorioLog)) {
            mkdir($diretorioLog, 0777, true);
        }

        $nomeUsuario = $_SESSION['nomeUsuario'] ?? 'Jogador';
        $nomeJogadorPk = $pkJog['nome'] ?? 'Pokémon';
        $nomeOponentePk = $pkOpo['nome'] ?? 'Oponente';
        $idTime = !empty($pkJog['idTimes']) ? (int)$pkJog['idTimes'] : null;

        $arquivoLog = $diretorioLog . 'logBatalha.txt';
        $tz = new DateTimeZone('America/Sao_Paulo');
        $dataHora = (new DateTime('now', $tz))->format('Y-m-d H:i:s');
        $entradaLog = "[$dataHora] - LOG DE BATALHA DE $nomeUsuario UTILIZANDO {$nomeJogadorPk} = $resultadoBatalha CONTRA {$nomeOponentePk} OPONENTE" . PHP_EOL;

        file_put_contents($arquivoLog, $entradaLog, FILE_APPEND);

        try {
            $stmt = $this->db->prepare("
                INSERT INTO logBatalha (idUser, pokeOp, idTime, resultado, textoLog)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $_SESSION['idUsuario'],
                $nomeOponentePk,
                $idTime,
                $resultadoBatalha,
                trim($entradaLog)
            ]);
        } catch (PDOException $e) {
            error_log("Erro ao inserir logBatalha no banco: " . $e->getMessage());
        }
    }

    public function CriarLogTurno($numeroTurno, $danoJogador, $danoOponente, $hpJogAtual, $hpOpoAtual) {
        $nomeUsuario = $_SESSION['nomeUsuario'] ?? 'Jogador';
        $diretorioLog = __DIR__ . '/../logs/logs_batalha/';

        $arquivoLog = $diretorioLog . 'logBatalha.txt';
        $tz = new DateTimeZone('America/Sao_Paulo');
        $dataHora = (new DateTime('now', $tz))->format('Y-m-d H:i:s');
        $entradaLog = "[$dataHora] - NO [TURNO {$numeroTurno}] $nomeUsuario CAUSOU " . $danoJogador . " DE DANO (VIDA ATUAL " . $hpJogAtual . ") - OPONENTE CAUSOU " . $danoOponente . " DE DANO (VIDA ATUAL " . $hpOpoAtual . ")" . PHP_EOL;

        file_put_contents($arquivoLog, $entradaLog, FILE_APPEND);
    }
}