<?php
/* ============================================================
   PANEL DE ADMINISTRACIÓN
   Cargar / editar / borrar turnos por sede y por día.
   ============================================================ */

require __DIR__ . '/../auth.php';
require __DIR__ . '/../conexion.php';
requerir_login();

// Fecha que estamos gestionando (por defecto, hoy)
$fecha = $_GET['fecha'] ?? date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    $fecha = date('Y-m-d');
}

$sedes = $pdo->query("SELECT id, nombre, direccion, tipos_cancha FROM sedes ORDER BY orden, id")->fetchAll();

// Tipos de cancha por sede (solo La Cautiva los tiene)
$tiposPorSede = [];
foreach ($sedes as $s) {
    $tiposPorSede[$s['id']] = array_values(array_filter(array_map('trim', explode(',', (string)($s['tipos_cancha'] ?? '')))));
}

// Rango de horas del panel
$cfgHoras = require __DIR__ . '/../config.php';
$horaApertura = (int)($cfgHoras['hora_apertura'] ?? 8);
$horaCierre   = (int)($cfgHoras['hora_cierre'] ?? 24);

$q = $pdo->prepare(
    "SELECT * FROM turnos WHERE sede_id = ? AND fecha = ? ORDER BY hora_inicio"
);

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$diasSemana = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
$tsFecha = strtotime($fecha);
$fechaLinda = $diasSemana[(int)date('w', $tsFecha)] . ' ' . date('d/m/Y', $tsFecha);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Panel de turnos · Turin Sport</title>
<link rel="stylesheet" href="estilos.css">
</head>
<body>

<header class="topbar">
  <div class="topbar-brand">TURIN SPORT <span>· Panel de turnos</span></div>
  <div class="topbar-right">
    <a class="btn-ghost" href="/pantalla/index.html" target="_blank">▣ Ver pantalla</a>
    <span class="user-chip"><?= h(usuario_actual()) ?> · <?= h(rol_actual()) ?></span>
    <a class="btn-ghost" href="logout.php">Salir</a>
  </div>
</header>

<main class="wrap">

  <?php if (isset($_GET['ok'])): ?><div class="alerta ok">Turno guardado correctamente.</div><?php endif; ?>
  <?php if (isset($_GET['del'])): ?><div class="alerta ok">Turno eliminado.</div><?php endif; ?>

  <!-- Selector de día -->
  <form class="fecha-bar" method="get">
    <label>Día:</label>
    <input type="date" name="fecha" value="<?= h($fecha) ?>" onchange="this.form.submit()">
    <span class="fecha-linda"><?= h($fechaLinda) ?></span>
    <a class="btn-ghost" href="?fecha=<?= date('Y-m-d') ?>">Hoy</a>
  </form>

  <!-- Formulario de alta / edición -->
  <section class="card form-card">
    <h2 id="form-titulo">Nuevo turno</h2>
    <form method="post" action="/api/guardar.php" class="grid-form">
      <input type="hidden" name="id" id="f-id" value="">
      <input type="hidden" name="fecha" value="<?= h($fecha) ?>">

      <div>
        <label>Sede</label>
        <select name="sede_id" id="f-sede" required onchange="toggleTipo()">
          <?php foreach ($sedes as $s): ?>
            <option value="<?= (int)$s['id'] ?>"><?= h($s['nombre']) ?> — <?= h($s['direccion']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label>Desde</label>
        <select name="hora_inicio" id="f-ini" required onchange="actualizarFin(null, true)">
          <?php for ($hh = $horaApertura; $hh < $horaCierre; $hh++): $v = sprintf('%02d:00', $hh); ?>
            <option value="<?= $v ?>"><?= $v ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div>
        <label>Hasta</label>
        <select name="hora_fin" id="f-fin" required></select>
      </div>

      <div>
        <label>Cancha</label>
        <input type="text" name="cancha" id="f-cancha" list="canchas" value="Cancha 1">
        <datalist id="canchas">
          <option>Cancha 1</option><option>Cancha 2</option><option>Cancha 3</option>
        </datalist>
      </div>

      <div id="tipo-box">
        <label>Tipo de cancha</label>
        <select name="tipo_cancha" id="f-tipo"></select>
      </div>

      <div>
        <label>Estado</label>
        <select name="estado" id="f-estado" onchange="toggleCliente()">
          <option value="reservado">Reservado</option>
          <option value="libre">Libre / Disponible</option>
        </select>
      </div>

      <div id="cliente-box">
        <label>Reservado por</label>
        <input type="text" name="cliente" id="f-cliente" placeholder="Nombre o equipo">
      </div>

      <div class="form-actions">
        <button type="submit" class="btn">Guardar turno</button>
        <button type="button" class="btn-ghost" onclick="resetForm()">Limpiar</button>
      </div>
    </form>
  </section>

  <!-- Listado por sede -->
  <?php foreach ($sedes as $s): ?>
    <?php $q->execute([$s['id'], $fecha]); $turnos = $q->fetchAll(); ?>
    <section class="card">
      <h2 class="sede-titulo">
        <span class="pill-sede"><?= h($s['nombre']) ?></span> <?= h($s['direccion']) ?>
        <small><?= count($turnos) ?> turno(s)</small>
      </h2>

      <?php if (!$turnos): ?>
        <p class="vacio">Todavía no hay turnos cargados para este día en esta sede.</p>
      <?php else: ?>
      <table class="tabla">
        <thead><tr><th>Horario</th><th>Cancha</th><th>Reservado por</th><th>Estado</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($turnos as $t): ?>
          <tr>
            <td class="mono"><?= substr($t['hora_inicio'],0,5) ?> – <?= substr($t['hora_fin'],0,5) ?></td>
            <td><?= h($t['cancha']) ?><?php if (!empty($t['tipo_cancha'])): ?><span class="tipo-tag"><?= h($t['tipo_cancha']) ?></span><?php endif; ?></td>
            <td><?= $t['estado']==='libre' ? '<i class="muted">Disponible</i>' : h($t['cliente']) ?></td>
            <td>
              <span class="estado estado-<?= h($t['estado']) ?>">
                <?= $t['estado']==='libre' ? 'Libre' : 'Reservado' ?>
              </span>
            </td>
            <td class="acciones">
              <button type="button" class="btn-mini"
                onclick='editar(<?= json_encode([
                  "id"=>$t["id"], "sede_id"=>$t["sede_id"],
                  "ini"=>substr($t["hora_inicio"],0,5), "fin"=>substr($t["hora_fin"],0,5),
                  "cancha"=>$t["cancha"], "tipo"=>$t["tipo_cancha"], "cliente"=>$t["cliente"], "estado"=>$t["estado"]
                ], JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE) ?>)'>Editar</button>
              <form method="post" action="/api/eliminar.php" class="inline"
                    onsubmit="return confirm('¿Borrar este turno?')">
                <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
                <input type="hidden" name="fecha" value="<?= h($fecha) ?>">
                <button type="submit" class="btn-mini danger">Borrar</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </section>
  <?php endforeach; ?>

  <p class="pie">Los cambios aparecen en la pantalla del TV en menos de 2 minutos (o al recargarla).</p>
</main>

<script>
const TIPOS = <?= json_encode($tiposPorSede, JSON_UNESCAPED_UNICODE) ?>;
const HORA_CIERRE = <?= $horaCierre ?>;
const pad = n => String(n).padStart(2,'0') + ':00';

// "Hasta" solo ofrece horas posteriores a "Desde"; por defecto, 1 hora después.
function actualizarFin(valor, forzar){
  const ini = parseInt(document.getElementById('f-ini').value, 10);
  const fin = document.getElementById('f-fin');
  const previo = forzar ? null : (valor || fin.value);
  fin.innerHTML = '';
  for (let h = ini + 1; h <= HORA_CIERRE; h++) fin.add(new Option(pad(h), pad(h)));
  fin.value = (previo && parseInt(previo,10) > ini) ? previo : pad(ini + 1);
}

// Tipo de cancha: se muestra solo en la sede que lo tiene (La Cautiva)
function toggleTipo(valor){
  const tipos = TIPOS[document.getElementById('f-sede').value] || [];
  const sel = document.getElementById('f-tipo');
  sel.innerHTML = '';
  tipos.forEach(t => sel.add(new Option(t, t)));
  if (valor && tipos.includes(valor)) sel.value = valor;
  document.getElementById('tipo-box').style.display = tipos.length ? '' : 'none';
  sel.required = tipos.length > 0;
  sel.disabled = tipos.length === 0;
}

function toggleCliente(){
  const libre = document.getElementById('f-estado').value === 'libre';
  document.getElementById('cliente-box').style.display = libre ? 'none' : '';
}
function editar(t){
  document.getElementById('f-id').value = t.id;
  document.getElementById('f-sede').value = t.sede_id;
  // Turnos viejos con minutos (ej. 13:30) se redondean a la hora en punto
  document.getElementById('f-ini').value = t.ini.slice(0,2) + ':00';
  actualizarFin(t.fin.slice(0,2) + ':00');
  toggleTipo(t.tipo);
  document.getElementById('f-cancha').value = t.cancha;
  document.getElementById('f-estado').value = t.estado;
  document.getElementById('f-cliente').value = t.cliente || '';
  document.getElementById('form-titulo').textContent = 'Editar turno';
  toggleCliente();
  window.scrollTo({top:0, behavior:'smooth'});
}
function resetForm(){
  document.getElementById('f-id').value = '';
  document.getElementById('f-cliente').value = '';
  document.getElementById('f-estado').value = 'reservado';
  document.getElementById('form-titulo').textContent = 'Nuevo turno';
  document.getElementById('f-ini').selectedIndex = 0;
  actualizarFin(null, true);
  toggleTipo();
  toggleCliente();
}
actualizarFin();
toggleTipo();
toggleCliente();
</script>
</body>
</html>
