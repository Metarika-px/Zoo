<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

function mail_template(string $title, string $body): string
{
    return '<!doctype html><html lang="ru"><head><meta charset="utf-8"><title>' . e($title) . '</title></head>'
        . '<body style="margin:0;background:#070b12;color:#f8fafc;font-family:Arial,sans-serif;">'
        . '<div style="max-width:680px;margin:0 auto;padding:28px;">'
        . '<div style="background:#101826;border:1px solid #263244;border-radius:8px;padding:24px;">'
        . '<h1 style="margin:0 0 16px;color:#19c37d;font-size:24px;">' . e($title) . '</h1>'
        . '<div style="font-size:16px;line-height:1.6;color:#f8fafc;">' . $body . '</div>'
        . '<p style="margin-top:24px;color:#9aa7b8;font-size:13px;">' . e(APP_NAME) . ' — учебная информационная система</p>'
        . '</div></div></body></html>';
}

function send_app_mail(string $to, string $subject, string $message, bool $isHtml = false): bool
{
    $body = $isHtml ? $message : nl2br(e($message));
    $html = mail_template($subject, $body);
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=utf-8',
        'From: ' . MAIL_FROM,
    ];

    return mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $html, implode("\r\n", $headers));
}