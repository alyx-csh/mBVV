<?php
class PokeApiService {
    public static function buscarPokemonDaApi($nomeOuId) { //busca um pokémon da api pelo nome inserido no form do montar time
        $url = "https://pokeapi.co/api/v2/pokemon/" . strtolower(trim($nomeOuId));
        $opts = [
            "http" => [
                "method" => "GET",
                "header" => "User-Agent: Mozilla/5.0"
            ]
        ];
        $context = stream_context_create($opts);
        $resposta = @file_get_contents($url, false, $context);

        if ($resposta === FALSE) {
            return null;
        }
        return json_decode($resposta, true);
    }

    // método que buscar um ataque aleatório do mesmo tipo que o tipo1 do Pokémon escolhido
    public static function buscarAtaquePorTipo($tipo) {
        $url = "https://pokeapi.co/api/v2/type/" . strtolower(trim($tipo));
        $opts = ["http" => ["method" => "GET", "header" => "User-Agent: Mozilla/5.0"]];
        $context = stream_context_create($opts);
        $resposta = @file_get_contents($url, false, $context);

        if ($resposta !== FALSE) {
            $dados = json_decode($resposta, true);
            if (!empty($dados['moves'])) {
                $indiceAleatorio = array_rand($dados['moves']);
                return ucfirst($dados['moves'][$indiceAleatorio]['name']);
            }
        }
        // caso a API falhe
        return ucfirst($tipo) . " Strike";
    }

    public static function buscarProximaEvolucao($nomePokemon) { //método para evoluir o pokémon
        $nomeBusca = strtolower(trim($nomePokemon));
        $urlSpecies = "https://pokeapi.co/api/v2/pokemon-species/" . urlencode($nomeBusca);
        $opts = ["http" => ["method" => "GET", "header" => "User-Agent: Mozilla/5.0"]];
        $context = stream_context_create($opts);
        $respSpecies = @file_get_contents($urlSpecies, false, $context);
        if ($respSpecies === FALSE) return null;
        $dadosSpecies = json_decode($respSpecies, true);
        $urlChain = $dadosSpecies['evolution_chain']['url'] ?? null;
        if (!$urlChain) return null;
        $respChain = @file_get_contents($urlChain, false, $context);
        if ($respChain === FALSE) return null;
        $dadosChain = json_decode($respChain, true);
        $chain = $dadosChain['chain'] ?? null;
        // percorre pela árvore de evolução da API de forma recursiva
        $encontrarProximo = function($no) use (&$encontrarProximo, $nomeBusca) {
            if (!isset($no['species']['name'])) return null;
            if (strtolower($no['species']['name']) === $nomeBusca) {
                return $no['evolves_to'][0]['species']['name'] ?? false;
            }
            foreach ($no['evolves_to'] as $proximoNo) {
                $resultado = $encontrarProximo($proximoNo);
                if ($resultado !== null) return $resultado;
            }
            return null;
        };
        $proximoNome = $encontrarProximo($chain);
        if (!$proximoNome) {
            return null; // já está na última evolução/não evolui
        }
        return self::buscarPokemonDaApi($proximoNome);
    }
}