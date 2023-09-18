<?php
session_start(); //Inicia a sessão.
include_once('config.php'); //Inclui o arquivo de configuração.
include('banco.php'); //Inclui o arquivo com funções relacionadas ao banco de dados.

//Verifica se o usuário não está logado, redireciona para a página de login se não estiver.
if ((!isset($_SESSION['id']))) {
    header('Location: index.php');
    return;
}

$logado = $_SESSION['id']; //Obtém o ID do usuário logado.
$filme_escolhido = null; //Inicializa a variável que armazenará informações sobre o filme escolhido.
$nao_encontrado = false; //Inicializa a variável para indicar se um filme não foi encontrado.
$filme_like = null; //Inicializa a variável para indicar se o usuário curtiu o filme.
$filme_deslike = null; //Inicializa a variável para indicar se o usuário descurtiu o filme.
$filme_watchlist = null; //Inicializa a variável para indicar se o filme está na watchlist do usuário.

//Verifica se o formulário de indicação de filme foi enviado.
if (isset($_POST["indicar"])) {
    // Obtém os parâmetros do formulário.
    $genero = $_POST["genero"];
    $classificacao = $_POST["classificacao"];
    $duracao = $_POST["duracao"];
    $ano = intval($_POST["ano"]);
    $disponibilidade = $_POST["disponibilidade"];
    $nacionalidade = $_POST["nacionalidade"];

    //Chama a função "filtro" para buscar um filme com base nos parâmetros.
    $nao_encontrado = filtro($client, $genero, $classificacao, $ano, $duracao, $disponibilidade, $nacionalidade);
}

//Verifica se um filme foi selecionado na página.
if (isset($_GET["filme"])) {
    $id_filme = $_GET["filme"];
    $resultado = filme_info($client, $id_filme);
    if (count($resultado) > 0) {
        //Se um filme com o ID especificado foi encontrado, armazena informações sobre ele.
        $filme_escolhido = $resultado[0];
        $like = $resultado[1];
        if ($like != null) {
            $filme_like = $like["like"];
            $filme_deslike = $like["deslike"];
        }

        $watchlist = $resultado[2];
        if ($watchlist != null) {
            $filme_watchlist = true;
        }
    } else {
        //Se o filme não foi encontrado, define a variável "nao_encontrado" como verdadeira.
        $nao_encontrado = true;
    }
}

// Verifica se o usuário curtiu um filme.
if (isset($_POST["like"])) {
    $id_filme = $_POST["like"];
    like_deslike($client, $id_filme, true); //Chama a função para registrar o "like" no filme.
    header('location: ?filme=' . $id_filme); //Redireciona de volta para a página do filme.
    die;
}

//Verifica se o usuário descurtiu um filme.
if (isset($_POST["deslike"])) {
    $id_filme = $_POST["deslike"];
    like_deslike($client, $id_filme, false); //Chama a função para registrar o "deslike" no filme.
    header('location: ?filme=' . $id_filme); //Redireciona de volta para a página do filme.
    die;
}

//Verifica se o usuário adicionou ou removeu um filme da watchlist.
if (isset($_POST["watchlist"])) {
    $id_filme = $_POST["watchlist"];
    watchlist($client, $id_filme); //Chama a função para gerenciar a watchlist do usuário.
    header('location: ?filme=' . $id_filme); //Redireciona de volta para a página do filme.
    die;
}

//Verifica se o usuário clicou em "reset" para limpar a URL.
if (isset($_POST["reset"])) {
    limparURL(); //Chama a função para limpar a URL.
}
?>


<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FlickPicks</title>
    <link rel="shortcut icon" href="img/png_logo.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/estilo_sistema.css">
</head>
<datagrid></datagrid>

<body>
    <!--------------------------------------------------MENU-------------------------------------------------->
    <header>
        <div class="container_menu">
            <div class="logo">
                <a href="sistema.php"><img src="img/png_logo.png" alt=""></a>
                <h1 class="title_rec">FLICKPICKS</h1>
            </div>
            <div class="social" id="social">
                <nav>
                    <ul>
                        <li><a href="perfil.php"><img class="img_perfil" src="img/perfil.png" alt=""></a></li>
                        <li><a href="roleta/roleta.php"><img class="img_perfil" src="img/roleta.png" alt=""></a></li>
                        <li><a href="sair.php"><img class="img_perfil" src="img/log-out.png" alt=""></a></li>
                    </ul>
                </nav>
                <button class="botao_menu menu-toggle"><img class="img_menu" id="menu" src="img/menu-aberto.png"
                        alt=""></button>
            </div>
        </div>
    </header>
    <script src="js/menu.js"></script>

    <!--------------------------------------------------CORPO-------------------------------------------------->
    <form action="" method="POST">
        <div class="container_corpo">
            <div class="tela_principal">
                <div class="topo">
                    <h1>Bem vindo ao FlickPicks! O que gostaria de ver hoje?</h1>


                </div>

                <div class="meio">
                    <h3>Filtre sua escolha:</h3>

                    <div class="filtros">
                        <div class="filtro">
                            <p>Gênero:</p>
                            <select name="genero" id="">
                                <option value=""></option>
                                <option value="Ação">Ação</option>
                                <option value="Terror">Terror</option>
                                <option value="Drama">Drama</option>
                                <option value="Animação">Animação</option>
                                <option value="Romance">Romance</option>
                                <option value="Comédia">Comédia</option>
                            </select>

                            <p>Classificação:</p>
                            <select name="classificacao" id="">
                                <option value=""></option>
                                <option value="L">Livre</option>
                                <option value="10">10</option>
                                <option value="12">12</option>
                                <option value="14">14</option>
                                <option value="16">16</option>
                                <option value="18">18</option>
                            </select>

                            <p>Disponibilidade:</p>
                            <select name="disponibilidade" id="">
                                <option value=""></option>
                                <option value="Netflix">Netflix</option>
                                <option value="Disney+">Disney+</option>
                                <option value="HBO Max">HBO Max</option>
                                <option value="Globoplay">Globoplay</option>
                                <option value="Star+">Star+</option>
                                <option value="Telecine">Telecine</option>
                                <option value="Prime Video">Prime Video</option>
                                <option value="Apple TV">Apple TV</option>
                                <option value="Lionsgate+">Lionsgate+</option>
                                <option value="Claro Vídeo">Claro Vídeo</option>
                                <option value="Paramount+">Paramount+</option>
                                <option value="Now">Now</option>
                            </select>

                        </div>

                        <div class="format_tablet"></div>

                        <div class="filtro filtro-direita">
                            <p>Ano:</p>
                            <select name="ano" id="">
                                <option value=""></option>
                                <option value="1990">1990-</option>
                                <option value="2000">1990</option>
                                <option value="2010">2000</option>
                                <option value="2020">2010</option>
                                <option value="2030">2020+</option>
                            </select>

                            <p>Duração:</p>
                            <select name="duracao" id="">
                                <option value=""></option>
                                <option value="0|105">1h30</option>
                                <option value="106|130">2h</option>
                                <option value="131|250">2h30+</option>
                            </select>

                            <p>Nacionalidade:</p>
                            <select name="nacionalidade" id="">
                                <option value=""></option>
                                <option value="Brasil">Brasil</option>
                                <option value="EUA">Estados Unidos</option>
                                <option value="Reino Unido">Reino Unido</option>
                                <option value="Japão">Japão</option>
                                <option value="Espanha">Espanha</option>
                                <option value="Irlanda">Irlanda</option>
                            </select>
                        </div>
                    </div>


                </div>

                <div class="fim">
                    <button type="submit" name="indicar">Indique-me!</button>
                    <button type="submit" name="reset" class="button_limpar">Limpar</button>
                </div>

            </div>

            <div class="view_filme">
                <?php if ($filme_escolhido != null && !$nao_encontrado): ?>
                    <h1>Aqui está seu filme!</h1>
                    <div class="poster">
                        <img src="<?php echo $filme_escolhido->poster; ?>" alt="">
                    </div>
                    <div class="info_filme">
                        <div class="info_principal">
                            <h3>
                                <?php echo $filme_escolhido->titulo; ?>
                            </h3>
                            <p class="classificacao">
                                <?php echo $filme_escolhido->classificacao; ?>
                            </p>
                            <p class="duracao">
                                <?php echo $filme_escolhido->duracao; ?>min
                            </p>
                            <p class="genero">
                                <?php echo $filme_escolhido->genero; ?>
                            </p>
                            <p class="ano">
                                <?php echo $filme_escolhido->ano; ?>
                            </p>
                        </div>

                        <div class="info_sinopse">
                            <p>
                                <?php echo $filme_escolhido->sinopse; ?>
                            </p>
                        </div>

                        <div class="info_disponibilidade">
                            <p>Disponivel em:
                                <?php echo $filme_escolhido->disponibilidade; ?>
                            </p>
                        </div>

                        <div class="feedback">
                            <form action="" method="post">
                                <button type="submit" name="like" value="<?php echo $filme_escolhido->_id; ?>"
                                    <?= $filme_like ? "style='background: green;'" : ""; ?>>
                                    <img class="like" src="img/like.png" alt="like">
                                </button>
                                <button type="submit" name="deslike" value="<?php echo $filme_escolhido->_id; ?>"
                                    class="deslike" <?= $filme_deslike ? "style='background: red;'" : ""; ?>><img
                                        src="img/deslike.png" alt="deslike"></button>
                                <button type="submit" name="watchlist" value="<?php echo $filme_escolhido->_id; ?>"
                                    class="watchlist" <?= $filme_watchlist ? "style='background: #2828fd;'" : ""; ?>>Watchlist</button>
                            </form>

                        </div>

                    </div>
                <?php elseif ($nao_encontrado): ?>
                    <h1>Filme não encontrado!</h1>
                    <div class="view_filme view_filme_tablet">
                        <img src="img/png_logo.png" alt="">
                    </div>
                <?php else: ?>
                    <h1>Seu filme aparecerá aqui!</h1>
                    <div class="view_filme view_filme_tablet">
                        <img src="img/png_logo.png" alt="">
                    </div>
                <?php endif ?>
            </div>
        </div>
    </form>

</body>

</html>