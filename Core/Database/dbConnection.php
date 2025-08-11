<?php

    class dbConnection {
        public static function connect() {
                try {
        $pdo = new PDO('mysql:host=localhost;
        dbname=project_soul',
         'root',
          '');
    } catch (PDOException $e) {
        echo 'Connection failed: ' . $e->getMessage();
    }
            return $pdo;
        }
    }