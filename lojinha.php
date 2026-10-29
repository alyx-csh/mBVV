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

    </style>
</head>
<body>
    <div class="logo"> <h1>Lojinha</h1> </div> <!-- colocar cores diferentes, tipo como é na logo do pokemon mesmo -->

    <div class="dinheiro"> <h2 class="money">Dinheiro: <br> $$$$$ <span> <!-- pegar o valor do banco de dados aqui --> </span> </h2> </div>

    <div class="itens">
        <h2>Itens</h2> <!-- quero fazer ser um negócio quadrado que vai pra baixo sem fazer a tela inteira mover -->
        
    </div>
</form>
</section>
</body>
</html>