<?php

$user = 'root';
$pass = '';
$host = '127.0.0.1';
$dbname = 'esma';

try {

    $pdo = new PDO( "mysql:host=$host;dbname=$dbname;charset=utf8", $user,$pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

   } catch (PDOException $e) {

    die("Erreur de connexion : " . $e->getMessage());

}