<?php
declare(strict_types=1);

const DB_HOST = '127.0.0.1';
const DB_NAME = '3dprinthub_db';
const DB_USER = 'root';
const DB_PASS = '';

// Dán API Key của webhook trên SePay vào giữa hai dấu nháy.
const SEPAY_WEBHOOK_API_KEY = '503d64e1e37aca94973504627a558536c4a966e23f7296351d0f87e5b88692df';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    exit('Không kết nối được MySQL. Hãy Start All trong Laragon và import database/3DPrintHub_DATABASE_ALL_IN_ONE.sql.');
}