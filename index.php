<?php
include('banco.php');
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    </script>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/estilo_login.css">
    <link rel="shortcut icon" href="img/png_logo.ico" type="image/x-icon">
    <title>FlickPicks - Login</title>
</head>

<body>
    <div class="container">
        <h1 class="titulo_celular">Faça login para continuar no FlickPicks!</h1>
        <div class="left-login">
            <h1 class="titulo">Faça login para continuar no FlickPicks!</h1>
            <img style="width: 70%;" src="img/assistindo.png" alt="">
        </div>
        <div class="login-box">
            <h2>Login</h2>
            <form action="" method='POST'>
                <?php if (isset($_POST["enviar"])) {
                    echo logar_user($client, $_POST["email"], $_POST["senha"]);
                } ?>
                <div class="user-box">
                    <input type="email" autocomplete="off" name="email" required="">
                    <label>Email</label>
                </div>
                <div class="user-box">
                    <input type="password" name="senha" required="">
                    <label>Senha</label>
                </div>
                <div class="botao">
                    <a class="login_cadastro" href="#">
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <input class="btn_enviar" type="submit" value="Entrar" name="enviar">
                    </a>
                </div>
                <a class="Slogin_Scadastro" href="registro.php">Cadastre-se aqui!</a>
            </form>
        </div>
    </div>
</body>

</html>