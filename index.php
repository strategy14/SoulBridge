<?php
session_start();
require_once 'Core/bootstrap.php';

    $request = new Request();
    $routes->handleRequest($request);
?>
