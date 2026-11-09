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
        @font-face {
            font-family: Pixelitta;
            src: url("fontes/Pixelitta-Regular.ttf");
        }
        body {
            background-image: url("imagens/pokemart.jpg");
            background-size: cover;
        }
        div.dinheiro{
            position: absolute;
            font-family: Helvetica;
            top: 13vh;
            left: 5%;
            width: 12%; 
            background-color: rgba(232, 232, 232, 0.95); 
            position: absolute; 
            margin: auto; 
            height: 10%; 
            border-radius: 2vh; 
            border: double;
        }

        h2.money{
            padding-left: 5%;
            margin-top: 3%;
        }

        div.itens{
            position: absolute;
            font-family: Helvetica;
            top: 13vh;
            left: 30%;
            width: 60%; 
            background-color: rgba(232, 232, 232, 0.97); 
            position: absolute; 
            margin: auto; 
            height: 80%; 
            border-radius: 1vh; 
            border: double;
            overflow: scroll;
        }
        h1.item{
            font-family: Pokemon; 
            color: #FFDE00;
            font-size: 5em;
            -webkit-text-stroke-color: #3B4CCA;
            -webkit-text-stroke-width: 0.15vh; /* cor da borda do texto */
            display: flex;
            text-align: center;
            margin-top: 1%;
            padding-left: 43%;
        }
        strong.pocao{
            position: absolute;
            top: 25%;
            left: 11%;
        }

        strong.revive{
            position: absolute;
            top: 39%;
            left: 11%;
        }
        
        strong.restore{
            position: absolute;
             top: 53%; /* aparentemente aumenta de 14 em 14 e o texto fica de boa, no mesmo lugar */
            left: 11%;
        }
        strong.pocao50{
            position: absolute;
            top: 67%;
            left: 11%;
        }
        strong.pocao200{
            position: absolute;
            top: 81%;
            left: 11%;
        }
        strong.max{
            position: absolute;
            top: 95%;
            left: 11%;
        }
        strong.antidote{
            position: absolute;
            top: 109%;
            left: 11%;
        }
        strong.burn{
            position: absolute;
            top: 123%;
            left: 11%;
        }
        strong.ice{
            position: absolute;
            top: 137%;
            left: 11%;
        }
        strong.paralyze{
            position: absolute;
            top: 151%;
            left: 11%;
        }
        strong.awakening{
            position: absolute;
            top: 165%;
            left: 11%;
        }
        strong.fRestore{
            position: absolute;
            top: 179%;
            left: 11%;
        }

        img{ 
            padding-left: 1%;
        }

        button.compra{
            position: absolute;
            top: 25%;
            left: 73%;
            width: 15%;
            height: 10%;
            border-radius: 1vh;
            border: double;
        }


    </style>
</head>
<body>
    <div class="dinheiro"> <h2 class="money">Dinheiro: <br> <p class="dindin"> $$$$<!-- pegar o valor do banco de dados aqui --></p> </h2> </div>  <!--Div Dinheiro -->

     <div class="itens"> <!--Div que mostra os itens -->
        <h1 class="item">Lojinha</h1> <br> 
        <tr>
            <td> <!-- item 1 -->
                <img src="imagens/itens/item1.png" alt="poção que cura 20HP" height="100px" width="100px"> <!-- foto do item -->
                <strong class="pocao"> Uma poção que cura 20 pontos de vida do pokémon </strong> <!-- legenda do item -->
                <button class="compra">Comprar</button> 
            </td> <br>

            <td> <!-- item 2 -->
                <img src="imagens/itens/item2.png" alt="revive" height="100px" width="100px"> 
                <strong class="revive"> Revive seu pokémon com aproximadamente 50% da vida total dele </strong> 
            </td> <br>

            <td> <!-- item 3 -->
                <img src="imagens/itens/item3.png" alt="restaura toda a vida do pokemon e cura seys status" height="100px" width="100px"> 
                <strong class="restore"> Poção que restaura toda vida do pokémon e cura todos seus status </strong>
            </td> <br>

            <td> <!-- item 4 -->
                <img src="imagens/itens/item4.png" alt="poção que cura 50HP" height="100px" width="100px"> <!-- foto do item -->
                <strong class="pocao50"> Uma poção que cura 50 pontos de vida do pokémon </strong> <!-- legenda do item -->
            </td> <br>

            <td> <!-- item 5 -->
                <img src="imagens/itens/item5.png" alt="poção que cura 200HP" height="100px" width="100px"> <!-- foto do item -->
                <strong class="pocao200"> Uma poção que cura 200 pontos de vida do pokémon </strong> <!-- legenda do item -->
            </td> <br>

            <td> <!-- item 6 -->
                <img src="imagens/itens/item6.png" alt="poção que cura toda a vida do pokémon" height="100px" width="100px"> <!-- foto do item -->
                <strong class="max"> Uma poção que restaura toda a vida do pokémon </strong> <!-- legenda do item -->
            </td> <br>

            <td> <!-- item 7 -->
                <img src="imagens/itens/item7.png" alt="spray antídoto" height="100px" width="100px"> <!-- foto do item -->
                <strong class="antidote"> Spray que cura envenenamento </strong> <!-- legenda do item -->
            </td> <br>

            <td> <!-- item 8 -->
                <img src="imagens/itens/item8.png" alt="spray anti-queimadura" height="100px" width="100px"> <!-- foto do item -->
                <strong class="burn"> Spray que cura queimaduras </strong> <!-- legenda do item -->
            </td> <br>

            <td> <!-- item 9 -->
                <img src="imagens/itens/item9.png" alt="spray anti-gelo" height="100px" width="100px"> <!-- foto do item -->
                <strong class="ice"> Spray que cura congelamento </strong> <!-- legenda do item -->
            </td> <br>

            <td> <!-- item 10 -->
                <img src="imagens/itens/item10.png" alt="spray anti-paralisia" height="100px" width="100px"> <!-- foto do item -->
                <strong class="paralyze"> Spray que cura paralisia </strong> <!-- legenda do item -->
            </td> <br>

            <td> <!-- item 11 -->
                <img src="imagens/itens/item11.png" alt="spray anti-sono" height="100px" width="100px"> <!-- foto do item -->
                <strong class="awakening"> Spray que acorda o pokémon </strong> <!-- legenda do item -->
            </td> <br>

            <td> <!-- item 12 -->
                <img src="imagens/itens/item12.png" alt="spray anti-status" height="100px" width="100px"> <!-- foto do item -->
                <strong class="fRestore"> Spray que cura qualquer status do pokémon </strong> <!-- legenda do item -->
            </td> <br> <!-- fazer o item 13, que é o item que faz o pokémon evoluir -->
        </tr>
    </div>
</body>
</html>