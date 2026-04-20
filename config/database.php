<?php

function getPDO() {

    static $pdo = null; //Static dimuat agar fungsi getPDO() koneksinya tidak dibuat ulang saat dipanggil berkali kali.

    //Jika $pdo masih null, berarti koneksi belum pernah dibuat.
    if ($pdo === null){ 
        $host   = "";
        $port   = "";
        $dbname = "";    //Konfigurasi database postgres SQL
        $user   = "";
        $pass   = "";
        $schema = "";

        //Setelah konfigurasi basis data sudah dibuat, maka $dsn akan dimuat.
        $dsn    = "pgsql:host=$host;port=$port;dbname=$dbname"; //pembuatan dsn (data source name)

        try { //$try untuk membuat koneksi menggunakan PDO yang membutuhkan : DSN, user, pass, array (sebagai opsi tambahan).
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false, //Kata AI jika menggunakan prepared stmt asli PGSQL akan lebih aman.
            ]);

            $pdo->exec("SET search_path TO $schema, public"); //Mengatur schema default.

        } catch (PDOException $e) {
            die("Koneksi database gagal: " . $e->getMessage()); //Jika eror, program berhenti/die dan muncul pesan eror.
        }
    }

    return $pdo; //Menggunakan koneksi yang sama saat fungsi dipanggil
}