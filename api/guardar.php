<?php
/* Crea o edita un turno. Solo admin y recepción. */

require __DIR__ . '/../auth.php';
require __DIR__ . '/../conexion.php';
requerir_rol(['admin', 'recepcion']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Método no permitido');
}

function rechazar($msg) {
    http_response_code(400);
    die($msg);
}

$id      = $_POST['id'] ?? '';
$sede_id = (int)($_POST['sede_id'] ?? 0);
$fecha   = $_POST['fecha'] ?? date('Y-m-d');
$inicio  = $_POST['hora_inicio'] ?? '';
$fin     = $_POST['hora_fin'] ?? '';
$tipo    = trim($_POST['tipo_cancha'] ?? '');
$cliente = trim($_POST['cliente'] ?? '');
$estado  = (($_POST['estado'] ?? 'reservado') === 'libre') ? 'libre' : 'reservado';

// Si está libre, no guardamos nombre de cliente.
if ($estado === 'libre') {
    $cliente = null;
} elseif ($cliente === '') {
    $cliente = 'Reservado';
}

if (!$sede_id || !$inicio || !$fin) {
    rechazar('Faltan datos obligatorios.');
}

// La fecha tiene que existir de verdad (no solo "tener forma" de fecha).
if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $fecha, $m) || !checkdate((int)$m[2], (int)$m[3], (int)$m[1])) {
    rechazar('Fecha inválida.');
}

// Solo horas en punto (ej. 13:00 a 14:00), y el fin después del inicio.
if (!preg_match('/^\d{2}:00$/', $inicio) || !preg_match('/^\d{2}:00$/', $fin)
    || (int)$fin <= (int)$inicio || (int)$fin > 24 || (int)$inicio > 23) {
    rechazar('Horario inválido: elegí horas en punto y que "Hasta" sea después de "Desde".');
}

// Largo máximo de los textos (coincide con las columnas de la base).
if (mb_strlen((string)$cliente) > 80) {
    rechazar('El nombre del cliente no puede superar los 80 caracteres.');
}

// La sede tiene que existir.
$st = $pdo->prepare("SELECT tipos_cancha FROM sedes WHERE id = ?");
$st->execute([$sede_id]);
$sede = $st->fetch(PDO::FETCH_NUM);
if (!$sede) {
    rechazar('La sede elegida no existe.');
}

// Tipo de cancha: la sede define cuáles tiene (Sede 1: Fútbol 5; Sede 2: Fútbol 5, 7 y 11).
$tiposSede = array_filter(array_map('trim', explode(',', (string)$sede[0])));
if ($tiposSede) {
    if (!in_array($tipo, $tiposSede, true)) {
        rechazar('Elegí el tipo de cancha para esta sede.');
    }
} else {
    $tipo = null;
}

// Nota: la columna "cancha" quedó en la base solo por compatibilidad con
// datos viejos; ya no se carga desde el panel (se guarda vacía).
if ($id !== '' && ctype_digit((string)$id)) {
    $stmt = $pdo->prepare(
        "UPDATE turnos SET sede_id=?, fecha=?, hora_inicio=?, hora_fin=?, tipo_cancha=?, cliente=?, estado=?
         WHERE id=?"
    );
    $stmt->execute([$sede_id, $fecha, $inicio, $fin, $tipo, $cliente, $estado, (int)$id]);
} else {
    $stmt = $pdo->prepare(
        "INSERT INTO turnos (sede_id, fecha, hora_inicio, hora_fin, cancha, tipo_cancha, cliente, estado)
         VALUES (?,?,?,?,?,?,?,?)"
    );
    $stmt->execute([$sede_id, $fecha, $inicio, $fin, '', $tipo, $cliente, $estado]);
}

header('Location: /admin/index.php?fecha=' . urlencode($fecha) . '&ok=1');
