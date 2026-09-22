<?php
/* ============================================================
   API pública que lee la PANTALLA (TV).
   Devuelve los turnos del día en formato JSON.
   Ejemplo:  api/turnos.php?fecha=hoy   o   api/turnos.php?fecha=2026-07-01
   ============================================================ */

require __DIR__ . '/../conexion.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');  // por si la pantalla corre en otro dominio

$fecha = $_GET['fecha'] ?? 'hoy';
if ($fecha === 'hoy' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    $fecha = date('Y-m-d');
}

try {
    $sedes = $pdo->query("SELECT id, nombre, direccion FROM sedes ORDER BY orden, id")->fetchAll();

    $stmt = $pdo->prepare(
        "SELECT hora_inicio, hora_fin, cancha, tipo_cancha, cliente, estado
         FROM turnos WHERE sede_id = ? AND fecha = ?
         ORDER BY hora_inicio"
    );

    $salida = [];
    foreach ($sedes as $s) {
        $stmt->execute([$s['id'], $fecha]);
        $turnos = array_map(function ($t) {
            return [
                'inicio'  => substr($t['hora_inicio'], 0, 5),
                'fin'     => substr($t['hora_fin'], 0, 5),
                'cancha'  => $t['cancha'],
                'tipo'    => $t['tipo_cancha'],
                'cliente' => $t['cliente'],
                'estado'  => $t['estado'],
            ];
        }, $stmt->fetchAll());

        $salida[] = [
            'nombre'    => $s['nombre'],
            'direccion' => $s['direccion'],
            'turnos'    => $turnos,
        ];
    }

    echo json_encode(['fecha' => $fecha, 'sedes' => $salida], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'No se pudieron leer los turnos'], JSON_UNESCAPED_UNICODE);
}
