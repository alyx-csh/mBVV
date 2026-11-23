<?php
class Usuario {
    private $db;
    public function __construct($db) {
        $this->db = $db;
    }

    public function buscarPorId($idUsuario) {
        $stmt = $this->db->prepare("SELECT * FROM usuario WHERE idUsuario = ?");
        $stmt->execute([$idUsuario]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function buscarPorNome($nome) {
        $stmt = $this->db->prepare("SELECT * FROM usuario WHERE nome = ?");
        $stmt->execute([$nome]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

<<<<<<< HEAD
    public function cadastrar($nome, $senha) {
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
    $pokemonId = random_int(1, 1025);
    $img = "https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/{$pokemonId}.png";
    $stmt = $this->db->prepare("INSERT INTO usuario (nome, senha, dinheiro, img) VALUES (?, ?, 5000, ?)");
    return $stmt->execute([$nome, $senhaHash, $img]);
=======
    public function cadastrar($nome, $senha) { //usada para fazer o login do novo usuário
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        $pokemonCount = 1025;

        $numPokemon = rand(1, $pokemonCount);
        $img = $this->getPokemon($numPokemon);

        function getPokemon(num) {
            let url = "https://pokeapi.co/api/v2/pokemon/" + num.toString;

            let res = await fetch(url);
            let pokemon = await res.json();
            // console.log(pokemon);
            let pokemonImg = pokemon["sprites"]["front_default"];

        }
        $stmt = $this->db->prepare("INSERT INTO usuario (nome, senha, dinheiro, img) VALUES (?, ?, 5000, ?)");
        return $stmt->execute([$nome, $senhaHash, $img]);
>>>>>>> 707c9f1d8e3bde3d135626f97298ffd6b67c0c2c
    }

    public function atualizarImagem($idUsuario, $img) {
        $stmt = $this->db->prepare("UPDATE usuario SET img = ? WHERE idUsuario = ?");
        return $stmt->execute([$img, $idUsuario]);
    }

    public function atualizarDinheiro($idUsuario, $novoDinheiro) { //atualiza o dinheiro (basicamente um set só q em php)
        $stmt = $this->db->prepare("UPDATE usuario SET dinheiro = ? WHERE idUsuario = ?");
        return $stmt->execute([$novoDinheiro, $idUsuario]);
    }
}