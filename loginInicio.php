<?php

 ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- estilo da página -->
    <style>
        @font-face {
            font-family: Pokemon;
            src: url("fontes/PocketMonk-15ze.ttf");
        }

        body{ background-image: url("imagens/fundo.png"); background-size: 100%;}
        div{ width:27%; background-color: rgba(232, 232, 232, 0.85); display: flex; justify-content: center; align-items: center; position: absolute; top: 0; bottom: 0; left: 0.25%; right: 0; margin: auto; height: 72%; border-radius: 3vh; border: double;}
        #titulo{ font-family: Pokemon; color: #FFDE00; -webkit-text-stroke: 0.15vh #3B4CCA; font-size: 8vh; position: absolute; top: -4%}
        label{ font-family: Helvetica; font-weight: bold; font-size: 2.7vh; position: absolute; left: 4vh;}
        input{ resize: horizontal; width: 46vh; position: absolute; height: 3.6vh; font-size: 2vh; padding: 0.7vh;}
        #senha{ top: 32vh;}
        #username{ top: 17vh;}
        #uninput{ left: 3.6vh; top: 20vh;}
        #senhainput{ left: 3.6vh; top: 35vh;}
        #submit{ left: 3.6vh; width: 47.9vh; top: 50vh; border-radius: 1vh;}
        #submit:hover{ background-color: rgba(194, 194, 194, 0.91); transform: scale(1.01) translate(1.1);}

    </style>
</head>
<body>
        <!-- div do formulário -->
        <div><h1 id=titulo>MonstroBolso</h1>
             <form action="processar.php" method="post" enctype='multipart/form-data'>
                    <label for="username" id="username">Nome de Usuário<br></label>
                    <input type="text" name="username" id="uninput"><br>
                    <label for="senha" id="senha">Senha<br></label>
                    <input type="password" name="senha" id="senhainput"><br>
                    <input type="submit" id="submit" value="Enviar" />
             </form>
        </div>
</body>
</html>