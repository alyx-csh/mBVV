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
        div{ width:30%; background-color: rgba(232, 232, 232, 0.9); display: flex; justify-content: center; align-items: center; position: absolute; top: 0; bottom: 0; left: 0; right: 0; margin: auto; height: 85%; border-radius: 3vh; border: double;}
        #titulo{ font-family: Pokemon; color: #FFDE00; -webkit-text-stroke: 0.15vh #3B4CCA; font-size: 7vh; position: absolute; top: -1%}
        label{ font-family: Helvetica; font-weight: bold; font-size: 2.3vh;}
        #divN{background-color: rgba(4, 170, 109, 0); border: none; positon: absolute; top: 20%;}
        #divS{background-color: rgba(4, 170, 109, 0); border: none;}
    </style>
</head>
<body>
        <div><h1 id=titulo>MonstroBolso</h1>
             <form action="menu.php" method-"POST" enctype='multipart/form-data'>
                <div id="divN">
                    <label for="username">Nome de Usuário<br></label>
                    <input type="text" name="username" id="username">
                </div>
                <div id="divS">
                    <label for="senha">Senha</label>
                    <input type="password" name="senha" id="senha">
                </div>
             </form>
        </div>
</body>
</html>