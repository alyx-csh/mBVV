<?php

class TeamController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function index() {
        require_once __DIR__ . '/../View/team/menuTimes.php';
    }

    public function telaMontarTime() {
        require_once __DIR__ . '/../View/team/montarTime.php';
    }

    public function salvarTime() {
    $nomeTime = trim($_POST['nomeTime'] ?? '');
    $pokemonsEscolhidos = $_POST['pokemons'] ?? []; 

    // Valida se o nome do time foi preenchido
    if (empty($nomeTime)) {
        $_SESSION['erro_time'] = "Dê um nome ao seu time antes de continuar";
        header("Location: index.php?action=montar_time");
        exit;
    }

    $pokemonModel = new Pokemon($this->db);
    $slotsIds = [];

    // limita no máximo a 6 pokémons
    $pokemonsEscolhidos = array_slice($pokemonsEscolhidos, 0, 6);

    foreach ($pokemonsEscolhidos as $termo) {
    $termoLimpo = trim($termo);
    if (empty($termoLimpo)) continue;

    $dadosApi = PokeApiService::buscarPokemonDaApi($termoLimpo);

        if ($dadosApi !== null) {
            // Passamos o idUsuario logado (obtido da sessão) para associar o slot ao utilizador
            $idUsuarioLogado = $_SESSION['idUsuario'];
            
            $idSlot = $pokemonModel->criarSlotInstancia($dadosApi, $idUsuarioLogado);
            $slotsIds[] = $idSlot;
        }
    }
    if (empty($slotsIds)) {
        $_SESSION['erro_time'] = "Erro: Nenhum dos Pokémon informados é válido ou existe na PokéAPI! Verifique os nomes.";
        header("Location: index.php?action=montar_time");
        exit;
    }
    $timeModel = new Time($this->db);
    $idUsuarioLogado = $_SESSION['idUsuario'];
    $timeModel->criarTimeComSlots($idUsuarioLogado, $nomeTime, $slotsIds);
    
    $_SESSION['sucesso_time'] = "Equipa montada e guardada com sucesso! (Pokémon inválidos foram ignorados).";
    header("Location: index.php?action=montar_time");
    exit;
    }

    public function verTimes() {
        $idUsuario = $_SESSION['idUsuario'];

        $timeModel = new Time($this->db);
        $inventarioModel = new Inventario($this->db);

        $times = $timeModel->buscarTimesCompletos($idUsuario);
        $inventario = $inventarioModel->listarPorUsuario($idUsuario);

        require_once __DIR__ . '/../View/team/meusTimes.php';
    }

    public function usarItem() {
        $idUsuario = $_SESSION['idUsuario'];
        $idSlot = $_POST['idSlot'] ?? null;
        $idItem = $_POST['idItem'] ?? null;

        if (!$idSlot || !$idItem) {
            $_SESSION['erro_uso_item'] = "Selecione um item válido.";
            header("Location: index.php?action=meus_times");
            exit;
        }

        $timeModel = new Time($this->db);
        $inventarioModel = new Inventario($this->db);

        $slot = $timeModel->buscarSlotDoUsuario($idSlot, $idUsuario);
        $itemInv = $inventarioModel->listarPorUsuario2($idUsuario, $idItem);

        if (!$slot || !$itemInv) {
            $_SESSION['erro_uso_item'] = "Pokémon ou item não encontrado no inventário.";
            header("Location: index.php?action=meus_times");
            exit;
        }

        $nomeItem = $itemInv['nome'];
        $hpAtual = intval($slot['hp']);
        $hpMax = intval($slot['hpMax']);
        $statusAtual = trim($slot['status'] ?? '');
        $statusLower = mb_strtolower($statusAtual);

        // 1. REVIVE
        if ($nomeItem === 'Revive') {
            if ($hpAtual > 0) {
                $_SESSION['erro_uso_item'] = "{$slot['nome']} não está desmaiado!";
                header("Location: index.php?action=meus_times");
                exit;
            }
            $novoHp = max(1, (int) floor($hpMax / 2));
            $stmt = $this->db->prepare("UPDATE slot SET hp = ?, status = NULL WHERE idSlot = ?");
            $stmt->execute([$novoHp, $idSlot]);
            $inventarioModel->consumirItem($idUsuario, $idItem);

            $_SESSION['sucesso_uso_item'] = "{$slot['nome']} foi revivido com {$novoHp} HP!";
            header("Location: index.php?action=meus_times");
            exit;
        }

        // 2. PEDRA DE EVOLUÇÃO
        if ($nomeItem === 'Pedra de evolução') {
            if ($hpAtual <= 0) {
                $_SESSION['erro_uso_item'] = "Reviva {$slot['nome']} antes de evoluí-lo!";
                header("Location: index.php?action=meus_times");
                exit;
            }

            $dadosEvolucao = PokeApiService::buscarProximaEvolucao($slot['nome']);
            if (!$dadosEvolucao) {
                $_SESSION['erro_uso_item'] = "{$slot['nome']} não pode mais evoluir!";
                header("Location: index.php?action=meus_times");
                exit;
            }

            $pokemonModel = new Pokemon($this->db);
            $novoIdPokemon = $pokemonModel->garantirPokemonBase($dadosEvolucao);
            $pkEvoluido = new PokemonEntity($dadosEvolucao);

            $stmt = $this->db->prepare("
                UPDATE slot 
                SET idPokemon = ?, nome = ?, tipo1 = ?, tipo2 = ?, hp = ?, atk = ?, def = ?, spA = ?, spD = ?, spe = ?, ataque1 = ?, img = ?
                WHERE idSlot = ?
            ");
            $stmt->execute([
                $novoIdPokemon,
                $pkEvoluido->getNome(),
                $pkEvoluido->getTipo1(),
                $pkEvoluido->getTipo2(),
                $pkEvoluido->getHp(),
                $pkEvoluido->getAtk(),
                $pkEvoluido->getDef(),
                $pkEvoluido->getSpA(),
                $pkEvoluido->getSpD(),
                $pkEvoluido->getSpe(),
                $pkEvoluido->getAtaque1(),
                $pkEvoluido->getImg(),
                $idSlot
            ]);

            $inventarioModel->consumirItem($idUsuario, $idItem);
            $_SESSION['sucesso_uso_item'] = "Parabéns! seu {$slot['nome']} evoluiu para {$pkEvoluido->getNome()}!";
            header("Location: index.php?action=meus_times");
            exit;
        }

        // demais itens exigem que o Pokémon esteja vivo, logo o if abaixo
        if ($hpAtual <= 0) {
            $_SESSION['erro_uso_item'] = "{$slot['nome']} está desmaiado! Use um Revive primeiro.";
            header("Location: index.php?action=meus_times");
            exit;
        }

        // 3. POÇÕES DE CURA DE HP
        $curasHp = [
            'Potion' => 20,
            'Super Potion' => 50,
            'Hyper Potion' => 200,
            'Max Potion' => $hpMax
        ];

        if (isset($curasHp[$nomeItem])) {
            if ($hpAtual >= $hpMax) {
                $_SESSION['erro_uso_item'] = "O HP de {$slot['nome']} já está cheio!";
                header("Location: index.php?action=meus_times");
                exit;
            }
            $novoHp = min($hpMax, $hpAtual + $curasHp[$nomeItem]);
            $stmt = $this->db->prepare("UPDATE slot SET hp = ? WHERE idSlot = ?");
            $stmt->execute([$novoHp, $idSlot]);
            $inventarioModel->consumirItem($idUsuario, $idItem);

            $_SESSION['sucesso_uso_item'] = "{$slot['nome']} recuperou HP ({$novoHp}/{$hpMax})!";
            header("Location: index.php?action=meus_times");
            exit;
        }

        // 4. FULL RESTORE (HP + Status)
        if ($nomeItem === 'Full Restore') {
            if ($hpAtual >= $hpMax && empty($statusAtual)) {
                $_SESSION['erro_uso_item'] = "{$slot['nome']} já está com HP cheio e sem condições de status!";
                header("Location: index.php?action=meus_times");
                exit;
            }
            $stmt = $this->db->prepare("UPDATE slot SET hp = ?, status = NULL WHERE idSlot = ?");
            $stmt->execute([$hpMax, $idSlot]);
            $inventarioModel->consumirItem($idUsuario, $idItem);

            $_SESSION['sucesso_uso_item'] = "{$slot['nome']} foi completamente restaurado!";
            header("Location: index.php?action=meus_times");
            exit;
        }

        // 5. ITENS DE CURA DE STATUS
        $mapaStatus = [
            'Antidote'      => ['poison', 'poisoned', 'envenenado', 'psn'],
            'Paralyze Heal' => ['paralysis', 'paralyzed', 'paralisado', 'par'],
            'Burn Heal'     => ['burn', 'burned', 'queimado', 'brn'],
            'Ice Heal'      => ['freeze', 'frozen', 'congelado', 'frz'],
            'Awakening'     => ['sleep', 'asleep', 'dormindo', 'slp']
        ];

        if ($nomeItem === 'Full Heal') {
            if (empty($statusAtual)) {
                $_SESSION['erro_uso_item'] = "{$slot['nome']} não possui nenhuma condição de status!";
                header("Location: index.php?action=meus_times");
                exit;
            }
            $stmt = $this->db->prepare("UPDATE slot SET status = NULL WHERE idSlot = ?");
            $stmt->execute([$idSlot]);
            $inventarioModel->consumirItem($idUsuario, $idItem);

            $_SESSION['sucesso_uso_item'] = "Status de {$slot['nome']} foi curado!";
            header("Location: index.php?action=meus_times");
            exit;
        }

        if (isset($mapaStatus[$nomeItem])) {
            if (empty($statusAtual) || !in_array($statusLower, $mapaStatus[$nomeItem])) {
                $_SESSION['erro_uso_item'] = "{$nomeItem} não tem efeito em {$slot['nome']}!";
                header("Location: index.php?action=meus_times");
                exit;
            }
            $stmt = $this->db->prepare("UPDATE slot SET status = NULL WHERE idSlot = ?");
            $stmt->execute([$idSlot]);
            $inventarioModel->consumirItem($idUsuario, $idItem);

            $_SESSION['sucesso_uso_item'] = "{$nomeItem} curado com sucesso em {$slot['nome']}!";
            header("Location: index.php?action=meus_times");
            exit;
        }

        $_SESSION['erro_uso_item'] = "Item não reconhecido.";
        header("Location: index.php?action=meus_times");
        exit;
    }
}