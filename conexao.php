<?php
    // define('HOST', 'auth-db1660.hstgr.io'); 
    // define('USER', 'u182528050_lzstore');
    // define('PASS', 'HQGMUDwFHfOPv4f!');
    // define('BASE', 'u182528050_lzstore');

    define('HOST', 'localhost'); // sem barra no final
    define('USER', 'root');
    define('PASS', '');
    define('BASE', 'loja');

    $conn = new MySQLi(HOST, USER, PASS, BASE);
    // if ($conn->connect_error) {
    //     die("Erro de conexão: " . $conn->connect_error);
    // } else {
    //     echo "Conexão bem-sucedida!";
    // }