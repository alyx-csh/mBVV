<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Selecionar Time - MonstroBolso</title>
    <style>
        body {
            background-image: url("imagens/fundo.png");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            font-family: Helvetica, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .container {
            width: 70vw;
            margin: auto;
            background: rgba(245, 245, 245, 0.95);
            border: 4px double #3B4CCA;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.4);
        }
        h1 {
            color: #FFDE00;
            -webkit-text-stroke: 1.5px #3B4CCA;
            text-align: center;
            font-size: 2.2em;
            margin-top: 0;
        }
        .grid-times {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .card-time {
            background: #ffffff;
            border: 3px solid #3B4CCA;
            border-radius: 12px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .card-time:hover {
            transform: translateY(-4px);
        }
        .card-time h3 {
            color: #3B4CCA;
            margin-top: 0;
            border-bottom: 2px solid #eee;
            padding-bottom: 8px;
        }
        .pokemons-preview {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 8px;
            margin: 15px 0;
        }
        .pk-item {
            background: #f1f2f6;
            border-radius: 8px;
            padding: 5px;
            width: 75px;
            font-size: 12px;
            font-weight: bold;
            color: #2f3542;
        }
        .pk-item img {
            width: 60px;
            height: 60px;
            display: block;
            margin: auto;
            image-rendering: pixelated;
        }
        .btn-escolher {
            display: inline-block;
            background: #FFDE00;
            color: #3B4CCA;
            border: 2px solid #3B4CCA;
            padding: 10px 20px;
            font-weight: bold;
            text-decoration: none;
            border-radius: 8px;
            cursor: pointer;
            width: 80%;
        }
        .btn-escolher:hover {
            background: #fbc531;
        }
        .voltar {
            text-align: center;
            margin-top: 25px;
        }
        .voltar a {
            color: #3B4CCA;
            font-weight: bold;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Escolha o seu Time para Batalhar</h1>

        <div class="grid-times">
            <?php foreach ($timesCompletos as $time): ?>
                <div class="card-time">
                    <h3><?php echo htmlspecialchars($time['nome']); ?></h3>
                    
                    <div class="pokemons-preview">
                        <?php foreach ($time['pokemons'] as $pk): ?>
                            <div class="pk-item">
                                <img src="<?php echo htmlspecialchars($pk['img']); ?>" alt="<?php echo htmlspecialchars($pk['nome']); ?>">
                                <span><?php echo htmlspecialchars($pk['nome']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <a href="index.php?action=arena&idTime=<?php echo $time['idTimes']; ?>" class="btn-escolher">
                    Batalhar com este time
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="voltar">
            <a href="index.php?action=inicio">⬅ Voltar ao Menu Principal</a>
        </div>
    </div>
</body>
</html>