-- ============================================================
--  USUARIO DE BASE DE DATOS CON PERMISOS MÍNIMOS
--  ------------------------------------------------------------
--  Por defecto la app se conecta como "root" (dueño de todo): si
--  alguien explotara una falla, podría borrar tablas o la base entera.
--  Este script crea un usuario que SOLO puede lo que la app necesita:
--
--    sedes     -> leer
--    turnos    -> leer, crear, editar, borrar
--    usuarios  -> leer (el login solo consulta; nunca modifica)
--
--  NO puede: crear/borrar/alterar tablas, ni tocar otras bases.
--
--  CÓMO USARLO (una sola vez, con el usuario administrador de MySQL):
--   1. Cambiá 'CAMBIAR_ESTA_CLAVE' por una contraseña larga y única.
--   2. Ejecutalo en Railway -> servicio MySQL -> pestaña "Data" -> Query
--      (o en phpMyAdmin). Reemplazá `railway` si tu base tiene otro nombre.
--   3. En el servicio de la app (canchas-god) -> Variables, cambiá:
--        MYSQL_URL       = mysql://turnero_app:LA_CLAVE@HOST:PUERTO/railway
--        MYSQLUSER       = turnero_app
--        MYSQLPASSWORD   = LA_CLAVE
--      (MYSQL_URL tiene prioridad sobre las otras: si la dejás con root,
--       la app seguirá usando root. Cambiala o borrala.)
--
--  IMPORTANTE: /instalar.php necesita crear tablas, así que corrélo ANTES
--  de pasar la app a este usuario (o temporalmente con el usuario admin).
--  La migración automática de conexion.php también usa ALTER TABLE; con este
--  usuario simplemente se saltea (ya no hace falta una vez instalado).
-- ============================================================

CREATE USER IF NOT EXISTS 'turnero_app'@'%' IDENTIFIED BY 'CAMBIAR_ESTA_CLAVE';

GRANT SELECT                         ON `railway`.`sedes`    TO 'turnero_app'@'%';
GRANT SELECT, INSERT, UPDATE, DELETE ON `railway`.`turnos`   TO 'turnero_app'@'%';
GRANT SELECT                         ON `railway`.`usuarios` TO 'turnero_app'@'%';

FLUSH PRIVILEGES;
