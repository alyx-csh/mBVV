<?php
class Pokemon {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function garantirPokemonBase($dadosApi) { //pega os dados da API e coloca pra tabela SQL pokemon
        $nome = ucfirst($dadosApi['name']);
        $stmt = $this->db->prepare("SELECT idPokemon FROM pokemon WHERE nome = ?");
        $stmt->execute([$nome]);
        $pok = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($pok) {
            return $pok['idPokemon'];
        }

        $pokemonObj = new PokemonEntity($dadosApi);
        
        $stmtIns = $this->db->prepare("
            INSERT INTO pokemon (nome, tipo1, tipo2, hp, atk, def, spA, spD, spe, img) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmtIns->execute([
            $pokemonObj->getNome(),
            $pokemonObj->getTipo1(),
            $pokemonObj->getTipo2(),
            $pokemonObj->getHp(),
            $pokemonObj->getAtk(),
            $pokemonObj->getDef(),
            $pokemonObj->getSpA(),
            $pokemonObj->getSpD(),
            $pokemonObj->getSpe(),
            $pokemonObj->getImg()
        ]);

        return $this->db->lastInsertId();
    }

    public function criarSlotInstancia($dadosApi, $idUsuario) { //cria o pokémon na tabela slot
        $idPokemon = $this->garantirPokemonBase($dadosApi);
        $pokemonObj = new PokemonEntity($dadosApi);

        $stmt = $this->db->prepare("
            INSERT INTO slot (idPokemon, idUsuario, nome, tipo1, tipo2, hp, atk, def, spA, spD, spe, ataque1, ataque2, img)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $idPokemon,
            $idUsuario,
            $pokemonObj->getNome(),
            $pokemonObj->getTipo1(),
            $pokemonObj->getTipo2(),
            $pokemonObj->getHp(),
            $pokemonObj->getAtk(),
            $pokemonObj->getDef(),
            $pokemonObj->getSpA(),
            $pokemonObj->getSpD(),
            $pokemonObj->getSpe(),
            $pokemonObj->getAtaque1(),
            $pokemonObj->getAtaque2(),
            $pokemonObj->getImg()
        ]);

        return $this->db->lastInsertId();
    }
}