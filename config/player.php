<?php

//TODO: find a better way to do this
declare(strict_types=1);

return [
    /*
    | Connexion DB pour le modèle Player (table profiles, schéma public en prod).
    |
    | Non défini : utilisation de la connexion déclarée sur le modèle (pgsql_public).
    | En tests SQLite, définir PLAYER_PROFILES_CONNECTION sur le nom de la connexion
    | par défaut (souvent "sqlite") pour un seul handle PDO et éviter les verrous.
    */
    'profiles_connection' => env('PLAYER_PROFILES_CONNECTION'),
];
