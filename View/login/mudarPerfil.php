<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mudar foto de perfil</title>
    <style>
        @font-face {
            font-family: Pokemon;
            src: url("fontes/PocketMonk-15ze.ttf");
        }

        body { 
            background-image: url("imagens/fundo.png"); 
            background-size: cover;
            background-position: center;
            height: 100vh;
            margin: 0;
        }

        div.card-login { 
            width: 28%; 
            min-width: 320px;
            background-color: rgba(232, 232, 232, 0.90); 
            display: flex; 
            flex-direction: column;
            align-items: center; 
            justify-content: flex-start;
            position: absolute; 
            top: 0; bottom: 0; left: 0; right: 0; 
            margin: auto; 
            height: fit-content;
            min-height: 65vh; 
            border-radius: 3vh; 
            border: 4px double #3B4CCA;
            box-shadow: 0 8px 16px rgba(0,0,0,0.3);
            padding: 25px 20px;
            box-sizing: border-box;
        }

        #titulo { 
            font-family: Pokemon; 
            color: #FFDE00; 
            -webkit-text-stroke: 0.15vh #3B4CCA; 
            font-size: 3.5vw;
            text-align: center;
            margin-top: 0;
            margin-bottom: 20px;
            width: 100%;
        }

        form {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: auto;
            margin-bottom: auto;
        }

        .input-group {
            width: 85%;
            margin-bottom: 16px;
            display: flex;
            flex-direction: column;
        }

        label { 
            font-family: Helvetica; 
            font-weight: bold; 
            font-size: 1.05em; 
            color: #333;
            margin-bottom: 5px;
        }

        input { 
            width: 100%; 
            height: 40px; 
            font-size: 1em; 
            padding: 5px 10px;
            border-radius: 8px;
            border: 2px solid #ccc;
            box-sizing: border-box;
            background-color: #fff;
        }

        input[type="file"] {
            height: auto;
            padding: 7px 10px;
            font-size: 0.9em;
            cursor: pointer;
        }

        input:focus {
            border-color: #3B4CCA;
            outline: none;
        }

        #submit { 
            width: 85%; 
            padding: 12px;
            margin-top: 8px;
            border-radius: 1vh; 
            border: 3px double #3B4CCA;
            background-color: #FFDE00;
            color: #000000;
            font-weight: bold;
            font-family: Helvetica;
            font-size: 1.1em;
            cursor: pointer;
            transition: transform 0.1s, background-color 0.2s;
        }

        a{
            text-align: center;
            width: 70%; 
            padding: 12px;
            margin-top: 8px;
            border-radius: 1vh; 
            border: 3px double #3B4CCA;
            background-color: #ff0000;
            color: #FFFFFF;
            font-weight: bold;
            font-family: Helvetica;
            font-size: 1.1em;
            cursor: pointer;
            transition: transform 0.1s, background-color 0.2s;
        }

        #submit:hover { 
            background-color: #ffcc00; 
            transform: scale(1.03); 
        }

        .erro { 
            color: #d32f2f; 
            font-family: Helvetica;
            font-size: 0.95em; 
            margin-bottom: 15px; 
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- div do formulário -->
    <div class="card-login"> 
        <h1 id="titulo">MonstroBolso VamoVe!</h1>

        <?php if(isset($_SESSION['erro_login'])): ?>
            <div class="erro">
                <?php echo $_SESSION['erro_login']; unset($_SESSION['erro_login']); ?>
            </div> 
        <?php endif; ?>

        <form action="index.php?action=salvar_perfil" method="POST" enctype="multipart/form-data">

            <div class="input-group">
                <label for="fotoinput">Selecionar foto de perfil</label>
                <input type="file" name="foto" id="fotoinput" accept="image/*" required>
            </div>

            <button type="submit" id="submit">Mudar</button>
            <br>
            <a href="index.php?action=logout" class="btn-logout">Sair do perfil</a>
        </form>
    </div>
</body>
</html>