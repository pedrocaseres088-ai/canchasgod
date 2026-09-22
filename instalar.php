<?php
/* ============================================================
   INSTALADOR — se usa UNA sola vez.
   Entrá a  https://TU-DOMINIO/instalar.php
   Crea las tablas, las 2 sedes y los usuarios del panel.
   Se puede correr de nuevo sin romper nada (no duplica datos).
   DESPUÉS DE USARLO, BORRÁ ESTE ARCHIVO DEL REPO.
   ============================================================ */

require __DIR__ . '/conexion.php';
header('Content-Type: text/html; charset=utf-8');

$log = [];
function ok($m)  { global $log; $log[] = "✅ $m"; }
function inf($m) { global $log; $log[] = "ℹ️ $m"; }

try {
    $pdo->exec("SET NAMES utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS sedes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(60) NOT NULL,
        direccion VARCHAR(160) NOT NULL,
        orden INT NOT NULL DEFAULT 0,
        tipos_cancha VARCHAR(160) NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS turnos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sede_id INT NOT NULL,
        fecha DATE NOT NULL,
        hora_inicio TIME NOT NULL,
        hora_fin TIME NOT NULL,
        cancha VARCHAR(40) NOT NULL DEFAULT 'Cancha 1',
        tipo_cancha VARCHAR(30) NULL,
        cliente VARCHAR(80) NULL,
        estado ENUM('reservado','libre') NOT NULL DEFAULT 'reservado',
        FOREIGN KEY (sede_id) REFERENCES sedes(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario VARCHAR(40) NOT NULL UNIQUE,
        clave_hash VARCHAR(255) NOT NULL,
        rol ENUM('admin','recepcion') NOT NULL DEFAULT 'recepcion'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    ok('Tablas creadas (o ya existían)');

    // Por si las tablas venían de una versión anterior sin las columnas nuevas
    if (!$pdo->query("SHOW COLUMNS FROM sedes LIKE 'tipos_cancha'")->fetch()) {
        $pdo->exec("ALTER TABLE sedes ADD COLUMN tipos_cancha VARCHAR(160) NULL");
    }
    if (!$pdo->query("SHOW COLUMNS FROM turnos LIKE 'tipo_cancha'")->fetch()) {
        $pdo->exec("ALTER TABLE turnos ADD COLUMN tipo_cancha VARCHAR(30) NULL AFTER cancha");
    }

    // Sedes
    if ((int)$pdo->query("SELECT COUNT(*) FROM sedes")->fetchColumn() === 0) {
        $pdo->exec("INSERT INTO sedes (nombre, direccion, orden, tipos_cancha) VALUES
            ('Sede 1', 'San Leonardo Murialdo 909', 1, NULL),
            ('Sede 2', 'La Cautiva 7654', 2, 'Fútbol 5,Fútbol 7,Fútbol 11')");
        ok('Sedes cargadas: San Leonardo Murialdo 909 y La Cautiva 7654');
    } else {
        $pdo->exec("UPDATE sedes SET tipos_cancha = 'Fútbol 5,Fútbol 7,Fútbol 11'
                    WHERE direccion LIKE '%Cautiva%' AND (tipos_cancha IS NULL OR tipos_cancha = '')");
        inf('Las sedes ya existían (no se duplicaron)');
    }

    // Usuarios
    $usuarios = [
        ['admin',     'admin123',     'admin'],
        ['recepcion', 'recepcion123', 'recepcion'],
    ];
    $existe = $pdo->prepare("SELECT 1 FROM usuarios WHERE usuario = ?");
    $crear  = $pdo->prepare("INSERT INTO usuarios (usuario, clave_hash, rol) VALUES (?,?,?)");
    foreach ($usuarios as [$u, $p, $r]) {
        $existe->execute([$u]);
        if ($existe->fetch()) {
            inf("El usuario <b>$u</b> ya existía (no se tocó su contraseña)");
        } else {
            $crear->execute([$u, password_hash($p, PASSWORD_DEFAULT), $r]);
            ok("Usuario creado: <b>$u</b> / <b>$p</b>");
        }
    }

    $log[] = '<br><b>¡Listo!</b> Entrá al panel: <a href="/admin/">/admin/</a> — y después <b>borrá instalar.php</b> del repo.';

} catch (Throwable $e) {
    http_response_code(500);
    $log[] = '❌ Error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"><title>Instalación · Turin Sport</title>
<style>body{font-family:system-ui,sans-serif;background:#0d0f14;color:#f2f5fa;padding:2rem;line-height:1.9}
a{color:#f2a81d}</style></head>
<body><h2>Instalación Turin Sport</h2><p><?= implode('<br>', $log) ?></p></body></html>
