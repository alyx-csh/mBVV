<?php

//iniciando sessão
session_start(); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        @font-face {
            font-family: Pokemon;
            src: url("fontes/PocketMonk-15ze.ttf");
        }

        body{ background-image: url("imagens/fundo.png"); background-size: 100%;}
        div{ width:30%; background-color: rgba(232, 232, 232, 0.9); display: flex; justify-content: center; align-items: center; position: absolute; top: 0; bottom: 0; left: 0; right: 0; margin: auto; height: 93%; border-radius: 3vh; border: double;}
        #titulo{ font-family: Pokemon; color: #FFDE00;}
    </style>
</head>
<body>
        <div><h1 id=titulo>MonstroBolso</h1></div>
</body>
</html>