-- ============================================================
--  BASE DE DATOS DEL TURNERO — Turin Sport
--  Importá este archivo en phpMyAdmin (Alwaysdata) una sola vez.
-- ============================================================
SET NAMES utf8mb4;

-- ---------- SEDES ----------
CREATE TABLE IF NOT EXISTS sedes (
  id        INT AUTO_INCREMENT PRIMARY KEY,
  nombre    VARCHAR(60)  NOT NULL,        -- ej: "Sede 1"
  direccion VARCHAR(160) NOT NULL,        -- ej: "San Leonardo Murialdo 909"
  orden     INT NOT NULL DEFAULT 0,       -- orden en que aparecen en pantalla
  tipos_cancha VARCHAR(160) NULL           -- ej: "Fútbol 5,Fútbol 7,Fútbol 11" (vacío = sin tipos)
) ENGINE=InnoDB;

-- ---------- TURNOS ----------
CREATE TABLE IF NOT EXISTS turnos (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  sede_id     INT  NOT NULL,
  fecha       DATE NOT NULL,
  hora_inicio TIME NOT NULL,
  hora_fin    TIME NOT NULL,
  cancha      VARCHAR(40) NOT NULL DEFAULT 'Cancha 1',
  tipo_cancha VARCHAR(30) NULL,           -- Fútbol 5 / 7 / 11 (solo sedes con tipos)
  cliente     VARCHAR(80) NULL,           -- quién reservó (vacío = libre)
  estado      ENUM('reservado','libre') NOT NULL DEFAULT 'reservado',
  FOREIGN KEY (sede_id) REFERENCES sedes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- USUARIOS (para el panel de administración) ----------
-- Si más adelante usás tu propio sistema de registro, esta tabla
-- se puede reemplazar por la tuya. Ver auth.php.
CREATE TABLE IF NOT EXISTS usuarios (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  usuario    VARCHAR(40)  NOT NULL UNIQUE,
  clave_hash VARCHAR(255) NOT NULL,
  rol        ENUM('admin','recepcion') NOT NULL DEFAULT 'recepcion'
) ENGINE=InnoDB;

-- ============================================================
--  DATOS INICIALES
-- ============================================================

-- Las dos sedes
INSERT INTO sedes (nombre, direccion, orden, tipos_cancha) VALUES
  ('Sede 1', 'San Leonardo Murialdo 909', 1, NULL),
  ('Sede 2', 'La Cautiva 7654', 2, 'Fútbol 5,Fútbol 7,Fútbol 11');

-- Turnos de ejemplo para HOY (para que la pantalla muestre algo enseguida).
-- Sede 1 = id 1, Sede 2 = id 2
INSERT INTO turnos (sede_id, fecha, hora_inicio, hora_fin, cancha, cliente, estado) VALUES
  (1, CURDATE(), '08:00','09:00','Cancha 1','Matías G.','reservado'),
  (1, CURDATE(), '09:00','10:00','Cancha 2','Club Atletismo','reservado'),
  (1, CURDATE(), '10:00','11:00','Cancha 1','Pablo R.','reservado'),
  (1, CURDATE(), '11:00','12:00','Cancha 1',NULL,'libre'),
  (1, CURDATE(), '16:00','17:00','Cancha 3','Sebastián L.','reservado'),
  (1, CURDATE(), '17:00','18:00','Cancha 1','Ramírez, J.','reservado'),
  (1, CURDATE(), '20:00','21:00','Cancha 3',NULL,'libre'),
  (1, CURDATE(), '21:00','22:00','Cancha 1','Jorge S.','reservado'),
  (2, CURDATE(), '09:00','10:00','Cancha 1','Escuelita de Fútbol','reservado'),
  (2, CURDATE(), '10:00','11:00','Cancha 2','Gonzalo P.','reservado'),
  (2, CURDATE(), '13:00','14:00','Cancha 2',NULL,'libre'),
  (2, CURDATE(), '16:00','17:00','Cancha 2','Luciana R.','reservado'),
  (2, CURDATE(), '18:00','19:00','Cancha 1','Torneo Interno','reservado'),
  (2, CURDATE(), '21:00','22:00','Cancha 2',NULL,'libre');

-- Los usuarios NO se crean acá (necesitan contraseña encriptada).
-- Entrá una vez a  /admin/setup.php  y se crean solos. Después borrá ese archivo.
