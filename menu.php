<?php

//alterando o nome da sessão
session_name('MONSTROBOLSO');
//iniciando sessão
session_start();

//verificando se o username está settado corretamente e acessando-o
if (isset($_SESSION['username'])) {
    echo $_SESSION['username'];
} else {
    echo "ERRO NO USERNAME!";
}
//verificando se a senha está settada corretamente a acessando-a
if (isset($_SESSION['senha'])) {
    echo $_SESSION['senha'];
} else {
    echo "ERRO NOA SENHA!";
}
?>