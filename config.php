<?php

//início da sessão
session_start();

    try {
        $db = new PDO("mysql:host=localhost;dbname=monstroBolso;charset=utf8", "root", "");
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo "Erro na conexão com o banco de dados: " . $e->getMessage();
        exit;
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