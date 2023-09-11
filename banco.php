<?php
include_once('config.php');

function criar_user($client, $username, $email, $name, $senha)
{
    $collection = $client->Recomendador->Usuarios;
    $user = $collection->findOne(
        array(
            '$or' => array(
                array("username" => $username),
                array("email" => $email)
            )
        )
    );

    if ($user) {
        // O usuário já existe.
        echo "<p class='user_registrado'>Usuário já registrado!</p>";
    } else {
        // O usuário não existe.
        echo "<p>Usuário registrado com sucesso!</p>";
        $insertOneResult = $collection->insertOne([
            'username' => $username,
            'email' => $email,
            'name' => $name,
            'senha' => md5($senha)
        ]);

        header('Location: sistema.php');
        $id_user = $insertOneResult->getInsertedId();
    }
}

function logar_user($client, $email, $senha)
{
    $collection = $client->Recomendador->Usuarios;

    $verif = $collection->findOne(array("email" => $email, "senha" => md5($senha)));

    if ($verif) {
        // O usuário existente.
        session_start();
        $_SESSION['id'] = $verif->_id;

        header('Location: sistema.php');
    } else {
        // O usuário não existe.
        session_start();
        unset($_SESSION['id']);
        echo "<p class='user_registrado'>Email ou Senha incorretos!</p>";
    }
}

function filtro($client, $genero, $classificacao, $ano, $duracao, $disponibilidade, $nacionalidade)
{
    $filme = $client->Recomendador->Usuario_filme;
    $collection = $client->Recomendador->filmes1;
    $collection_watchlist = $client->Recomendador->Usuario_watchlist;
    $duracao_filtro = [];

    $anoInicial = $ano; 
    $anoFinal = $ano - 9;
    $pattern = $disponibilidade;
    $regex = new MongoDB\BSON\Regex($pattern, 'i'); //'i' indica que a busca é case-insensitive
    
    $criterio = [
        '$match' => [
            'genero' => $genero,
            'classificacao' => $classificacao,
            'nacionalidade' => $nacionalidade,
            'disponibilidade' => ['$regex' => $regex]
        ]
        ];

    if (strlen($duracao) > 0 && strlen($ano) > 0) {
        $criterio['$match']['$and'] = [];
    }

    if ($ano > 0) {
        $ano_filtro = [
            '$or' => [
                ['ano' => $ano],
                ['ano' => $anoFinal],
                [
                    '$and' => [
                        ['ano' => ['$gt' => $anoFinal]],
                        ['ano' => ['$lt' => $anoInicial]]
                    ]
                ]
            ]
        ];

        $criterio['$match']['$and'][] = $ano_filtro;
    }

    if (strlen($duracao) > 0) {
        $duracao_vetor = explode("|", $duracao);

        $duracao_filtro = [
            '$or' => [
                ['duracao' => ['$gte' => intval($duracao_vetor[0]), '$lt' => intval($duracao_vetor[1])]],
            ]
        ];
        $criterio['$match']['$and'][] = $duracao_filtro;
    }

    if (strlen($genero) == 0) {
        unset($criterio['$match']['genero']);
    }

    if (strlen($classificacao) == 0) {
        unset($criterio['$match']['classificacao']);
    }

    if ($ano == 0) {
        unset($criterio['$match']['ano']);
    }

    if ($duracao == 0) {
        unset($criterio['$match']['duracao']);
    }

    if (strlen($nacionalidade) == 0) {
        unset($criterio['$match']['nacionalidade']);
    }
    
    if (strlen($disponibilidade) == 0) {
        unset($criterio['$match']['disponibilidade']);
    }
    
    if (count($criterio['$match']) == 0) {
        return true;
    }

    // Define the random selection criteria
    $sample = ['$sample' => ['size' => 1]]; // Select one random document

    // Perform the aggregation pipeline
    $result = $collection->aggregate([$criterio, $sample]);
    $result_array = $result->toArray();
    if (count($result_array) == 0) {
        return true;
    }

    // Get the first document from the result
    $randomMovie = $result_array[0];
    header('location: ?filme='.$randomMovie->_id);
    die;  
}

function filme_info($client,$id_filme) {

    $filme = $client->Recomendador->Usuario_filme;
    $collection = $client->Recomendador->filmes1;
    $collection_watchlist = $client->Recomendador->Usuario_watchlist;

    $id_user = $_SESSION["id"];
    $id_filme_obj = new MongoDB\BSON\ObjectId($id_filme);
    $movie = $collection->findOne(array("_id" => $id_filme_obj));
    $filme_user = $filme->findOne(array("id_usuario" => $id_user, "id_filme" => $id_filme_obj));

    $filme_watchlist = $collection_watchlist->findOne(array("id_usuario" => $id_user, "id_filme" => $id_filme_obj));

    return [$movie, $filme_user, $filme_watchlist];
}

//Isso ainda não funciona
function like_deslike($client, $id_filme, $like)
{

    $collection = $client->Recomendador->Usuario_filme;

    $id_user = $_SESSION["id"];
    $id_filme_obj = new MongoDB\BSON\ObjectId($id_filme);

    $filme = $collection->findOne(array("id_usuario" => $id_user, "id_filme" => $id_filme_obj));

    if ($filme) {
        //O registro já existe.
        $collection->deleteOne(
            array("id_usuario" => $id_user, "id_filme" => $id_filme_obj),
            // Critérios para encontrar o documento
            array('$set' => array('like' => $like, 'deslike' => !$like)) // Atualizações a serem aplicadas
        );

    } else {
        //O registro não existe.
        $insertOneResult = $collection->insertOne([
            'id_usuario' => $id_user,
            'id_filme' => $id_filme_obj,
            'data' => date("Y-m-d"),
            'like' => $like,
            'deslike' => !$like
        ]);
    }
}

function watchlist($client, $id_filme)
{
    $collection = $client->Recomendador->Usuario_watchlist;

    $id_user = $_SESSION["id"];

    $id_filme_obj = new MongoDB\BSON\ObjectId($id_filme);

    $filme = $collection->findOne(array("id_usuario" => $id_user, "id_filme" => $id_filme_obj));
    //Isso não funciona
    if ($filme) {
        //O filme já existe.
        $collection->deleteOne(
            array("id_usuario" => $id_user, "id_filme" => $id_filme_obj),
            //Critérios para encontrar o documento
        );

    } else {
        //O filme não existe.
        $insertOneResult = $collection->insertOne([
            'id_usuario' => $id_user,
            'id_filme' => $id_filme_obj,
            'data' => date("Y-m-d")
        ]);
    }
}

function pegar_watchlist($client)
{
    $collection = $client->Recomendador->Usuario_watchlist;
    $collection2 = $client->Recomendador->filmes1;
    $id_user = $_SESSION["id"];

    $busca = $collection->find(['id_usuario' => $id_user]);
    $filmes = [];
    foreach ($busca as $filme_watchlist) {
        $id = new MongoDB\BSON\ObjectId($filme_watchlist->id_filme);
        $filme = $collection2->findOne(['_id' => $id]);
        $filmes[] = $filme;
    }
    return $filmes;
}

function exibir_user($client)
{
    $collection = $client->Recomendador->Usuarios;
    $id_user = $_SESSION["id"];
    $usuario = $collection->findOne(array('_id' => $id_user));

    if ($usuario) {
        $nome = $usuario->username;
        echo $nome;
    } else {
        echo "deu ruim";
    }
}

function exibir_recomendacoes($client)
{
    $collection = $client->Recomendador->Usuario_watchlist;
    $collection2 = $client->Recomendador->filmes1;

    $id_user = $_SESSION["id"];

    $busca = $collection->find(['id_usuario' => $id_user]);

    $totalFilmes = 0; // Inicializa a variável de contagem de filmes

    foreach ($busca as $filme_watchlist) {
        $id = new MongoDB\BSON\ObjectId($filme_watchlist->id_filme);
        $filme = $collection2->find(['_id' => $id]);
        $totalFilmes += iterator_count($filme);
    }

    echo $totalFilmes;
}

//Isso não funciona
function exibir_like_deslike($client)
{
    $collection = $client->Recomendador->Usuario_filme;

    $id_user = $_SESSION["id"];

    $busca = $collection->find(['id_usuario' => $id_user]);

    $totalLike = 0; // Inicializa a variável de contagem de filmes
    $totalDeslike = 0;

    foreach ($busca as $filme_watchlist) {
        $totalLike = $filme_watchlist->like ? $totalLike + 1 : $totalLike;
        $totalDeslike = $filme_watchlist->deslike ? $totalDeslike + 1 : $totalDeslike;
        
    }

    return [$totalLike, $totalDeslike];
}

// Limpa a URL e redireciona para a página principal
function limparURL() {
    // Obtém a URL base
    $url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
    
    // Redireciona para a URL base
    header('Location: ' . $url);
    exit();
}