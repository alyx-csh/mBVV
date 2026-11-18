<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Times</title>
    <style>
        @font-face {
            font-family: Pokemon;
            src: url("fontes/PocketMonk-15ze.ttf");
        }
        body {
            background-image: url("imagens/meusTimes.webp");
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            font-family: Helvetica, sans-serif;
            margin: 0;
            padding: 25px;
        }
        .container {
            width: 82vw;
            max-width: 1150px;
            margin: 0 auto;
            background: rgba(238, 238, 238, 0.96);
            border: 4px double #3B4CCA;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 18px rgba(0,0,0,0.35);
        }
        h1 {
            font-family: Pokemon;
            color: #FFDE00;
            font-size: 3.3em;
            -webkit-text-stroke: 1.5px #3B4CCA;
            text-align: center;
            margin-top: 0;
            margin-bottom: 15px;
            letter-spacing: 2px;
        }
        .msg-sucesso {
            background: #d4edda; color: #155724; padding: 12px;
            border-radius: 8px; margin-bottom: 15px; text-align: center; font-weight: bold;
        }
        .msg-erro {
            background: #f8d7da; color: #721c24; padding: 12px;
            border-radius: 8px; margin-bottom: 15px; text-align: center; font-weight: bold;
        }
        .bloco-time {
            background: #ffffff;
            border: 3px solid #3B4CCA;
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 25px;
        }
        .bloco-time h2 {
            margin-top: 0;
            color: #3B4CCA;
            border-bottom: 2px solid #eee;
            padding-bottom: 8px;
        }
        .grid-pokemons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 15px;
        }
        .card-pk {
            background: #f8f9fa;
            border: 2px solid #dcdde1;
            border-radius: 10px;
            padding: 12px;
            text-align: center;
        }
        .card-pk.desmaiado {
            background: #f5e6e8;
            border-color: #e84118;
        }
        .card-pk img {
            width: 84px;
            height: 84px;
            image-rendering: pixelated;
        }
        .card-pk h3 {
            margin: 5px 0;
            color: #2f3640;
        }
        .badge-tipo {
            display: inline-block;
            background: #3B4CCA;
            color: #fff;
            font-size: 0.75em;
            padding: 2px 8px;
            border-radius: 12px;
            margin: 2px;
        }
        .status-tag {
            display: inline-block;
            margin-top: 6px;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 0.8em;
            font-weight: bold;
        }
        .status-ok { background: #dff9fb; color: #130f40; }
        .status-problema { background: #ff7979; color: #fff; }
        .barra-hp-fundo {
            width: 100%;
            height: 10px;
            background: #dcdde1;
            border-radius: 5px;
            overflow: hidden;
            margin: 6px 0;
        }
        .barra-hp-preenchida {
            height: 100%;
            background: #44bd32;
        }
        .form-item {
            margin-top: 10px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .form-item select {
            padding: 6px;
            border-radius: 6px;
            border: 1px solid #aaa;
            font-size: 0.9em;
        }
        .btn-usar {
            background: #FFDE00;
            color: #3B4CCA;
            border: 2px solid #3B4CCA;
            padding: 7px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }
        .btn-usar:hover { background: #fbc531; }
        .barra-topo {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        .btn-nav {
            color: #3B4CCA;
            font-weight: bold;
            text-decoration: none;
        }
        .btn-nav:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <div class="barra-topo">
            <a href="index.php?action=montar_time" class="btn-nav">Voltar para Central de Times</a>
            <a href="index.php?action=loja" class="btn-nav">Ir para Lojinha</a>
        </div>

        <h1>Meus Times</h1>

        <?php if(isset($_SESSION['sucesso_uso_item'])): ?>
            <div class="msg-sucesso">
                <?php echo $_SESSION['sucesso_uso_item']; unset($_SESSION['sucesso_uso_item']); ?>
            </div>
        <?php endif; ?>

        <?php if(isset($_SESSION['erro_uso_item'])): ?>
            <div class="msg-erro">
                <?php echo $_SESSION['erro_uso_item']; unset($_SESSION['erro_uso_item']); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($times)): ?>
            <p style="text-align: center; font-weight: bold;">
                Você ainda não montou nenhum time. <a href="index.php?action=novo_time">Clique aqui para criar um!</a>
            </p>
        <?php else: ?>
            <?php foreach ($times as $time): ?>
                <div class="bloco-time">
                    <h2>Equipe: <?php echo htmlspecialchars($time['nome']); ?></h2>

                    <div class="grid-pokemons">
                        <?php foreach ($time['pokemons'] as $pk): 
                            $hpAtual = intval($pk['hp']);
                            $hpMax = max(1, intval($pk['hpMax']));
                            $pctHp = min(100, max(0, ($hpAtual / $hpMax) * 100));
                            $desmaiado = ($hpAtual <= 0);
                            $statusTexto = $desmaiado ? 'Desmaiado' : (!empty($pk['status']) ? $pk['status'] : 'Saudável');
                        ?>
                            <div class="card-pk <?php echo $desmaiado ? 'desmaiado' : ''; ?>">
                                <img src="<?php echo htmlspecialchars($pk['img']); ?>" alt="<?php echo htmlspecialchars($pk['nome']); ?>">
                                <h3><?php echo htmlspecialchars($pk['nome']); ?></h3>

                                <div>
                                    <span class="badge-tipo"><?php echo htmlspecialchars($pk['tipo1']); ?></span>
                                    <?php if (!empty($pk['tipo2'])): ?>
                                        <span class="badge-tipo"><?php echo htmlspecialchars($pk['tipo2']); ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="barra-hp-fundo">
                                    <div class="barra-hp-preenchida" style="width: <?php echo $pctHp; ?>%;"></div>
                                </div>
                                <small><strong>HP:</strong> <?php echo $hpAtual; ?> / <?php echo $hpMax; ?></small><br>

                                <span class="status-tag <?php echo ($desmaiado || !empty($pk['status'])) ? 'status-problema' : 'status-ok'; ?>">
                                    Status: <?php echo htmlspecialchars($statusTexto); ?>
                                </span>

                                <?php if (!empty($inventario)): ?>
                                    <form action="index.php?action=usar_item" method="POST" class="form-item">
                                        <input type="hidden" name="idSlot" value="<?php echo $pk['idSlot']; ?>">
                                        <select name="idItem" required>
                                            <option value="">-- Escolher item --</option>
                                            <?php foreach ($inventario as $item): ?>
                                                <option value="<?php echo $item['idItem']; ?>">
                                                    <?php echo htmlspecialchars($item['nome']); ?> (x<?php echo $item['quantidade']; ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn-usar">Usar Item</button>
                                    </form>
                                <?php else: ?>
                                    <p style="font-size: 0.8em; color: #666; margin-top: 10px;">Sem itens na mochila.</p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>