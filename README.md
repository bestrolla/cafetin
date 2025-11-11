# Cafetín (CDC)

Sistema web para gestión de inventario, ventas, deudas y abonos, con perfiles de Administrador y Cajero. Este README explica la estructura del proyecto, cómo instalar y configurar, y las reglas de validación aplicadas a los formularios en todos los módulos.

## Características
- Gestión de inventario: alta, edición y control de stock y precios.
- Ventas rápidas desde el Lobby del Cajero con registro de clientes.
- Cuentas por cobrar: visualización de facturas, saldos y abonos en USD y Bs.
- Configuración de tasa de cambio y preferencias.
- Validaciones de formularios uniformes para mejorar la calidad de datos.

## Estructura del Proyecto
Raíz del proyecto (carpetas principales):

```
cafetin/
├── BBDD/                 # SQL y utilidades de base de datos
├── acces/                # Seguridad, navegación y estilos comunes
├── admin/                # Módulos para Administrador
│   ├── agregar_cajero/
│   ├── caja/
│   ├── configuracion/
│   ├── cuentas/
│   └── inventario/
├── cajero/               # Módulos para Cajero
│   ├── configuracion/
│   ├── cuentas/
│   └── lobby/
└── login/                # Inicio de sesión y recuperación
```

Cada submódulo contiene `vista/` (interfaces y `script.js`) y `logica/` (PHP que procesa datos/consultas).

## Requisitos
- Servidor: WAMP, XAMPP o Apache+PHP+MySQL.
- PHP 7.4+.
- MySQL/MariaDB.

## Instalación
1. Copia el proyecto en el directorio del servidor (`www`/`htdocs`).
2. Crea la base de datos y carga el esquema:
   - Importa `BBDD/cafetin.sql` (o la versión más reciente disponible).
3. Configura conexión a la base de datos:
   - Edita `BBDD/BBDD.php` y define host, usuario, contraseña y nombre de la base de datos.
4. (Opcional) Crea el usuario Administrador inicial:
   - Revisa `BBDD/crear_admin.php` o el flujo de alta de admin según tu entorno.
5. Inicia el servidor y abre `http://localhost/cafetin/` en el navegador.

## Módulos Principales

### Inventario (Admin)
- Ruta: `admin/inventario/vista/`
- Formulario de nuevo producto y edición.
- Validaciones clave:
  - Nombre: solo letras; se capitaliza la primera letra al perder foco.
  - Cantidades: solo dígitos, no negativos.
  - Precios: números decimales con un punto, no negativos; se formatean a dos decimales al perder foco.
- Archivo relevante: `admin/inventario/vista/script.js`.

### Agregar Cajero (Admin)
- Ruta: `admin/agregar_cajero/vista/`
- Alta de cajeros con nombre, apellido y teléfono.
- Validaciones:
  - Nombre/Apellido: solo letras; capitaliza la primera letra en blur.
  - Teléfono: solo dígitos, normalizado antes del envío.
- Archivo relevante: `admin/agregar_cajero/vista/script.js`.

### Cuentas (Admin)
- Ruta: `admin/cuentas/vista/`
- Visualización de cuentas, detalle, historial y abonos.
- Validaciones en el modal de Abono:
  - Montos USD/Bs: decimales válidos, no negativos; dos decimales al perder foco.
  - Observaciones: capitaliza la primera letra.
- Archivo relevante: `admin/cuentas/vista/script.js`.

### Lobby (Cajero)
- Ruta: `cajero/lobby/vista/`
- Registro/selección de cliente y venta rápida.
- Validaciones:
  - Cédula y Teléfono: solo dígitos, normalizados en blur.
  - Nombre, Apellido y Alias: solo letras y espacios; capitaliza la primera letra en blur.
- Archivo relevante: `cajero/lobby/vista/script.js`.

### Cuentas (Cajero)
- Ruta: `cajero/cuentas/vista/`
- Consulta de saldos, detalle por fecha y abonos.
- Validaciones en el modal de Abono:
  - Montos USD/Bs: decimales válidos, no negativos; dos decimales al perder foco; el equivalente se recalcula en tiempo real.
  - Observaciones: capitaliza la primera letra.
- Archivo relevante: `cajero/cuentas/vista/script.js`.

## Tasa de Cambio y Moneda
- La tasa se usa para convertir entre USD y Bs al mostrar totales y equivalentes.
- El proyecto utiliza una preferencia de moneda (USD/Bs) y funciones como `getTasaCambio()` y `formatMonto(...)` en las vistas.
- Si tu entorno no provee una interfaz de configuración, puedes definir la tasa vía almacenamiento local:
  - En consola del navegador: `localStorage.setItem('tasaCambio', '40.00');` (ejemplo)
  - Asegúrate de que la tasa sea mayor a 0 para que se muestren equivalentes correctamente.

## Seguridad
- Archivos en `acces/` para cabeceras de seguridad y protección CSRF.
- Validaciones en el frontend para mejorar la calidad de datos (no sustituyen la validación del backend).
- Recuerda validar en el backend todas las entradas antes de persistir.

## Flujo de Uso Sugerido
1. Configura la conexión a BD y (opcional) la tasa de cambio.
2. Ingresa como Administrador.
3. Agrega cajeros en `Admin > Agregar Cajero`.
4. Carga productos en `Admin > Inventario`.
5. El Cajero usa `Lobby` para registrar/seleccionar cliente y realizar ventas.
6. Admin y Cajero consultan `Cuentas` para ver saldos y registrar abonos.

## Desarrollo
- Scripts JS por módulo dentro de `vista/` para manejar interacciones y validaciones.
- Lógica PHP por módulo dentro de `logica/` para consultas y operaciones.
- Estructura y estilos en cada carpeta `vista/` (`.php`, `.css`, `.js`).

## Consejos de Mantenimiento
- Mantén sincronizadas las reglas de validación entre módulos.
- Verifica que los IDs/Names de formulario correspondan a los manejadores en `script.js`.
- Antes de cambios grandes, prueba en un ambiente local y verifica las vistas que impactan moneda/tasa.

## Soporte y Extensiones
- ¿Necesitas aplicar las mismas validaciones a otros formularios (`login`, `recuperación`, etc.)? Aporta los IDs de inputs y las rutas, y replica las funciones de sanitización.
- Si se integra un módulo de configuración centralizada para la tasa, documenta el flujo en esta sección.

---

Si necesitas un tutorial paso a paso con capturas o quieres que ampliemos este README con ejemplos de API y consultas SQL usadas por los módulos, indícalo y lo añadimos.

## Descripción

CDC es una aplicación  desarrollada en PHP que permite la gestión de usuarios y el acceso mediante un sistema de login seguro. El proyecto utiliza HTML, CSS y JavaScript para la interfaz, y PHP para la lógica de servidor.

## Estructura del proyecto

- **login/inicio/vista/**: Contiene la interfaz de inicio de sesión y los estilos.
- **acces/footer/**: Incluye el footer reutilizable en todas las páginas.
- **acces/img/**: Imágenes utilizadas en la aplicación.

## Instalación

1. Clona el repositorio en tu servidor local (por ejemplo, WAMP).
2. Asegúrate de tener PHP y un servidor web configurado.
3. Coloca los archivos en la carpeta `www` de tu servidor.

## Uso

1. Accede a la URL principal en tu navegador.
2. Ingresa tus credenciales en el formulario de login.
3. Navega por la aplicación según los permisos de usuario.

## Personalización

- Modifica los estilos en `login/inicio/vista/style.css`.
- Cambia las imágenes en `acces/img/`.
- Edita el footer en `acces/footer/footer.php`.

## Créditos

Desarrollado por angel manzano.

## Licencia

Este proyecto es de uso interno y no cuenta con una licencia pública.
# CDC
# CDC
# CDC
# cafetin
