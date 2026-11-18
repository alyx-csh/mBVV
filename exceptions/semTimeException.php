<?php
class SemTimeException extends Exception {
    public function __construct($message = "Você precisa de um time para batalhar", $code = 403, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}