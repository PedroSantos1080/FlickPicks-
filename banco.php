<?php
include_once('config.php'); //Conexão com o Banco de Dados MongoDB


//Função responsável pela criação de todo usuário novo.
function criar_user($client, $username, $email, $name, $senha)
{
    $collection = $client->Recomendador->Usuarios; //Acessa o caminho da minha coleção, database e o documento em específico.

     //Verifica os campos do documento com os campos digitados no site.
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

//Função responsável pelo login de usuários já cadastrados.
function logar_user($client, $email, $senha)
{
    $collection = $client->Recomendador->Usuarios; //Acessa o caminho da minha coleção, database e o documento em específico.

    $verif = $collection->findOne(array("email" => $email, "senha" => md5($senha)));//Compara os campos do documento com os dados inseridos no site.

    if ($verif) {
        // O usuário existente.
        session_start(); //Inicia a sessão do usuário no site.
        $_SESSION['id'] = $verif->_id;
        header('Location: sistema.php');
    } else {
        // O usuário não existe.
        session_start();
        unset($_SESSION['id']);
        echo "<p class='user_registrado'>Email ou Senha incorretos!</p>";
    }
}

//Função que faz todo o filtro dos filmes, passando os devidos parametos para o servidor e retornando para o usuário.
function filtro($client, $genero, $classificacao, $ano, $duracao, $disponibilidade, $nacionalidade)
{
    $filme = $client->Recomendador->Usuario_filme; //Acessa o caminho da minha coleção, database e o documento em específico.
    $collection = $client->Recomendador->filmes1; //Acessa o caminho da minha coleção, database e o documento em específico.
    $collection_watchlist = $client->Recomendador->Usuario_watchlist; //Acessa o caminho da minha coleção, database e o documento em específico.


    //A partir daqui, é feito os vários tipos de filtros, como o de genêro, classificação, ano, duração, disponibilidade e nacionalidade.

    $duracao_filtro = [];
    $anoInicial = $ano; 
    $anoFinal = $ano - 9;
    $pattern = $disponibilidade;
    $regex = new MongoDB\BSON\Regex($pattern, 'i'); //'i' indica que a busca é case-insensitive - Permite pesquisar filmes pela disponibilidade.
    
    // Define um critério inicial para a ao consulta MongoDB
    //Comparada todos os codumentos do banco de dados com o que o usuário selecionou. 


    $criterio = [
        '$match' => [
            'genero' => $genero,
            'classificacao' => $classificacao,
            'nacionalidade' => $nacionalidade,
            'disponibilidade' => ['$regex' => $regex]
        ]
        ];

    
    if (strlen($duracao) > 0 && strlen($ano) > 0) { //Verifica se o usuário especificou duração e o ano.
        $criterio['$match']['$and'] = [];//Adiciona a condição "and" no critério, para que possa ser adicionado uma nova pesquisa no banco.
    }

    if ($ano > 0) { //Verifica se o ano foi especificado.
        $ano_filtro = [ //Adiciona novos critérios para a comparação e busca dos dados no banco.
            '$or' => [
                ['ano' => $ano], //Filtra por ano igual.
                ['ano' => $anoFinal], //Filtra por ano igual a $anoFinal.
                [
                    '$and' => [
                        ['ano' => ['$gt' => $anoFinal]], //Filtra por anos maiores que $anoFinal.
                        ['ano' => ['$lt' => $anoInicial]] //Filtra por anos menores que $anoInicial.
                    ]
                ]
            ]
        ];

        $criterio['$match']['$and'][] = $ano_filtro; //Adiciona o novo critério de busca ao vetor $criterio.
    }

    //Verifica se o usuário especificou a duração
    if (strlen($duracao) > 0) {
        $duracao_vetor = explode("|", $duracao); //Devide a duração em um array
        //(cada campo de duração possui dois valores separados por "|", indicando a minutagem correspondente)

        $duracao_filtro = [ //Adiciona novos critérios para a comparação e busca dos dados no banco.
            '$or' => [
                ['duracao' => ['$gte' => intval($duracao_vetor[0]), '$lt' => intval($duracao_vetor[1])]],
            ]
        ];
        $criterio['$match']['$and'][] = $duracao_filtro; //Adiciona o novo critério de busca a vetor $criterio.
    }

    //Remove critérios vazios para otimizar a consulta no banco. 
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

    // Define o critério de seleção aleatória.
    $sample = ['$sample' => ['size' => 1]]; // Seleciona um documento aleatório.

    // Executa a pipeline de agregação.
    $result = $collection->aggregate([$criterio, $sample]);
    $result_array = $result->toArray();

    //Verifica se não há resultados.
    if (count($result_array) == 0) {
        //Retorna true se nenhum filme foi encontrado.
        return true;
    }

    //Obtém o primeir documento do resultado.
    $randomMovie = $result_array[0];

    // Redireciona o usuário para a página do filme.
    header('location: ?filme='.$randomMovie->_id);
    die;  
}

//Função responsável por associar ID dos filmes ao perfil de um usuário em especifico.
function filme_info($client,$id_filme) {

    $filme = $client->Recomendador->Usuario_filme; //Acessa o caminho da minha coleção, database e o documento em específico.
    $collection = $client->Recomendador->filmes1; //Acessa o caminho da minha coleção, database e o documento em específico.
    $collection_watchlist = $client->Recomendador->Usuario_watchlist; //Acessa o caminho da minha coleção, database e o documento em específico.

    $id_user = $_SESSION["id"]; //Obtém o ID do usuário da sessão.
    $id_filme_obj = new MongoDB\BSON\ObjectId($id_filme); //Converte a string do ID do filme em um objeto ObjectId do MongoDB.
    $movie = $collection->findOne(array("_id" => $id_filme_obj)); //Procura o filme na coleção principal com base no ID do filme.
    $filme_user = $filme->findOne(array("id_usuario" => $id_user, "id_filme" => $id_filme_obj)); //Procura o filme associado ao usuário na coleção de filmes do usuário.

    $filme_watchlist = $collection_watchlist->findOne(array("id_usuario" => $id_user, "id_filme" => $id_filme_obj));  // Procura o filme na watchlist do usuário.

    return [$movie, $filme_user, $filme_watchlist];//Retorna um array contendo informações sobre o filme.
}


//Função para definir um filme como "gostei" ou "não gostei".
function like_deslike($client, $id_filme, $like)
{

    $collection = $client->Recomendador->Usuario_filme; //Acessa o caminho da minha coleção, database e o documento em específico.

    $id_user = $_SESSION["id"]; //Obtém o ID do usuário da sessão.
    $id_filme_obj = new MongoDB\BSON\ObjectId($id_filme); // Converte a string do ID do filme em um objeto ObjectId do MongoDB.

    // Procura um registro na coleção com base no ID do usuário e no ID do filme.
    $filme = $collection->findOne(array("id_usuario" => $id_user, "id_filme" => $id_filme_obj));

    if ($filme) {
        //O registro já existe.
        //Remove o registro existente com base nos critérios fornecidos.
        $collection->deleteOne(
            array("id_usuario" => $id_user, "id_filme" => $id_filme_obj),
            // Critérios para encontrar o documento
            array('$set' => array('like' => $like, 'deslike' => !$like)) // Atualizações a serem aplicadas
        );

    } else {
        //O registro não existe.
        //Insere um novo registro na coleção com informações sobre o filme e a ação do usuário.
        $insertOneResult = $collection->insertOne([
            'id_usuario' => $id_user,
            'id_filme' => $id_filme_obj,
            'data' => date("Y-m-d"),
            'like' => $like,
            'deslike' => !$like
        ]);
    }
}

//Função para adionar filmes a Watchlist do usuário em seu perfil.
function watchlist($client, $id_filme)
{
    $collection = $client->Recomendador->Usuario_watchlist;

    $id_user = $_SESSION["id"];

    $id_filme_obj = new MongoDB\BSON\ObjectId($id_filme);

    $filme = $collection->findOne(array("id_usuario" => $id_user, "id_filme" => $id_filme_obj));
 

    if ($filme) {
        //O filme já existe.
        $collection->deleteOne(
            array("id_usuario" => $id_user, "id_filme" => $id_filme_obj),
            //Critérios para encontrar o documento e excluir do perfil do usuário.
        );

    } else {
        //O filme não existe.
        //Adiciona o filme ao perfil do usuário.
        $insertOneResult = $collection->insertOne([
            'id_usuario' => $id_user,
            'id_filme' => $id_filme_obj,
            'data' => date("Y-m-d")
        ]);
    }
}

//Função que pega a Watchlist do usuário.
function pegar_watchlist($client)
{
    //Acessa as coleções no banco de dados MongoDB.
    $collection = $client->Recomendador->Usuario_watchlist;
    $collection2 = $client->Recomendador->filmes1;

    $id_user = $_SESSION["id"]; //Obtém o ID do usuário da sessão.

    //Realiza uma pesquisa na coleção de watchlist com base no ID do usuário.
    $busca = $collection->find(['id_usuario' => $id_user]);

    $filmes = []; //Inicializa um array para armazenar os filmes da watchlist.
    foreach ($busca as $filme_watchlist) {
        $id = new MongoDB\BSON\ObjectId($filme_watchlist->id_filme); //Converte o ID do filme da watchlist em um objeto ObjectId do MongoDB.
        $filme = $collection2->findOne(['_id' => $id]); //Procura o filme correspondente na coleção principal de filmes com base no ID.
        $filmes[] = $filme; //Adiciona o filme encontrado ao array de filmes.
    }
    return $filmes;
}

//Função para exibir o nome do
function exibir_user($client)
{
    $collection = $client->Recomendador->Usuarios;
    $id_user = $_SESSION["id"];
    $usuario = $collection->findOne(array('_id' => $id_user));

    if ($usuario) {
        $nome = $usuario->username; //Faz a pesquisa usando o ID para encontrar o username.
        echo $nome; //Exibe.
    } else {
        echo "Algo de errado aconteceu!... ):";
    }
}

//Função para exibir quantos filmes o usuário adicionou em sua Watchlist.
function exibir_recomendacoes($client)
{
    $collection = $client->Recomendador->Usuario_watchlist;
    $collection2 = $client->Recomendador->filmes1;

    $id_user = $_SESSION["id"];

    $busca = $collection->find(['id_usuario' => $id_user]);

    $totalFilmes = 0; // Inicializa a variável de contagem de filmes

    foreach ($busca as $filme_watchlist) {
        $id = new MongoDB\BSON\ObjectId($filme_watchlist->id_filme);
        $filme = $collection2->find(['_id' => $id]); //Realiza uma pesquisa na coleção principal de filmes com base no ID do filme.
        $totalFilmes += iterator_count($filme); //Obtém o número total de documentos (filmes) na pesquisa atual.
    }

    echo $totalFilmes; //Exibe no perfil do usuário.
}

//Função para exibir quantos likes e deslikes o usuário possui em sua conta. 
function exibir_like_deslike($client)
{
    $collection = $client->Recomendador->Usuario_filme;

    $id_user = $_SESSION["id"];

    $busca = $collection->find(['id_usuario' => $id_user]);

    //Inicializa a variável de contagem de filmes.
    $totalLike = 0; 
    $totalDeslike = 0;

    foreach ($busca as $filme_watchlist) {
        $totalLike = $filme_watchlist->like ? $totalLike + 1 : $totalLike; //Faz a contagem dos likes.
        $totalDeslike = $filme_watchlist->deslike ? $totalDeslike + 1 : $totalDeslike; //Faz a contagem dos deslikes.
        
    }

    return [$totalLike, $totalDeslike]; //Retorna os valores.
}

// Limpa a URL e redireciona para a página principal
function limparURL() {
    // Obtém a URL base
    $url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
    
    // Redireciona para a URL base
    header('Location: ' . $url);
    exit();
}