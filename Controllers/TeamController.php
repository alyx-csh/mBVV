<?php

class TeamController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function telaMontarTime() {
        require_once __DIR__ . '/../View/team/montarTime.php';
    }

    public function salvarTime() {
    $nomeTime = trim($_POST['nomeTime'] ?? '');
    $pokemonsEscolhidos = $_POST['pokemons'] ?? []; 

    // Valida se o nome do time foi preenchido
    if (empty($nomeTime)) {
        $_SESSION['erro_time'] = "Por favor, dê um nome à equipa!";
        header("Location: index.php?action=montar_time");
        exit;
    }

    $pokemonModel = new Pokemon($this->db);
    $slotsIds = [];

    // Limita no máximo a 6 pokémons
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
}