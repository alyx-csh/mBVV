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

        //pega os atributos base do pokémon e roda a função acima
        $this->hp  = $calcStat($dadosApi['stats'][0]['base_stat'], true);
        $this->atk = $calcStat($dadosApi['stats'][1]['base_stat']);
        $this->def = $calcStat($dadosApi['stats'][2]['base_stat']);
        $this->spA = $calcStat($dadosApi['stats'][3]['base_stat']);
        $this->spD = $calcStat($dadosApi['stats'][4]['base_stat']);
        $this->spe = $calcStat($dadosApi['stats'][5]['base_stat']);

        // faz o ataque 1 ser obrigatoriamente do tipo1 do pokémon e só deixa o ataque secundário como Tackle
        $this->ataque1 = PokeApiService::buscarAtaquePorTipo($this->tipo1);
        $this->ataque2 = "Tackle";
        //seta a img do pokémon como o sprite que a API dá
        $this->img = $dadosApi['sprites']['front_default'] ?? '';
    }
    //como todas os atributos são privados, precisa de um get public pra cada atributo
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