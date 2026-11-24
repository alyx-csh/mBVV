<?php //criação da exceção para caso o usário tente batalhar e não tenha nenhum pokémon vivo naquele time
class SemPokVivoException extends Exception {
    public function __construct($message = "Você precisa de ao menos um pokémon vivo para batalhar", $code = 403, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}