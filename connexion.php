<?php
try {
    $bdd = new PDO('mysql:host=localhost;dbname=skydiary', 'root', '', array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
} catch (PDOException $e) {
    echo $e->getMessage();
}
