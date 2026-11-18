<?php
// Se a sessão do usuário não existir, bloqueia e manda pro login
if (!isset($_SESSION['idUsuario'])) {
    header("Location: index.php?action=login");
    exit;
}

$usuarioModel = new Usuario($db);
$usuario = $usuarioModel->buscarPorId($_SESSION['idUsuario']);
$nome = $usuario['nome'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu MonstroBolso VavoVê</title>
    <style>
        @font-face {
            font-family: Pokemon;
            src: url("fontes/PocketMonk-15ze.ttf");
        }

        body {
        background-image: url("imagens/inicio.jpg");
        font-family: Helvetica;
        text-align: center;
        padding-top: 100px;
        color: white;
        }
        .menu-container {
        background: rgba(232, 232, 232, 0.95);
        width: 400px;
        margin: 0 auto;
        padding: 30px;
        border-radius: 15px;
        border: 4px double #3B4CCA;
        }
        h1{
        font-family: Pokemon;
        color: #FFDE00;
        font-size: 3em;
        -webkit-text-stroke-color: #3B4CCA;
        -webkit-text-stroke-width: 0.2vh;
        text-align: center;
        margin-top: 0;
        margin-bottom: 25px;
        letter-spacing: 2px;
        }
        .btn-opcao {
        display: block;
        width: 100%;
        padding: 15px;
        margin: 15px 0;
        background-color: #FFDE00;
        color: #000000;
        font-weight: bold;
        font-size: 1.2em;
        text-decoration: none;
        border-radius: 8px;
        border: 2px solid #3B4CCA;
        box-sizing: border-box;
        transition: 0.2s;
        }
        .btn-opcao:hover {
        background-color: #e5c800;
        transform: scale(1.05);
        }
        .troca-login{
        background: rgba(232, 232, 232, 0.95);
        margin: 0 auto;
        border-radius: 15px;
        border: 4px double #3B4CCA;
        width: 200px;
        padding: 2vh;
        position: absolute;
        left: 86.8%;
        top: 0%;
        height: 6vh;
        }
        .nome-login {
        color: #000000;
        font-size: 2.4vh;
        position: absolute;
        top: -2vh;
        }
    </style>
</head>
<body>

    <div class="menu-container">
        <h1>MonstroBolso VamoVe!</h1>
        <!-- <p>Escolha para onde ir:</p> -->

        <!-- leva pra loja -->
        <a href="index.php?action=loja" class="btn-opcao">Lojinha</a>

        <!-- leva pra batalha (fazer um trigger pra se não tiver nenhum Pokémon não entra)-->
        <a href="index.php?action=batalha" class="btn-opcao">Batalhar</a>

        <!-- montar  -->
        <a href="index.php?action=montar_time" class="btn-opcao">Montar time</a>
    </div>

    <div class="troca-login">
        <b><p class="nome-login"><?php echo $nome; ?></p></b>
        <a href="index.php?action=logout" class="btn-opcao">Mudar Login</a>
    </div>

</body>
</html>