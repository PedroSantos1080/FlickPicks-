<?php
include('banco.php');
if (isset($_POST["enviar"])) {
    echo criar_user($client, $_POST["username"], $_POST["email"], $_POST["nome"], $_POST["senha"]);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form action="" method='POST'>
        <p>Username:</p>
        <input type="text" name="username" id="">
        <p>Email:</p>
        <input type="email" name="email" id="">
        <p>Nome:</p>
        <input type="text" name="nome" id="">
        <p>Senha:</p>
        <input type="password" name="senha" id="">
        <input type="submit" value="Enviar" name="enviar">
    </form> 

</body>
</html>