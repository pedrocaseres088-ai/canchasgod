<?php
/* Conexión a la base de datos (no hace falta tocar este archivo). */

$cfg = require __DIR__ . '/config.php';

date_default_timezone_set($cfg['zona_horaria'] ?? 'America/Argentina/Buenos_Aires');

try {
    $dsn = "mysql:host={$cfg['db']['host']};port={$cfg['db']['port']};"
         . "dbname={$cfg['db']['nombre']};charset=utf8mb4";
    $pdo = new PDO(
        $dsn,
        $cfg['db']['usuario'],
        $cfg['db']['clave'],
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    die('Error de conexión a la base de datos. Revisá las variables en Railway · ' . $e->getMessage());
}

/* ------------------------------------------------------------
   Migración automática (se aplica sola la primera vez).
   Agrega los "tipos de cancha" sin tener que tocar la base a mano.
   ------------------------------------------------------------ */
try {
    $col = $pdo->query("SHOW COLUMNS FROM sedes LIKE 'tipos_cancha'")->fetch();
    if (!$col) {
        $pdo->exec("ALTER TABLE sedes ADD COLUMN tipos_cancha VARCHAR(160) NULL");
        // Solo La Cautiva tiene distintos tipos de cancha
        $pdo->exec("UPDATE sedes SET tipos_cancha = 'Fútbol 5,Fútbol 7,Fútbol 11'
                    WHERE direccion LIKE '%Cautiva%'");
    }
    $col = $pdo->query("SHOW COLUMNS FROM turnos LIKE 'tipo_cancha'")->fetch();
    if (!$col) {
        $pdo->exec("ALTER TABLE turnos ADD COLUMN tipo_cancha VARCHAR(30) NULL AFTER cancha");
    }
} catch (Throwable $e) {
    // Si falla (ej. tablas todavía no creadas), seguimos sin romper la página.
}

