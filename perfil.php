<?php
session_start();
include_once('config.php');
include('banco.php');

if ((!isset($_SESSION['id']))) {
    header('Location: login.php');
    return;
}

$logado = $_SESSION['id'];

$watchlist = pegar_watchlist($client);

$like_deslike = exibir_like_deslike($client);
$like = $like_deslike[0];
$deslike = $like_deslike[1];

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/estilo_perfil.css">
    <link rel="shortcut icon" href="img/png_logo.ico" type="image/x-icon">
    <title>FlickPicks - Perfil</title>
</head>

<body>
    <!--------------------------------------------------MENU-------------------------------------------------->
    <header>
        <div class="container_menu">
            <div class="logo"><img src="img/png_logo.png" alt="">
                <h1 class="title_rec">FLICKPICKS</h1>
            </div>
            <div class="social" id="social">
                <nav>
                    <ul>
                        <li><a href="sistema.php"><img class="img_perfil" src="img/volte.png" alt=""></a></li>
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
                <div class="perfil">
                    <div class="image_perfil">
                        <img class="image" id="imagem-preview" src="" alt="">
                    </div>
                    <select class="select-perfil" id="imagem-selecionada" onchange="selecionarImagem()">
                        <option value=""></option>
                        <option value="img/red.png">Red</option>
                        <option value="img/blue.png">Blue</option>
                        <option value="img/green.png">Green</option>
                    </select>
                </div>

                <h1 class="nome_user">
                    <?php exibir_user($client); ?>
                </h1>
            </div>

            <div class="view_filme">
                <div class="info_perfil">
                    <h3>
                        <?php exibir_recomendacoes($client); ?>
                    </h3>
                    <p>recomendações</p>
                </div>

                <div class="info_perfil">
                    <h3>
                        <?php echo $like; ?>
                    </h3>
                    <p>curtidos</p>
                </div>

                <div class="info_perfil">
                    <h3>
                        <?php echo $deslike; ?>
                    </h3>
                    <p>não curtido</p>
                </div>

            </div>

            <div class="watchlist">

                <h1 class="title_wt">Watchlist <span class="Dica">? <div class="DicaTexto">Para adicionar filmes a sua Watchlist, use nosso recomendador de filmes na tela principal! Para remover, basta clicar no filme desejado, que logo em seguida você sera redirecionado para o filme selecionado.</div></span> </h1>
                <div class="filmes_wt">
                    <?php if ($watchlist): ?>
                        <div class="carousel">
                            <div class="arrow_prev arrow prev">&#8249;</div>
                            <div class="carousel-container">
                                <?php foreach ($watchlist as $filme): ?>
                                    <img class="img_posters" src="<?php echo $filme->poster; ?>" style="width: 150px;" alt=""
                                        onclick="redirectToFilme('<?php echo $filme->_id; ?>')">
                                <?php endforeach; ?>
                            </div>
                            <div class="arrow_next arrow next">&#8250;</div>
                        </div>

                    <?php else: ?>
                        <div class="carousel-container">
                            <h1 class="sem_whatchlist">Não há nada para ver aqui ainda /: <br> Adicione alguns filmes!</h1>
                        </div>
                    <?php endif ?>

                </div>


            </div>


        </div>


    </form>

    <script>
        function redirectToFilme(filmeId) {
            window.location.href = 'sistema.php?filme=' + filmeId;
        }
        const prevArrow = document.querySelector('.arrow.prev');
        const nextArrow = document.querySelector('.arrow.next');

        prevArrow.addEventListener('click', prev);
        nextArrow.addEventListener('click', next);

        // Resto do código JavaScript do carousel anterior...



        const carousel = document.querySelector('.carousel');
        const container = document.querySelector('.carousel-container');

        // Função para avançar o carousel
        function next() {
            container.style.transform += 'translateX(-50%)';
        }

        // Função para retroceder o carousel
        function prev() {
            container.style.transform = 'translateX(0)';
        }

        carousel.addEventListener('mouseenter', () => {
            carousel.classList.add('paused');
        });

        carousel.addEventListener('mouseleave', () => {
            carousel.classList.remove('paused');
        });

        carousel.addEventListener('transitionend', () => {
            if (!carousel.classList.contains('paused')) {
                if (container.style.transform === 'translateX(-100%)') {
                    container.appendChild(container.firstElementChild);
                    container.style.transform = 'translateX(0)';
                }
            }
        });

    </script>

</body>

</html>