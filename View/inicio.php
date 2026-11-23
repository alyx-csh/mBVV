<?php
// Se a sessão do usuário não existir, bloqueia e manda pro login
if (!isset($_SESSION['idUsuario'])) {
    header("Location: index.php?action=login");
    exit;
}

$usuarioModel = new Usuario($db);
$usuario = $usuarioModel->buscarPorId($_SESSION['idUsuario']);
$nome = $usuario['nome'];
$fotoPerfil = $usuario['img'];
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
            margin: 0;
        }
        .menu-container {
            background: rgba(232, 232, 232, 0.95);
            width: 400px;
            margin: 0 auto;
            padding: 30px;
            border-radius: 15px;
            border: 4px double #3B4CCA;
        }
        h1 {
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
        .troca-login {
            background: rgba(232, 232, 232, 0.95);
            border-radius: 15px;
            border: 4px double #3B4CCA;
            width: 240px;
            height: 76px;
            padding: 8px 12px;
            position: fixed;
            right: 15px;
            top: 15px;
            box-sizing: border-box;
            display: flex;
            align-items: center;
            gap: 10px;
            overflow: hidden;
        }
        .foto-perfil {
            width: 52px;
            height: 52px;
            max-width: 52px;
            max-height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #3B4CCA;
            flex-shrink: 0;
        }
        .info-login {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: stretch;
            flex: 1;
            min-width: 0;
            height: 100%;
            gap: 4px;
        }
        .nome-login {
            color: #000000;
            font-size: 0.95em;
            font-weight: bold;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            text-align: left;
        }
        .btn-logout {
            display: block;
            width: 100%;
            padding: 5px 8px;
            margin: 0;
            background-color: #FFDE00;
            color: #000000;
            font-weight: bold;
            font-size: 0.85em;
            text-decoration: none;
            border-radius: 6px;
            border: 2px solid #3B4CCA;
            box-sizing: border-box;
            transition: 0.2s;
            text-align: center;
        }
        .btn-logout:hover {
            background-color: #e5c800;
            transform: scale(1.03);
        }
    </style>
</head>
<body>

    <div class="menu-container">
        <h1>MonstroBolso VamoVe!</h1>
        <!-- <p>Escolha para onde ir:</p> -->

        <!-- leva pra loja -->
        <a href="index.php?action=loja" class="btn-opcao">Lojinha</a>

        <!-- leva pra batalha -->
        <a href="index.php?action=batalha" class="btn-opcao">Batalhar</a>

        <!-- montar -->
        <a href="index.php?action=montar_time" class="btn-opcao">Montar time</a>
    </div>

    <div class="troca-login">
        <?php if ($fotoPerfil): ?>
            <img src="<?php echo htmlspecialchars($fotoPerfil); ?>" alt="Foto de perfil" class="foto-perfil">
        <?php endif; ?>
        <div class="info-login">
            <p class="nome-login"><?php echo htmlspecialchars($nome); ?></p>
            <a href="index.php?action=mudar_perfil" class="btn-logout">Mudar perfil</a>
        </div>
    </div>

</body>
</html>