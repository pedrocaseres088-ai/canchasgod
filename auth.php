<?php
/* ============================================================
   LOGIN Y ROLES
   ------------------------------------------------------------
   Este archivo es el "enchufe" para tu sistema de registro.

   El resto del sistema SOLO necesita que, cuando un usuario
   inicia sesión, existan estas dos variables de sesión:

       $_SESSION['usuario']  -> nombre de usuario (texto)
       $_SESSION['rol']      -> 'admin'  o  'recepcion'

   Cuando me pases tu sistema de registro con roles, lo único
   que hay que hacer es que TU login setee esas dos variables.
   Todo lo demás sigue funcionando igual.

   Roles usados:
     - 'admin'      : puede todo (turnos + más adelante sedes/usuarios)
     - 'recepcion'  : puede cargar, editar y borrar turnos
   ============================================================ */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function usuario_actual() {
    return $_SESSION['usuario'] ?? null;
}

function rol_actual() {
    return $_SESSION['rol'] ?? null;
}

/* Redirige al login si no hay sesión iniciada. */
function requerir_login() {
    if (!usuario_actual()) {
        header('Location: login.php');
        exit;
    }
}

/* Corta la ejecución si el rol no está en la lista permitida. */
function requerir_rol(array $roles_permitidos) {
    requerir_login();
    if (!in_array(rol_actual(), $roles_permitidos, true)) {
        http_response_code(403);
        die('No tenés permisos para realizar esta acción.');
    }
}
