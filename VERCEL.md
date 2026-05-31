# Despliegue en Vercel

Este proyecto usa **MySQL** (como en WAMP), no SQLite por defecto.

## Configuración en el panel de Vercel

1. **Root Directory**: vacío (raíz del repositorio).
2. **Output Directory**: no definir `api` como salida.
3. **Framework Preset**: Other.

## Variables de entorno (MySQL remoto)

En Vercel debes configurar una base MySQL accesible desde internet (PlanetScale, Railway, Aiven, etc.):

| Variable | Ejemplo |
|----------|---------|
| `MYSQL_HOST` | host del proveedor |
| `MYSQL_DATABASE` | `cafetin` |
| `MYSQL_USER` | usuario |
| `MYSQL_PASSWORD` | contraseña |

Importa tu base desde phpMyAdmin (export de WAMP) al MySQL remoto.

## Local (WAMP)

Por defecto conecta a:

- Host: `127.0.0.1`
- Base: `cafetin`
- Usuario: `root`
- Contraseña: *(vacía)*

Puedes cambiarlo con las mismas variables `MYSQL_*` en el entorno.

## Router PHP

Las rutas pasan por `api/index.php` (ver `vercel.json`). Eso se mantiene para que las páginas `.php` funcionen en Vercel.

## Sesiones

En Vercel las sesiones usan `/tmp`. Si el login “se pierde” entre páginas, es limitación del entorno serverless.
