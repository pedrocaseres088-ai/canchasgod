<?php
/* Login del panel. Cuando integres tu sistema de registro, este
   archivo se reemplaza: solo tenés que setear $_SESSION['usuario']
   y $_SESSION['rol'] al validar. Ver auth.php */

require __DIR__ . '/../auth.php';
require __DIR__ . '/../conexion.php';

// Si ya está logueado, al panel.
if (usuario_actual()) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['usuario'] ?? '');
    $p = $_POST['clave'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ?");
    $stmt->execute([$u]);
    $row = $stmt->fetch();

    if ($row && password_verify($p, $row['clave_hash'])) {
        $_SESSION['usuario'] = $row['usuario'];
        $_SESSION['rol']     = $row['rol'];
        header('Location: index.php');
        exit;
    }
    $error = 'Usuario o contraseña incorrectos.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ingresar · Turin Sport</title>
<link rel="stylesheet" href="estilos.css">
</head>
<body class="login-body">
  <form class="login-card" method="post">
    <div class="login-brand">TURIN SPORT</div>
    <div class="login-sub">Panel de turnos</div>
    <?php if ($error): ?><div class="alerta err"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <label>Usuario</label>
    <input type="text" name="usuario" autofocus required>
    <label>Contraseña</label>
    <input type="password" name="clave" required>
    <button type="submit">Ingresar</button>
  </form>
</body>
</html>
