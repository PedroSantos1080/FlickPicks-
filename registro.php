<?php
include('banco_teste.php');
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    </script>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/estilo_registro.css">
    <link rel="shortcut icon" href="img/png_logo.ico" type="image/x-icon">
    <title>FlickPicks - Registro</title>
</head>

<body>

    <div class="container">
        <h1 class="titulo_celular">Faça login para continuar em nosso site</h1>

        <div class="left-login">
            <h1 class="titulo">Faça registro para continuar no FlickPicks!</h1>
            <img style="width: 70%;" src="img/assistindo.png" alt="">
        </div>

        <!--Card Login https://cinepop.com.br/wp-content/uploads/2022/05/Tudo-em-Todo-o-Lugar-ao-Mesmo-Tempo-scaled.jpg>-->

        <div class="login-box">
            <h2 class="registrar">Registro</h2>
            <form action="" method='POST'>
                <?php if (isset($_POST["entrar"])) {
                    echo criar_user($client, $_POST["username"], $_POST["email"], $_POST["nome"], $_POST["senha"]);
                } ?>
                <div class="user-box">
                    <input type="text" autocomplete="off" name="username" required="">
                    <label>Nome de usuário</label>
                </div>
                <div class="user-box">
                    <input type="text" autocomplete="off" name="nome" required="">
                    <label>Nome</label>
                </div>
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
                        <input class="btn_enviar" type="submit" value="Registrar" name="entrar">
                    </a>
                </div>
                <a class="Slogin_Scadastro" href="login.php">Já tem uma conta? Entre aqui!</a>
            </form>
        </div>

    </div>

</body>

</html>