<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
require_once("classes/Connection.php");
require_once("classes/Authentication.php");

if (isset($_POST['signUp'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $passwordConfirm = $_POST['passwordConfirm'];

    $connection = new Connection();

    $authentication = new Authentication($connection->pdo);

    $signUp = $authentication->signUp($email, $password, $passwordConfirm);

    var_dump($authentication->errors);
} else {
    header("location:index.php");
}