<?php
declare(strict_types=1);

const APP_NAME = 'Зоопарк';
const DB_HOST = 'localhost';
const DB_NAME = 'zoo_course';
const DB_USER = 'root';
const DB_PASS = '';
const DB_CHARSET = 'utf8mb4';

const MAIL_FROM = 'noreply@zoo-course.local';
const UPLOAD_DIR = __DIR__ . '/../uploads/';

$documentRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? __DIR__ . '/..') ?: '');
$projectRoot = str_replace('\\', '/', realpath(__DIR__ . '/..') ?: '');
$baseUrl = '';
if ($documentRoot !== '' && str_starts_with($projectRoot, $documentRoot)) {
    $baseUrl = substr($projectRoot, strlen($documentRoot));
}
define('BASE_URL', rtrim($baseUrl, '/'));
define('UPLOAD_WEB', BASE_URL . '/uploads/');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Europe/Moscow');
