-- ============================================================
--  BASE DE DATOS DEL TURNERO — Turin Sport
--  Importá este archivo en phpMyAdmin (Alwaysdata) una sola vez.
--
--  Incluye RESTRICCIONES DE SEGURIDAD / INTEGRIDAD (CHECK, claves
--  foráneas, valores obligatorios). Requiere MySQL 8.0.16+ o
--  MariaDB 10.2+ para que los CHECK se hagan cumplir.
--  Para limitar los permisos del usuario de la app, ver
--  seguridad_usuario_app.sql
-- ============================================================
SET NAMES utf8mb4;

-- ---------- SEDES ----------
CREATE TABLE IF NOT EXISTS sedes (
  id        INT AUTO_INCREMENT PRIMARY KEY,
  nombre    VARCHAR(60)  NOT NULL,        -- ej: "Sede 1"
  direccion VARCHAR(160) NOT NULL,        -- ej: "San Leonardo Murialdo 909"
  orden     INT NOT NULL DEFAULT 0,       -- orden en que aparecen en pantalla
  tipos_cancha VARCHAR(160) NULL           -- ej: "Fútbol 5,Fútbol 7,Fútbol 11" (vacío = sin tipos)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- TURNOS ----------
-- Ya no se usan "Cancha 1 / Cancha 2": la cancha se identifica con tipo_cancha.
-- La columna "cancha" queda solo por compatibilidad con datos viejos.
CREATE TABLE IF NOT EXISTS turnos (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  sede_id     INT  NOT NULL,
  fecha       DATE NOT NULL,
  hora_inicio TIME NOT NULL,
  hora_fin    TIME NOT NULL,
  cancha      VARCHAR(40) NOT NULL DEFAULT '',   -- (en desuso)
  tipo_cancha VARCHAR(30) NULL,           -- Fútbol 5 / 7 / 11 (solo sedes con tipos)
  cliente     VARCHAR(80) NULL,           -- quién reservó (vacío = libre)
  estado      ENUM('reservado','libre') NOT NULL DEFAULT 'reservado',
  FOREIGN KEY (sede_id) REFERENCES sedes(id) ON DELETE CASCADE,

  -- El turno termina después de empezar.
  CONSTRAINT chk_turnos_horario CHECK (hora_fin > hora_inicio),
  -- Solo horas en punto (13:00, 14:00 …).
  CONSTRAINT chk_turnos_hora_punto CHECK (
    MINUTE(hora_inicio) = 0 AND SECOND(hora_inicio) = 0 AND
    MINUTE(hora_fin)    = 0 AND SECOND(hora_fin)    = 0),
  -- Libre => sin cliente. Reservado => con cliente (no vacío).
  CONSTRAINT chk_turnos_cliente CHECK (
    (estado = 'libre' AND cliente IS NULL) OR
    (estado = 'reservado' AND cliente IS NOT NULL AND CHAR_LENGTH(TRIM(cliente)) > 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- USUARIOS (para el panel de administración) ----------
-- Si más adelante usás tu propio sistema de registro, esta tabla
-- se puede reemplazar por la tuya. Ver auth.php.
CREATE TABLE IF NOT EXISTS usuarios (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  usuario    VARCHAR(40)  NOT NULL UNIQUE,
  clave_hash VARCHAR(255) NOT NULL,
  rol        ENUM('admin','recepcion') NOT NULL DEFAULT 'recepcion',

  -- Nombre de usuario simple (letras, números, _ . -), 3 a 40 caracteres.
  CONSTRAINT chk_usuarios_nombre CHECK (usuario REGEXP '^[A-Za-z0-9_.-]{3,40}$'),
  -- Impide guardar una contraseña en texto plano: un hash de password_hash()
  -- siempre tiene 60 caracteres o más.
  CONSTRAINT chk_usuarios_hash CHECK (CHAR_LENGTH(clave_hash) >= 60)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
--  DATOS INICIALES
-- ============================================================

-- Las dos sedes
INSERT INTO sedes (nombre, direccion, orden, tipos_cancha) VALUES
  ('Sede 1', 'San Leonardo Murialdo 909', 1, 'Fútbol 5'),
  ('Sede 2', 'La Cautiva 7654', 2, 'Fútbol 5,Fútbol 7,Fútbol 11');

-- Turnos de ejemplo para HOY (para que la pantalla muestre algo enseguida).
-- Sede 1 = id 1 (Fútbol 5), Sede 2 = id 2 (Fútbol 5 / 7 / 11)
INSERT INTO turnos (sede_id, fecha, hora_inicio, hora_fin, tipo_cancha, cliente, estado) VALUES
  (1, CURDATE(), '08:00','09:00','Fútbol 5','Matías G.','reservado'),
  (1, CURDATE(), '09:00','10:00','Fútbol 5','Club Atletismo','reservado'),
  (1, CURDATE(), '10:00','11:00','Fútbol 5','Pablo R.','reservado'),
  (1, CURDATE(), '11:00','12:00','Fútbol 5',NULL,'libre'),
  (1, CURDATE(), '16:00','17:00','Fútbol 5','Sebastián L.','reservado'),
  (1, CURDATE(), '17:00','18:00','Fútbol 5','Ramírez, J.','reservado'),
  (1, CURDATE(), '20:00','21:00','Fútbol 5',NULL,'libre'),
  (1, CURDATE(), '21:00','22:00','Fútbol 5','Jorge S.','reservado'),
  (2, CURDATE(), '09:00','10:00','Fútbol 5','Escuelita de Fútbol','reservado'),
  (2, CURDATE(), '10:00','11:00','Fútbol 7','Gonzalo P.','reservado'),
  (2, CURDATE(), '13:00','14:00','Fútbol 7',NULL,'libre'),
  (2, CURDATE(), '16:00','17:00','Fútbol 7','Luciana R.','reservado'),
  (2, CURDATE(), '18:00','19:00','Fútbol 11','Torneo Interno','reservado'),
  (2, CURDATE(), '21:00','22:00','Fútbol 5',NULL,'libre');

-- Los usuarios NO se crean acá (necesitan contraseña encriptada).
-- Entrá una vez a  /instalar.php  y se crean solos. Después borrá ese archivo
-- y cambiá las contraseñas iniciales.
