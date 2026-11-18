<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Montar Equipa de Pokémon</title>
    <style>
        @font-face {
            font-family: Pokemon;
            src: url("fontes/PocketMonk-15ze.ttf");
        }

        body {
        background-image: url("imagens/montarTime.png");
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        background-attachment: fixed;
        height: 100vh;
        margin: 0;
        overflow-y: auto;
        font-family: Helvetica, sans-serif;
        }

        /* Caixa principal com o estilo translúcido e borda dupla azul */
        .container-time {
        width: 45vw; 
        background-color: rgba(232, 232, 232, 0.95); 
        border-radius: 2vh; 
        border: 4px double #3B4CCA;
        padding: 25px;
        box-sizing: border-box;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        margin: 8vh auto; /* Centraliza na tela e dá espaço do topo */
        }

        h1.titulo {
        font-family: Pokemon; 
        color: #FFDE00;
        font-size: 3.5em;
        -webkit-text-stroke-color: #3B4CCA;
        -webkit-text-stroke-width: 0.2vh;
        text-align: center;
        margin-top: 0;
        margin-bottom: 20px;
        letter-spacing: 2px;
        }

        label {
        font-weight: bold;
        color: #333;
        font-size: 1.05em;
        display: block;
        margin-bottom: 5px;
        }

        input[type="text"] {
        width: 100%;
        padding: 10px;
        margin-bottom: 12px;
        border: 2px solid #ccc;
        border-radius: 8px;
        box-sizing: border-box;
        font-size: 1em;
        background: rgba(255, 255, 255, 0.9);
        }

        input[type="text"]:focus {
        border-color: #3B4CCA;
        outline: none;
        }

        /* Botão estilizado idêntico ao da compra/login */
        button.btn-salvar {
        width: 100%;
        padding: 12px;
        border-radius: 1vh;
        border: 3px double #3B4CCA;
        background-color: #FFDE00;
        color: #3B4CCA;
        font-weight: bold;
        font-family: Helvetica;
        font-size: 1.1em;
        cursor: pointer;
        transition: transform 0.1s, background-color 0.2s;
        margin-top: 15px;
        }

        button.btn-salvar:hover {
        background-color: #ffcc00;
        transform: scale(1.02);
        }

        .msg-sucesso {
        background: #d4edda;
        color: #155724;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 15px;
        text-align: center;
        font-weight: bold;
        }

        .msg-erro {
        background: #f8d7da;
        color: #721c24;
        padding: 10px;
        border-radius: 5px; 
        margin-bottom: 15px; 
        text-align: center; 
        font-weight: bold;
        }

        .link-voltar {
        display: block;
        text-align: center;
        margin-top: 15px;
        color: #3B4CCA;
        text-decoration: none;
        font-weight: bold;
        }

        .link-voltar:hover {
        text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container-time">
        <h1 class="titulo">Monte seu time!</h1>
        
        <?php if(isset($_SESSION['sucesso_time'])): ?>
            <div class="msg-sucesso">
                <?php echo $_SESSION['sucesso_time']; unset($_SESSION['sucesso_time']); ?>
            </div>
        <?php endif; ?>

        <?php if(isset($_SESSION['erro_time'])): ?>
            <div class="msg-erro">
                <?php echo $_SESSION['erro_time']; unset($_SESSION['erro_time']); ?>
            </div>
        <?php endif; ?>

        <form action="index.php?action=salvar_time" method="POST">
            <label>Nome da Equipe:</label>
            <input type="text" name="nomeTime" placeholder="Pudim" required>

            <hr style="margin: 15px 0; border: 0; border-top: 1px solid #ccc;">

            <label>Digite os nomes (até 6):</label>
            <input type="text" name="pokemons[]" placeholder="Pikachu">
            <input type="text" name="pokemons[]" placeholder="Charizard">
            <input type="text" name="pokemons[]" placeholder="Bulbasaur">
            <input type="text" name="pokemons[]" placeholder="Snorlax">
            <input type="text" name="pokemons[]" placeholder="Gengar">
            <input type="text" name="pokemons[]" placeholder="Lucario">

            <button type="submit" class="btn-salvar">Guardar equipe</button>
        </form>
        
        <a href="index.php?action=inicio" class="link-voltar">Voltar ao Início</a>
    </div>
</body>
</html>