<?php
    // Se a sessão do usuário não existir, bloqueia e manda pro login
    if (!isset($_SESSION['idUsuario'])) {
        header("Location: index.php?action=login");
        exit;
    }
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
            background-position: center;
            background-repeat: no-repeat;
            height: 100vh;
            margin: 0;
            overflow: hidden;
        }
        
        /* controla a caixa de dinheiro e o botão de sair */
        div.dinheiro {
        position: absolute;
        font-family: Helvetica;
        top: 13vh;
        left: 5%;
        width: 18vw; 
        background-color: rgba(232, 232, 232, 0.95); 
        height: 15vh; border-radius: 2vh; 
        border: 4px double #3B4CCA;
        padding: 15px;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        }

        h2.money {
        margin: 0;
        font-size: 1.3em;
        color: #333; 
        }

        p.dindin {
        margin: 5px 0 0 0;
        font-size: 1em;
        font-weight: bold;
        color: #2e7d32;
        }

        .btn-sair {
        display: block;
        width: 100%;
        padding: 6px;
        background-color: #ff3b3b;
        color: white;
        text-decoration: none;
        font-family: Helvetica;
        font-size: 0.9em;
        border-radius: 6px;
        border: 2px solid #b71c1c;
        text-align: center;
        box-sizing: border-box;
        transition: background 0.2s;
        }

        .btn-sair:hover {
        background-color: #d32f2f;
        }

        div.itens {
        position: absolute;
        font-family: Helvetica, sans-serif;
        top: 13vh;
        left: 28%;
        width: 65vw; 
        background-color: rgba(232, 232, 232, 0.97); 
        height: 80vh; 
        border-radius: 1vh; 
        border: 4px double #3B4CCA;
        overflow-y: scroll; 
        padding: 20px;
        box-sizing: border-box;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }

        h1.titulo {
        font-family: Pokemon; 
        color: #FFDE00;
        font-size: 4.5em;
        -webkit-text-stroke-color: #3B4CCA;
        -webkit-text-stroke-width: 0.15vh;
        text-align: center;
        margin-top: 0;
        margin-bottom: 25px;
        letter-spacing: 2px;
        }

        .item-linha {
        display: flex;
        align-items: center;
        background: rgba(255, 255, 255, 0.9);
        margin-bottom: 15px;
        padding: 15px;
        border-radius: 10px;
        border: 2px solid #ccc;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .alerta-sucesso{
        background: #d4edda;
        color: #155724;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 15px;
        text-align: center;
        font-family: Helvetica; 
        }

        .alerta-erro {
        background: #f8d7da;
        color: #721c24;
        padding: 10px;
        border-radius: 5px; 
        margin-bottom: 15px; 
        text-align: center; 
        font-family: Helvetica; 
        }

        .item-linha img {
        width: 80px;
        height: 80px;
        object-fit: contain;
        padding-left: 0;
        }

        .item-info {
        margin-left: 20px;
        flex-grow: 1;
        }

        .item-info strong {
        font-size: 1.2em;
        display: block;
        color: #222;
        margin-bottom: 5px;
        }

        .item-info p {
        margin: 0;
        color: #555;
        font-size: 0.95em;
        }

        /* Estilo do Preço do Item */
        .item-preco {
        font-family: Helvetica;
        font-size: 1.05em;
        color: #d32f2f;
        font-weight: bold;
        }

        /* Botão de Compra */
        button.compra {
        padding: 12px 25px;
        border-radius: 1vh;
        border: 3px double #3B4CCA;
        background-color: #FFDE00;
        color: #3B4CCA;
        font-weight: bold;
        font-family: Helvetica;
        font-size: 1em;
        cursor: pointer;
        transition: transform 0.1s, background-color 0.2s;
        }

        button.compra:hover {
        background-color: #ffcc00;
        transform: scale(1.05);
        }

    </style>
</head>
<body>
    <!-- Caixa de Dinheiro + Botão Sair -->
    <div class="dinheiro"> 

        <h2 class="money">Dinheiro: <br> 
            <p class="dindin">PkD$ <?php echo number_format($din, 0, ',', '.'); ?></p> 
        </h2> 

        <a href="index.php" class="btn-sair"> <strong>Sair</strong> </a> <!--botão de sair-->
    
    </div>  

    <!-- Lista de Itens -->
    <div class="itens"> 
        <h1 class="titulo">Lojinha</h1> 
        
        <?php if(isset($_SESSION['mensagem'])): ?>
            <div class="alerta-sucesso">
                <?php echo $_SESSION['mensagem'];
                unset($_SESSION['mensagem']); 
                ?>
            </div>
        <?php endif; ?>

        <?php if(isset($_SESSION['erro'])): ?>
            <div class="alerta-erro">
                <?php echo $_SESSION['erro'];
                unset($_SESSION['erro']); ?>
            </div>
        <?php endif; ?>

        <?php 
            $contador = 1; 
            foreach ($itens as $row):
        ?>
            <div class="item-linha">
                <img src="imagens/itens/item<?php echo $contador; ?>.png" alt="<?php echo htmlspecialchars($row['descricao']); ?>"> 
                
                <div class="item-info">
                    <strong><?php echo htmlspecialchars($row['nome']); ?></strong>
                    <p><?php echo htmlspecialchars($row['descricao']); ?></p>
                    <span class="item-preco">Preço: P$ <?php echo number_format($row['preco'], 0, ',', '.'); ?></span>
                </div>

                <form action="index.php?action=comprar" method="POST">
                    <input type="hidden" name="idItem" value="<?php echo $row['idItem']; ?>">
                    <button type="submit" class="compra">Comprar</button>
                </form>
            </div>
        <?php
            $contador = ($contador < 13) ? $contador + 1 : 13;
            endforeach; 
        ?>
    </div>
</body>
</html>