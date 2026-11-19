<?php
declare(strict_types=1);
function e(?string $valor): string { return htmlspecialchars($valor ?? '', ENT_QUOTES, 'UTF-8'); }
function redirigir(string $ruta): never { header('Location: '.$ruta); exit; }
function csrf_token(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }
function validar_csrf(?string $token): bool { return is_string($token) && hash_equals($_SESSION['csrf'] ?? '', $token); }
function password_cumple_politica(string $password): bool { return strlen($password)>=8 && preg_match('/[A-Z]/',$password) && preg_match('/\d/',$password) && preg_match('/[^A-Za-z0-9]/',$password); }
