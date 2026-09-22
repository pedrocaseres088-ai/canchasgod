<?php
/* Crea o edita un turno. Solo admin y recepción. */

require __DIR__ . '/../auth.php';
require __DIR__ . '/../conexion.php';
requerir_rol(['admin', 'recepcion']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Método no permitido');
}

$id      = $_POST['id'] ?? '';
$sede_id = (int)($_POST['sede_id'] ?? 0);
$fecha   = $_POST['fecha'] ?? date('Y-m-d');
$inicio  = $_POST['hora_inicio'] ?? '';
$fin     = $_POST['hora_fin'] ?? '';
$cancha  = trim($_POST['cancha'] ?? 'Cancha 1');
$tipo    = trim($_POST['tipo_cancha'] ?? '');
$cliente = trim($_POST['cliente'] ?? '');
$estado  = (($_POST['estado'] ?? 'reservado') === 'libre') ? 'libre' : 'reservado';

// Si está libre, no guardamos nombre de cliente.
if ($estado === 'libre') {
    $cliente = null;
} elseif ($cliente === '') {
    $cliente = 'Reservado';
}

if (!$sede_id || !$inicio || !$fin || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    http_response_code(400);
    die('Faltan datos obligatorios.');
}

// Solo horas en punto (ej. 13:00 a 14:00), y el fin después del inicio.
if (!preg_match('/^\d{2}:00$/', $inicio) || !preg_match('/^\d{2}:00$/', $fin)
    || (int)$fin <= (int)$inicio || (int)$fin > 24) {
    http_response_code(400);
    die('Horario inválido: elegí horas en punto y que "Hasta" sea después de "Desde".');
}

// Tipo de cancha: solo se guarda si la sede tiene tipos (La Cautiva).
$st = $pdo->prepare("SELECT tipos_cancha FROM sedes WHERE id = ?");
$st->execute([$sede_id]);
$tiposSede = array_filter(array_map('trim', explode(',', (string)$st->fetchColumn())));
if ($tiposSede) {
    if (!in_array($tipo, $tiposSede, true)) {
        http_response_code(400);
        die('Elegí el tipo de cancha para esta sede.');
    }
} else {
    $tipo = null;
}

if ($id !== '' && ctype_digit((string)$id)) {
    $stmt = $pdo->prepare(
        "UPDATE turnos SET sede_id=?, fecha=?, hora_inicio=?, hora_fin=?, cancha=?, tipo_cancha=?, cliente=?, estado=?
         WHERE id=?"
    );
    $stmt->execute([$sede_id, $fecha, $inicio, $fin, $cancha, $tipo, $cliente, $estado, (int)$id]);
} else {
    $stmt = $pdo->prepare(
        "INSERT INTO turnos (sede_id, fecha, hora_inicio, hora_fin, cancha, tipo_cancha, cliente, estado)
         VALUES (?,?,?,?,?,?,?,?)"
    );
    $stmt->execute([$sede_id, $fecha, $inicio, $fin, $cancha, $tipo, $cliente, $estado]);
}

header('Location: /admin/index.php?fecha=' . urlencode($fecha) . '&ok=1');
