<?php
    try {
        $db = new PDO("mysql:host=localhost;dbname=mBVV;charset=utf8", "root", "");
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo "Erro na conexão com o banco de dados: " . $e->getMessage();
        exit;
    }
//GUILHERME TEM Q TIRAR ISSO SENÃO ELE SEMPRE LEVA PRO LOGIN ATÉ SE EU PEDIR PRA ABRIR A LOJINHA
//quase certeza que isso é em outro lugar também, deve ser em algo de MVC
//convertendo os POSTS em SESSIONS

//if($_SERVER['REQUEST_METHOD'] === 'POST'){
    //$_SESSION['username'] = $_POST['username'];
    //$_SESSION['senha'] = $_POST['senha'];
    //header('Location: menu.php');
//} else {
    //header("Location: loginInicio.php");
//}
