# Turin Sport — Sistema de turnos

Backend (PHP + MySQL) + panel de administración + pantalla para el TV.
Quien maneja el complejo carga los turnos desde el panel, **sin tocar código**,
y la pantalla del TV se actualiza sola.

---

## Qué hace cada parte

```
turnero_sistema/
├── index.html            → portada con los dos accesos
├── config.php            → ★ el ÚNICO archivo que editás (datos de la base)
├── conexion.php          → conexión a MySQL
├── auth.php              → login y roles (acá se enchufa tu sistema de registro)
├── db.sql                → crea las tablas y datos de ejemplo (se importa 1 vez)
│
├── pantalla/
│   └── index.html        → el turnero que va en el TV (lee los turnos de la API)
│
├── admin/
│   ├── setup.php         → crea los usuarios iniciales (se corre 1 vez y se borra)
│   ├── login.php         → ingreso al panel
│   ├── index.php         → panel: cargar / editar / borrar turnos
│   ├── logout.php
│   └── estilos.css
│
└── api/
    ├── turnos.php        → devuelve los turnos del día en JSON (lo lee la pantalla)
    ├── guardar.php       → crea o edita un turno
    └── eliminar.php      → borra un turno
```

---

## Instalación en Alwaysdata (paso a paso)

**1) Crear la base de datos**
- Entrá al panel de Alwaysdata → **Bases de datos → MySQL → Agregar una base**.
- Anotá: *host*, *nombre de la base*, *usuario* y *contraseña*.

**2) Configurar `config.php`**
- Abrí `config.php` y completá esos 4 datos. Es lo único que hay que editar.

**3) Subir los archivos**
- Subí toda la carpeta `turnero_sistema/` a la carpeta `www/` de tu sitio
  (por FTP o desde **Alwaysdata → Archivos**).

**4) Crear las tablas**
- En Alwaysdata → **Bases de datos → phpMyAdmin**, elegí tu base y usá
  **Importar** para subir el archivo `db.sql`. Esto crea las tablas y carga
  turnos de ejemplo para hoy.

**5) Crear los usuarios del panel**
- Entrá una vez a `https://TU-SITIO/admin/setup.php`.
- Crea dos usuarios:
  - **admin / admin123**  (puede todo)
  - **recepcion / recepcion123**  (carga y edita turnos)
- **Cambiá esas contraseñas y borrá el archivo `admin/setup.php`.**

**¡Listo!**
- Panel: `https://TU-SITIO/admin/`
- Pantalla del TV: `https://TU-SITIO/pantalla/index.html`

---

## Cómo se usa el panel

- Elegís el **día** arriba (por defecto, hoy).
- En **"Nuevo turno"** cargás: sede, horario, cancha, estado y quién reservó.
  - **Reservado** → aparece en **rojo** en la pantalla.
  - **Libre / Disponible** → aparece en **verde**.
  - El turno que coincide con la **hora actual** se pinta solo en **amarillo** ("En juego").
- Cada turno tiene **Editar** y **Borrar**.
- Los cambios se ven en el TV en menos de 2 minutos (o al recargar la pantalla).

> Si la pantalla no logra conectarse a la base, muestra datos de ejemplo
> para no quedar en blanco. Al reconectar, vuelve a los datos reales.

---

## Roles

| Rol         | Puede hacer                                  |
|-------------|----------------------------------------------|
| `admin`     | Todo (turnos + a futuro sedes/usuarios)      |
| `recepcion` | Cargar, editar y borrar turnos               |

La pantalla del TV **no necesita login** (solo lee).

---

## Enchufar TU sistema de registro con roles

Todo el control de acceso pasa por **`auth.php`**. El resto del sistema solo
necesita que, al iniciar sesión, existan estas dos variables:

```php
$_SESSION['usuario'];  // nombre de usuario
$_SESSION['rol'];      // 'admin'  o  'recepcion'
```

Cuando me pases tu sistema de registro, lo integramos así:
1. Tu login valida al usuario (contra tu tabla / tu lógica).
2. Al validar, setea `$_SESSION['usuario']` y `$_SESSION['rol']`.
3. Se puede reemplazar `admin/login.php` por tu pantalla de ingreso, o
   redirigir a la tuya. Nada más cambia.

Si tus roles se llaman distinto (por ej. `gerente`, `empleado`), los mapeamos
a `admin` / `recepcion` en `auth.php`. **Pasámelo y lo dejo enganchado.**

---

## Nota técnica
Está hecho en **PHP + MySQL** por ser lo más simple de hostear en Alwaysdata
(no requiere levantar ningún proceso). Si tu sistema de registro está en
**Node.js o Python**, avisame y lo reescribo en ese stack para que sea todo uno.
