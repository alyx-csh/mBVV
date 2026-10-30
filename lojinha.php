<?php
//iniciando sessão
session_start(); 
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lojinha</title>
    <style>
        @font-face {
            font-family: Pokemon;
            src: url("fontes/PocketMonk-15ze.ttf");
        }

        div.logo{
            position: absolute;
            top: -4vh;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;   /* cor do preenchimento do texto */
            font-family: Pokemon; 
            color: #FFDE00;
            font-size: 3em;
            -webkit-text-stroke-color: #3B4CCA;
            -webkit-text-stroke-width: 0.15vh; /* cor da borda do texto */
        }

        div.dinheiro{
            position: absolute;
            font-family: Helvetica;
            top: 13vh;
            left: 5%;
            width:13%; 
            background-color: rgba(232, 232, 232, 0.9); 
            position: absolute; 
            margin: auto; 
            height: 13%; 
            border-radius: 3vh; 
            border: double;
        }

        h2.money{
            padding-left: 5%;
        }

        div.itens{
            position: absolute;
            font-family: Helvetica;
            top: 15vh;
            left: 30%;
            width: 60%; 
            background-color: rgba(232, 232, 232, 0.9); 
            position: absolute; 
            margin: auto; 
            height: 80%; 
            border-radius: 1vh; 
            border: double;
            overflow: scroll;
        }
        
        h2.item{
            padding-left: 1%;
        }
        strong.pocao{
            position: absolute;
            top: 10%;
            left: 10%;
        }

        strong.revive{
            position: absolute;
            top: 24%;
            left: 10%;
        }
        
        strong.restore{
            position: absolute;
             top: 38%; /* aparentemente aumenta de 14% em 14% e o texto fica de boa, no mesmo lugar */
            left: 10%;
        }

        img{
            padding-left: 1%;
        }

    </style>
</head>
<body>
    <div class="logo"> <h1>Lojinha</h1> </div> <!-- Div Título -->

    <div class="dinheiro"> <h2 class="money">Dinheiro: <br> $$$$$ <span> <!-- pegar o valor do banco de dados aqui --> </span> </h2> </div>  <!--Div Dinheiro -->

     <div class="itens"> <!--Div que mostra os itens -->
        <h2 class="item">Itens</h2> <!-- quero fazer ser um negócio quadrado que vai pra baixo sem fazer a tela inteira mover -->
        <tr>
            <td> <img src="imagens\item1.png" alt="poção que cura 20HP" height="100px" width="100px"> <strong class="pocao"> Uma poção que cura 20 pontos de vida do pokémon </strong> </td> <br>
            <td> <img src="imagens/item2.png" alt="revive" height="100px" width="100px"> <strong class="revive"> Revive seu pokémon com aproximadamente 50% da vida total dele </strong> </td> <br>
            <td> <img src="imagens\item3.png" alt="poção que cura 20HP" height="100px" width="100px"> <strong class="restore"> Poção que restaura toda vida do pokémon e cura todos seus status </strong> </td> <br>
        </tr>
    </div>

</body>
</html>