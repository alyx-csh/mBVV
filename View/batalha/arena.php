<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Arena de Batalha - MonstroBolso</title>
    <style>
        body {
            background-image: url("imagens/batalha.png");
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
        .lista-equipe { margin-top: 15px; display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }
        .btn-troca { background: #4cd137; color: white; border: none; padding: 8px 12px; border-radius: 5px; cursor: pointer; }
        .btn-troca:disabled { background: #7f8c8d; cursor: not-allowed; }
    </style>
</head>
<body>
    <div class="arena-container">
        <h1>Tá na hora do Vamo Ve!</h1>

        <?php
            $inventario = $itensUsuario ?? [];
            $itensDeStatus = ['Antidote', 'Paralyze Heal', 'Burn Heal', 'Ice Heal', 'Awakening', 'Full Heal', 'Full Restore'];
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
                <p>Status: <span id="status-oponente">Saudável</span></p>
                <img src="<?php echo $oponente['img']; ?>" alt="Oponente">
            </div>

            <h2>VS</h2>

            <!-- Jogador Ativo (Carrega o HP e Status atuais vindos do banco no slot) -->
            <div class="combatente">
                <h3 id="nome-pk-ativo"><?php echo htmlspecialchars($pokemonAtivo['nome']); ?></h3>
                <p>HP: <span id="hp-jog-atual"><?php echo $pokemonAtivo['hp']; ?></span> / <span id="hp-jog-max"><?php echo $pokemonAtivo['hpMax']; ?></span></p>
                <p>Status: <span id="status-jogador"><?php echo htmlspecialchars($pokemonAtivo['status'] ?: 'Saudável'); ?></span></p>
                <img id="img-pk-ativo" src="<?php echo $pokemonAtivo['img']; ?>" alt="Jogador">
            </div>
        </div>

        <div class="painel-controle">
            <p id="log-batalha">ataque ou troque de pokémon</p>
            <div class="botoes-ataque">
                <button id="btn-ataque1" onclick="executarAto('ataque1', '<?php echo htmlspecialchars($pokemonAtivo['ataque1'], ENT_QUOTES, 'UTF-8'); ?>')">
                    <?php echo htmlspecialchars($pokemonAtivo['ataque1']); ?> <br>
                </button>
                <button id="btn-ataque2" onclick="executarAto('ataque2', '<?php echo htmlspecialchars($pokemonAtivo['ataque2'], ENT_QUOTES, 'UTF-8'); ?>')">
                    <?php echo htmlspecialchars($pokemonAtivo['ataque2']); ?> <br>
                </button>
            </div>

            <div style="margin-top: 15px;">
                <label for="item-batalha">Usar item:</label>
                <select id="item-batalha">
                    <option value="">Selecione um item de status</option>
                    <?php foreach ($inventario as $item): ?>
                        <?php if (in_array($item['nome'], $itensDeStatus) && intval($item['quantidade'] ?? 0) > 0): ?>
                            <option value="<?php echo $item['idItem']; ?>">
                                <?php echo htmlspecialchars($item['nome']); ?> (x<?php echo intval($item['quantidade']); ?>)
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <button type="button" onclick="usarItemBatalha()">Usar item no Pokémon ativo</button>
            </div>

            <?php if (count($pokemons) > 1): ?>
                <div class="lista-equipe">
                    <p style="width: 100%; margin: 5px 0;"><strong>Trocar Pokémon:</strong></p>
                    <?php foreach ($pokemons as $index => $pk): ?>
                        <button
                            id="troca-<?php echo $pk['idSlot']; ?>"
                            class="btn-troca"
                            data-id-slot="<?php echo $pk['idSlot']; ?>"
                            data-nome="<?php echo htmlspecialchars($pk['nome'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-hp="<?php echo $pk['hp']; ?>"
                            data-hp-max="<?php echo $pk['hpMax']; ?>"
                            data-img="<?php echo htmlspecialchars($pk['img'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-ataque1="<?php echo htmlspecialchars($pk['ataque1'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-ataque2="<?php echo htmlspecialchars($pk['ataque2'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-tipo1="<?php echo htmlspecialchars($pk['tipo1'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-atk="<?php echo $pk['atk']; ?>"
                            data-def="<?php echo $pk['def']; ?>"
                            data-spe="<?php echo $pk['spe']; ?>"
                            data-status="<?php echo htmlspecialchars($pk['status'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                            <?php echo ($pk['hp'] <= 0) ? 'disabled' : ''; ?>
                            onclick="trocarPokemon(this)">
                            <?php echo htmlspecialchars($pk['nome']); ?> (HP: <?php echo $pk['hp']; ?>)
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php endif; ?>
    </div>

    <script>
        let pokemonAtivo = {
            idTimes: <?php echo json_encode($timeAtual['idTimes'] ?? null); ?>,
            idSlot: <?php echo json_encode($pokemonAtivo['idSlot'] ?? 0); ?>,
            nome: <?php echo json_encode($pokemonAtivo['nome'] ?? 'Pokémon'); ?>,
            atk: <?php echo json_encode($pokemonAtivo['atk'] ?? 100); ?>,
            def: <?php echo json_encode($pokemonAtivo['def'] ?? 100); ?>,
            spe: <?php echo json_encode($pokemonAtivo['spe'] ?? 100); ?>,
            hp: <?php echo json_encode($pokemonAtivo['hp'] ?? 150); ?>,
            hpMax: <?php echo json_encode($pokemonAtivo['hpMax'] ?? 150); ?>,
            status: <?php echo json_encode($pokemonAtivo['status'] ?? null); ?>,
            tipo1: <?php echo json_encode($pokemonAtivo['tipo1'] ?? 'Normal'); ?>
        };

        let oponente = {
            nome: <?php echo json_encode($oponente['nome']); ?>,
            atk: <?php echo $oponente['atk']; ?>,
            def: <?php echo $oponente['def']; ?>,
            spe: <?php echo $oponente['spe']; ?>,
            hp: <?php echo $oponente['hp']; ?>,
            hpMax: <?php echo $oponente['hpMax']; ?>,
            status: null,
            tipo1: <?php echo json_encode($oponente['tipo1']); ?>
        };

        function statusLegivel(status) {
            if (!status || status === 'null') return 'Saudável';
            return status;
        }

        function sincronizarBotaoTrocaAtivo() {
            const botaoTrocaAtivo = document.getElementById(`troca-${pokemonAtivo.idSlot}`);
            if (botaoTrocaAtivo) {
                botaoTrocaAtivo.dataset.hp = pokemonAtivo.hp;
                botaoTrocaAtivo.dataset.status = pokemonAtivo.status || '';
                botaoTrocaAtivo.textContent = `${pokemonAtivo.nome} (HP: ${pokemonAtivo.hp})`;
                if (pokemonAtivo.hp <= 0) {
                    botaoTrocaAtivo.disabled = true;
                }
            }
        }

        async function executarAto(tipoAtaque, nomeAtaque) {
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
                oponente.status = data.statusOponente;
                pokemonAtivo.status = data.statusJogador;

                document.getElementById('status-oponente').innerText = statusLegivel(oponente.status);
                document.getElementById('status-jogador').innerText = statusLegivel(pokemonAtivo.status);
                sincronizarBotaoTrocaAtivo();

                document.getElementById('log-batalha').innerText = data.mensagem;

                if (data.derrota || pokemonAtivo.hp <= 0) {
                    desativarBotoesAtaque();
                    document.getElementById('log-batalha').innerHTML += "<br><strong>Pokémon desmaiou, redirecionando à tela inicial...</strong>";
                    setTimeout(() => window.location.href = 'index.php?action=inicio', 4000);
                    return;
                }

                if (data.vitoria) {
                    desativarBotoesAtaque();
                    document.getElementById('log-batalha').innerHTML += "<br><strong>Você ganhou, redirecionando à tela inicial...</strong>";
                    setTimeout(() => window.location.href = 'index.php?action=inicio', 4000);
                }
            } catch (erro) {
                console.error("Falha:", erro);
                document.getElementById('log-batalha').innerText = "Ocorreu um erro de conexão ao processar o turno.";
            }
        }

        async function usarItemBatalha() {
            const select = document.getElementById('item-batalha');
            const idItem = select ? select.value : '';

            if (!idItem) {
                document.getElementById('log-batalha').innerText = "Selecione um item antes de usar.";
                return;
            }

            try {
                const resposta = await fetch('index.php?action=usar_item_batalha', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        idSlot: pokemonAtivo.idSlot,
                        idItem: parseInt(idItem, 10)
                    })
                });

                const data = await resposta.json();
                document.getElementById('log-batalha').innerText = data.mensagem || "Ação concluída.";

                if (!data.sucesso) {
                    return;
                }

                pokemonAtivo.hp = data.hpJogador;
                pokemonAtivo.status = data.statusJogador;

                document.getElementById('hp-jog-atual').innerText = pokemonAtivo.hp;
                document.getElementById('status-jogador').innerText = statusLegivel(pokemonAtivo.status);
                sincronizarBotaoTrocaAtivo();

                const opcaoSelecionada = select.querySelector(`option[value="${data.idItem}"]`);
                if (opcaoSelecionada) {
                    const nomeBase = opcaoSelecionada.textContent.replace(/\s*\(x\d+\)$/, '');
                    if (data.quantidadeRestante <= 0) {
                        opcaoSelecionada.remove();
                        select.value = '';
                    } else {
                        opcaoSelecionada.textContent = `${nomeBase} (x${data.quantidadeRestante})`;
                    }
                }
            } catch (erro) {
                console.error("Erro ao usar item na batalha:", erro);
                document.getElementById('log-batalha').innerText = "Erro de conexão ao usar o item.";
            }
        }

        function trocarPokemon(botao) {
            const d = botao.dataset;
            const hp = parseInt(d.hp, 10);

            if (hp <= 0) {
                alert("Pokémon desmaiado e não pode entrar em batalha");
                return;
            }

            pokemonAtivo.idSlot = parseInt(d.idSlot, 10);
            pokemonAtivo.nome = d.nome;
            pokemonAtivo.atk = parseInt(d.atk, 10);
            pokemonAtivo.def = parseInt(d.def, 10);
            pokemonAtivo.spe = parseInt(d.spe, 10);
            pokemonAtivo.hp = hp;
            pokemonAtivo.hpMax = parseInt(d.hpMax, 10);
            pokemonAtivo.status = d.status || null;
            pokemonAtivo.tipo1 = d.tipo1;

            document.getElementById('nome-pk-ativo').innerText = `${d.nome}`;
            document.getElementById('hp-jog-atual').innerText = hp;
            document.getElementById('hp-jog-max').innerText = d.hpMax;
            document.getElementById('status-jogador').innerText = statusLegivel(pokemonAtivo.status);
            document.getElementById('img-pk-ativo').src = d.img;
            document.getElementById('log-batalha').innerText = `Enviou ${d.nome}!`;

            const btn1 = document.getElementById('btn-ataque1');
            const btn2 = document.getElementById('btn-ataque2');

            btn1.innerText = d.ataque1;
            btn1.setAttribute("onclick", `executarAto('ataque1', '${d.ataque1.replace(/'/g, "\\'")}')`);

            btn2.innerText = d.ataque2;
            btn2.setAttribute("onclick", `executarAto('ataque2', '${d.ataque2.replace(/'/g, "\\'")}')`);
        }

        function desativarBotoesAtaque() {
            const botoes = document.querySelectorAll('.botoes-ataque button, .btn-troca');
            botoes.forEach(btn => btn.disabled = true);
        }
    </script>
</body>
</html>