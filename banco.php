<?php
require 'vendor/autoload.php';
use MongoDB\Client;
use MongoDB\Driver\ServerApi;

// Replace the placeholder with your Atlas connection string
$uri = 'mongodb+srv://Pedro_Henrique:fantaLARANJA&sucoTANGUE16@cluster0.bvfdckq.mongodb.net/?retryWrites=true&w=majority';

// Specify Stable API version 1
$apiVersion = new ServerApi(ServerApi::V1);

// Create a new client and connect to the server
$client = new MongoDB\Client($uri, [], ['serverApi' => $apiVersion]);
/*$retorno = "";
try {
    // Send a ping to confirm a successful connection
    $client->selectDatabase('admin')->command(['ping' => 1]);
    $retorno = "Pinged your deployment. You successfully connected to MongoDB!\n";
} catch (Exception $e) {
    printf($e->getMessage());
}*/




function criar_user($client, $username, $email, $name, $senha) {
    $collection = $client->cluster0->Usuarios;

    $user = $collection->findOne(array("username" => $username));
    if ($user) {
        // O usuário já existe.
        print "<h1>Usuário já registrado!</h1>";
    } else {
        // O usuário não existe.
        print "<h1>Usuário registrado com sucesso!<h1>";
        $insertOneResult = $collection->insertOne([
            'username' => $username,
            'email' => $email,
            'name' => $name,
            'senha' => md5($senha)
        ]);
        //header('Location: confirmacao.html');
    
    
        $id_user = $insertOneResult->getInsertedId();//pega o id do usuario inserido. (Colocar return no começo para funcionar)
    }





}

