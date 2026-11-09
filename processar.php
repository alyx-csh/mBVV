<?php

//alterando o nome da sessão;
session_name('MONSTROBOLSO');
//início da sessão
session_start();

//conexão com o banco de dados por PDO
    try {
        $dsn = "mysql:host=localhost;dbname=monstroBolso;charset=utf8";
        $usernamePDO = 'usernameBanco';
        $senhaPDO = '';

        $pdo = new PDO($dsn, $usernamePDO, $senhaPDO);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        header("Location: loginInicio.php");
    }

//convertendo os POSTS em SESSIONS
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $_SESSION['username'] = $_POST['username'];
    $_SESSION['senha'] = $_POST['senha'];
    header('Location: menu.php');
} else {
    header("Location: loginInicio.php");
}

?>