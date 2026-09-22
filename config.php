<?php
/* ============================================================
   CONFIGURACIÓN — versión para RAILWAY
   ------------------------------------------------------------
   En Railway NO se ponen los datos a mano: se leen solos desde
   las variables de entorno que crea el servicio MySQL.
   (Ver LEEME_RAILWAY.md → paso "Conectar variables".)
   Igual funciona en cualquier hosting con variables tipo DB_HOST.
   ============================================================ */

if (!function_exists('_env')) {
function _env($claves, $def = null) {
    foreach ((array)$claves as $k) {
        $v = getenv($k);
        if ($v !== false && $v !== '') return $v;
    }
    return $def;
}
}

$host = $port = $user = $pass = $db = null;

// Opción A: una sola URL (MYSQL_URL / DATABASE_URL)
$url = _env(['MYSQL_URL', 'DATABASE_URL']);
if ($url) {
    $p    = parse_url($url);
    $host = $p['host'] ?? null;
    $port = $p['port'] ?? null;
    $user = $p['user'] ?? null;
    $pass = $p['pass'] ?? null;
    $db   = isset($p['path']) ? ltrim($p['path'], '/') : null;
}

// Opción B: variables sueltas (las que da Railway)
$host = $host ?: _env(['MYSQLHOST', 'DB_HOST'], 'localhost');
$port = $port ?: _env(['MYSQLPORT', 'DB_PORT'], 3306);
$user = $user ?: _env(['MYSQLUSER', 'DB_USER'], 'root');
$pass = ($pass !== null) ? $pass : _env(['MYSQLPASSWORD', 'DB_PASSWORD'], '');
$db   = $db   ?: _env(['MYSQLDATABASE', 'DB_NAME'], 'railway');

return [
    'db' => [
        'host'    => $host,
        'port'    => $port,
        'nombre'  => $db,
        'usuario' => $user,
        'clave'   => $pass,
    ],
    'zona_horaria' => 'America/Argentina/Buenos_Aires',

    // Rango de horarios que se ofrecen en el panel (solo horas en punto).
    // Ej: 8 = 08:00 es el primer turno; 24 = el último turno termina a las 24:00.
    'hora_apertura' => 8,
    'hora_cierre'   => 24,
];
