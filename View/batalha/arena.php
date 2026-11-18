<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Arena de Batalha - MonstroBolso</title>
    <style>
        body {
            background-image: url("imagens/fundo.png");
            background-size: cover;
            background-attachment: fixed;
            font-family: Helvetica, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .arena-container {
            width: 70vw;
            margin: auto;
            background: rgba(245, 245, 245, 0.95);
            border: 4px double #3B4CCA;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.4);
        }
        h1 { color: #FFDE00; -webkit-text-stroke: 1px #3B4CCA; text-align: center; font-family: sans-serif; }
        .campo-batalha {
            display: flex;
            justify-content: space-around;
            align-items: center;
            background: #dff9fb;
            border: 2px solid #78e08f;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .combatente { text-align: center; }
        .combatente img { width: 120px; height: 120px; image-rendering: pixelated; }
        .painel-controle { text-align: center; background: #f5f6fa; padding: 15px; border-radius: 10px; }
        .botoes-ataque { display: flex; gap: 15px; justify-content: center; margin-top: 10px; }
        button { background: #FFDE00; color: #3B4CCA; border: 2px solid #3B4CCA; padding: 10px 20px; font-weight: bold; border-radius: 8px; cursor: pointer; }
        button:hover { background: #fbc531; }
        .lista-equipa { margin-top: 15px; display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }
        .btn-troca { background: #4cd137; color: white; border: none; padding: 8px 12px; border-radius: 5px; cursor: pointer; }
        .btn-troca:disabled { background: #7f8c8d; cursor: not-allowed; }
    </style>
</head>
<body>
    <div class="arena-container">
        <h1>Tá na hora do Vamo Ve!</h1>

        <?php 
            $pokemons = $timeAtual['pokemons'] ?? [];
            $ativoIndex = 0; 
            $pokemonAtivo = $pokemons[$ativoIndex] ?? null;
        ?>

        <?php if (!$pokemonAtivo): ?>
            <p style="text-align: center; color: red;">Time sem pokémons!</p>
        <?php else: ?>

        <div class="campo-batalha">
            <!-- Oponente -->
            <div class="combatente">
                <h3><?php echo htmlspecialchars($oponente['nome']); ?></h3>
                <p>HP: <span id="hp-opo-atual"><?php echo $oponente['hp']; ?></span> / <span id="hp-opo-max"><?php echo $oponente['hpMax']; ?></span></p>
                <img src="<?php echo $oponente['img']; ?>" alt="Oponente">
            </div>

            <h2>VS</h2>

            <!-- Jogador Ativo (Carrega o HP atual vindo do banco no slot) -->
            <div class="combatente">
                <h3 id="nome-pk-ativo"><?php echo htmlspecialchars($pokemonAtivo['nome']); ?> (Tipo: <?php echo $pokemonAtivo['tipo1']; ?>)</h3>
                <p>HP: <span id="hp-jog-atual"><?php echo $pokemonAtivo['hp']; ?></span> / <span id="hp-jog-max"><?php echo $pokemonAtivo['hpMax']; ?></span></p>
                <img id="img-pk-ativo" src="<?php echo $pokemonAtivo['img']; ?>" alt="Jogador">
            </div>
        </div>

        <div class="painel-controle">
            <p id="log-batalha">ataque ou troque de pokémon</p>
            <div class="botoes-ataque">
                <button id="btn-ataque1" onclick="executarAto('ataque1', '<?php echo htmlspecialchars($pokemonAtivo['ataque1']); ?>')">
                    <?php echo htmlspecialchars($pokemonAtivo['ataque1']); ?> <br>
                </button>
                <button id="btn-ataque2" onclick="executarAto('ataque2', '<?php echo htmlspecialchars($pokemonAtivo['ataque2']); ?>')">
                    <?php echo htmlspecialchars($pokemonAtivo['ataque2']); ?> <br>
                </button>
            </div>

            <?php if (count($pokemons) > 1): ?>
                <div class="lista-equipa">
                    <p style="width: 100%; margin: 5px 0;"><strong>Trocar Pokémon:</strong></p>
                    <?php foreach ($pokemons as $index => $pk): ?>
                        <button class="btn-troca" <?php echo ($pk['hp'] <= 0) ? 'disabled' : ''; ?> onclick="trocarPokemon(<?php echo $pk['idSlot']; ?>, '<?php echo addslashes($pk['nome']); ?>', <?php echo $pk['hp']; ?>, <?php echo $pk['hpMax']; ?>, '<?php echo $pk['img']; ?>', '<?php echo $pk['ataque1']; ?>', '<?php echo $pk['ataque2']; ?>', '<?php echo $pk['tipo1']; ?>', <?php echo $pk['atk']; ?>, <?php echo $pk['def']; ?>, <?php echo $pk['spe']; ?>)">
                            <?php echo htmlspecialchars($pk['nome']); ?> (HP: <?php echo $pk['hp']; ?>)
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php endif; ?>

        <p style="text-align: center; margin-top: 20px;"><a href="index.php?action=inicio">⬅ Voltar ao Menu</a></p>
    </div>

    <script>
        // Objeto em JS contendo o idSlot para rastrear e atualizar o HP no banco
        let pokemonAtivo = {
            idSlot: <?php echo $pokemonAtivo['idSlot'] ?? 0; ?>,
            atk: <?php echo $pokemonAtivo['atk'] ?? 100; ?>,
            def: <?php echo $pokemonAtivo['def'] ?? 100; ?>,
            spe: <?php echo $pokemonAtivo['spe'] ?? 100; ?>,
            hp: <?php echo $pokemonAtivo['hp'] ?? 150; ?>
        };

        let oponente = {
            atk: <?php echo $oponente['atk']; ?>,
            def: <?php echo $oponente['def']; ?>,
            spe: <?php echo $oponente['spe']; ?>,
            hp: <?php echo $oponente['hp']; ?>
        };

        async function executarAto(tipoAtaque, nomeAtaque) {
            if (pokemonAtivo.hp <= 0) {
                document.getElementById('log-batalha').innerText = "Seu pokémon desmaiou e não pode mais lutar!";
                return;
            }

            try {
                const resposta = await fetch('index.php?action=processar_turno', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        tipoAtaque: tipoAtaque,
                        pokemonAtivo: pokemonAtivo,
                        oponente: oponente
                    })
                });

                if (!resposta.ok) {
                    throw new Error("Erro na requisição da batalha");
                }

                const data = await resposta.json();

                document.getElementById('hp-opo-atual').innerText = data.hpOponente;
                document.getElementById('hp-jog-atual').innerText = data.hpJogador;
                
                oponente.hp = data.hpOponente;
                pokemonAtivo.hp = data.hpJogador;

                document.getElementById('log-batalha').innerText = data.mensagem;

                if (data.derrota || pokemonAtivo.hp <= 0) {
                    desativarBotoesAtaque();
                    // Correção da atribuição de HTML no log
                    document.getElementById('log-batalha').innerHTML += "<br><strong>Pokémon desmaiado!</strong>";
                    setTimeout(() => window.location.href = 'index.php?action=inicio', 4000);
                    return;
                }

                if (data.vitoria) {
                    desativarBotoesAtaque();
                    setTimeout(() => window.location.href = 'index.php?action=inicio', 4000);
                }

            } catch (erro) {
                console.error("Falha:", erro);
                document.getElementById('log-batalha').innerText = "Ocorreu um erro de conexão ao processar o turno.";
            }
        }

        function trocarPokemon(idSlot, nome, hp, hpMax, img, atq1, atq2, tipo1, atkStat, defStat, speStat) {
            if (hp <= 0) {
                alert("Pokémon desmaiado e não pode entrar em batalha");
                return;
            }

            // Atualiza os dados do Pokémon ativo
            pokemonAtivo.idSlot = idSlot;
            pokemonAtivo.atk = atkStat;
            pokemonAtivo.def = defStat;
            pokemonAtivo.spe = speStat;
            pokemonAtivo.hp = hp;

            // Atualiza a interface visual (Nome, HP, Imagem)
            document.getElementById('nome-pk-ativo').innerText = `${nome} (Tipo: ${tipo1})`;
            document.getElementById('hp-jog-atual').innerText = hp;
            document.getElementById('hp-jog-max').innerText = hpMax;
            document.getElementById('img-pk-ativo').src = img;
            document.getElementById('log-batalha').innerText = `Enviou ${nome}!`;

            // ATUALIZA OS BOTÕES DE ATAQUE COM OS GOLPES DO NOVO POKÉMON
            const btn1 = document.getElementById('btn-ataque1');
            const btn2 = document.getElementById('btn-ataque2');

            btn1.innerText = atq1;
            btn1.setAttribute("onclick", `executarAto('ataque1', '${atq1}')`);

            btn2.innerText = atq2;
            btn2.setAttribute("onclick", `executarAto('ataque2', '${atq2}')`);
        }

        function desativarBotoesAtaque() {
            const botoes = document.querySelectorAll('.botoes-ataque button, .btn-troca');
            botoes.forEach(btn => btn.disabled = true);
        }
    </script>
</body>
</html>