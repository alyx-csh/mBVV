<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Times - MonstroBolso</title>
    <style>
        @font-face {
            font-family: Pokemon;
            src: url("fontes/PocketMonk-15ze.ttf");
        }
        body {
            background-image: url("imagens/menuTime.jpg");
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            font-family: Helvetica, sans-serif;
            margin: 0;
            padding-top: 12vh;
            text-align: center;
        }
        .hub-container {
            background: rgba(232, 232, 232, 0.95);
            width: 440px;
            margin: 0 auto;
            padding: 30px;
            border-radius: 15px;
            border: 4px double #3B4CCA;
            box-shadow: 0 6px 16px rgba(0,0,0,0.35);
        }
        h1 {
            font-family: Pokemon;
            color: #FFDE00;
            font-size: 3.2em;
            -webkit-text-stroke: 1.5px #3B4CCA;
            margin-top: 0;
            margin-bottom: 10px;
            letter-spacing: 2px;
        }
        p.subtitulo {
            color: #333;
            margin-bottom: 25px;
            font-weight: bold;
        }
        .btn-hub {
            display: block;
            width: 100%;
            padding: 16px;
            margin: 15px 0;
            background-color: #FFDE00;
            color: #000;
            font-weight: bold;
            font-size: 1.2em;
            text-decoration: none;
            border-radius: 10px;
            border: 3px solid #3B4CCA;
            box-sizing: border-box;
            transition: 0.2s;
        }
        .btn-hub:hover {
            background-color: #fbc531;
            transform: scale(1.04);
        }
        .btn-secundario {
            background-color: #3B4CCA;
            color: #FFDE00;
        }
        .btn-secundario:hover {
            background-color: #2a3699;
        }
        .link-voltar {
            display: inline-block;
            margin-top: 15px;
            color: #3B4CCA;
            font-weight: bold;
            text-decoration: none;
        }
        .link-voltar:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="hub-container">
        <h1>Central de Times</h1>
        <p class="subtitulo">Escolha uma opção</p>

        <a href="index.php?action=novo_time" class="btn-hub">
            Novo time
        </a>

        <a href="index.php?action=meus_times" class="btn-hub btn-secundario">
            Ver times (e usar Itens)
        </a>

        <a href="index.php?action=inicio" class="link-voltar">Voltar ao Menu Principal</a>
    </div>
</body>
</html>