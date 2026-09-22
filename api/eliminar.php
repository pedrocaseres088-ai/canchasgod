<?php
/* Borra un turno. Solo admin y recepción. */

require __DIR__ . '/../auth.php';
require __DIR__ . '/../conexion.php';
requerir_rol(['admin', 'recepcion']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Método no permitido');
}

$id    = (int)($_POST['id'] ?? 0);
$fecha = $_POST['fecha'] ?? date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    $fecha = date('Y-m-d');
}

if ($id) {
    $pdo->prepare("DELETE FROM turnos WHERE id = ?")->execute([$id]);
}

header('Location: /admin/index.php?fecha=' . urlencode($fecha) . '&del=1');
