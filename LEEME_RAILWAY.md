# Turin Sport — Puesta en marcha en RAILWAY

Ya tenés en Railway el servicio del código (**canchas-god**) y el de la base
(**MySQL**). Faltan 3 pasos para que funcione de punta a punta.

---

## PASO 1 — Subir esta versión al repo

Reemplazá el contenido de tu repo de GitHub por esta carpeta (o subí/actualizá
estos archivos) y hacé **push**. Lo importante es que estos queden en la **raíz**
del repo:

```
Dockerfile          ← nuevo (hace que Railway corra PHP)
config.php          ← nuevo (lee las variables de Railway)
conexion.php
auth.php
instalar.php        ← nuevo (llena la base de un click)
index.html          ← redirige a la pantalla
api/        (turnos.php, guardar.php, eliminar.php)
admin/      (index.php, login.php, logout.php, estilos.css)
pantalla/   (index.html)
```

Al hacer push, Railway **redeploya solo** usando el `Dockerfile`.

---

## PASO 2 — Conectar el código con la base (variables)

Railway NO comparte las variables entre servicios automáticamente. Hay que
enlazarlas una vez:

1. Entrá al servicio **canchas-god** → pestaña **Variables**.
2. Agregá estas 5 variables (botón *New Variable* → *Add Reference* suele
   ofrecerte el servicio MySQL directamente). Los valores son referencias:

   ```
   MYSQLHOST      = ${{MySQL.MYSQLHOST}}
   MYSQLPORT      = ${{MySQL.MYSQLPORT}}
   MYSQLUSER      = ${{MySQL.MYSQLUSER}}
   MYSQLPASSWORD  = ${{MySQL.MYSQLPASSWORD}}
   MYSQLDATABASE  = ${{MySQL.MYSQLDATABASE}}
   ```

   > Atajo: si Railway te deja agregar la referencia **`MYSQL_URL`**
   > (`MYSQL_URL = ${{MySQL.MYSQL_URL}}`), con esa sola alcanza — el código
   > también la entiende.

3. Guardá. Railway vuelve a desplegar el servicio.

---

## PASO 3 — Llenar la base (un solo click)

1. Entrá en el navegador a:  **`https://TU-DOMINIO/instalar.php`**
   (tu dominio es `canchas-god-production.up.railway.app`)
2. Vas a ver ✅ Tablas creadas / Sedes / Turnos / Usuarios.
3. Listo. **Borrá `instalar.php`** del repo después (por seguridad).

---

## Probar que quedó andando

- **Pantalla (TV):** `https://TU-DOMINIO/`  → muestra el turnero.
- **Panel:** `https://TU-DOMINIO/admin/`
  - Usuario: **admin** · Contraseña: **admin123** (cambiala)
  - Cargá o editá un turno → recargá la pantalla → **el cambio tiene que verse**.
    Si se ve, está todo conectado. 🎉

> Si la pantalla muestra siempre lo mismo aunque edites, es que no está leyendo
> la base (revisá el PASO 2). Cuando conecta bien, muestra los datos reales.

---

## Alternativa para el PASO 3 (SQL a mano)

Si preferís cargar las tablas desde la consola de MySQL en Railway, está el
archivo **`db.sql`** con las tablas + datos. Pegalo en la pestaña *Data* del
servicio MySQL. **Ojo:** los usuarios del panel NO se pueden crear por SQL
(la contraseña va encriptada), así que igual tenés que entrar una vez a
`instalar.php` para crear admin y recepción.

---

## Lo que sigue

Cuando me pases tu **sistema de registro con roles**, reemplazamos el login del
panel por el tuyo. Solo hace falta que, al iniciar sesión, se seteen
`$_SESSION['usuario']` y `$_SESSION['rol']` (ver `auth.php`).
