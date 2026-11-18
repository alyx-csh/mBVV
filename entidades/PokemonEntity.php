<?php
class PokemonEntity {
    private $nome;
    private $tipo1;
    private $tipo2;
    private $hp;
    private $atk;
    private $def;
    private $spA;
    private $spD;
    private $spe;
    private $ataque1;
    private $ataque2;
    private $img;

    public function __construct($dadosApi) {
        $this->nome = ucfirst($dadosApi['name']);
        $this->tipo1 = ucfirst($dadosApi['types'][0]['type']['name']);
        $this->tipo2 = isset($dadosApi['types'][1]) ? ucfirst($dadosApi['types'][1]['type']['name']) : null;
        
        $calcStat = function($baseStat, $isHp = false) {
            if ($isHp) {
                return floor(((2 * $baseStat + 31) * 50) / 100) + 50 + 10;
            } else {
                return floor((floor(((2 * $baseStat + 31) * 50) / 100) + 5));
            }
        };

        $this->hp  = $calcStat($dadosApi['stats'][0]['base_stat'], true);
        $this->atk = $calcStat($dadosApi['stats'][1]['base_stat']);
        $this->def = $calcStat($dadosApi['stats'][2]['base_stat']);
        $this->spA = $calcStat($dadosApi['stats'][3]['base_stat']);
        $this->spD = $calcStat($dadosApi['stats'][4]['base_stat']);
        $this->spe = $calcStat($dadosApi['stats'][5]['base_stat']);

        // Atribuição dos ataques: Ataque 1 baseado no tipo1 do Pokémon via PokéAPI, Ataque 2 normal (Tackle)
        $this->ataque1 = PokeApiService::buscarAtaquePorTipo($this->tipo1);
        $this->ataque2 = "Tackle";

        $this->img = $dadosApi['sprites']['front_default'] ?? '';
    }

    public function getNome() { return $this->nome; }
    public function getTipo1() { return $this->tipo1; }
    public function getTipo2() { return $this->tipo2; }
    public function getHp() { return $this->hp; }
    public function getAtk() { return $this->atk; }
    public function getDef() { return $this->def; }
    public function getSpA() { return $this->spA; }
    public function getSpD() { return $this->spD; }
    public function getSpe() { return $this->spe; }
    public function getAtaque1() { return $this->ataque1; }
    public function getAtaque2() { return $this->ataque2; }
    public function getImg() { return $this->img; }
}