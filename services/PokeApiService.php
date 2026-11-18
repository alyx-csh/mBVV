<?php
class PokeApiService {
    public static function buscarPokemonDaApi($nomeOuId) {
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
            return null; // Pokémon não encontrado na API
        }

        return json_decode($resposta, true);
    }
}