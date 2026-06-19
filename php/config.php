<?php

function getConnexion() {
    $host = 'localhost';
    $dbname = 'benault_formulaire'; // Mets ici le vrai nom de ta base
    $user = 'root';
    $password = 'root';

    try {
        $conn = new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
            $user,
            $password
        );

        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return $conn;

    } catch (PDOException $e) {
        die("Erreur de connexion à la base de données.");
    }
}
