# Despliegue en Vercel

Este proyecto PHP usa el runtime [vercel-php](https://github.com/vercel-community/php). Todas las peticiones a archivos `.php` pasan por `api/index.php`, que ejecuta el archivo correspondiente del proyecto.

## Configuración en el panel de Vercel

1. **Root Directory**: dejar vacío (raíz del repositorio).
2. **Output Directory**: no definir `api` como salida; debe quedar por defecto.
3. **Framework Preset**: Other.

## Variables de entorno (recomendadas)

| Variable | Valor | Descripción |
|----------|--------|-------------|
| `CAFETIN_DB_DRIVER` | `sqlite` | Base de datos en serverless (ya viene en `vercel.json`) |
| `CAFETIN_SQLITE_PATH` | `/tmp/cafetin.db` | Ruta escribible en Vercel |

### MySQL externo (opcional)

Si usas PlanetScale, Railway MySQL, etc.:

| Variable | Ejemplo |
|----------|---------|
| `CAFETIN_DB_DRIVER` | `mysql` |
| `MYSQL_HOST` | host del proveedor |
| `MYSQL_DATABASE` | `cafetin` |
| `MYSQL_USER` | usuario |
| `MYSQL_PASSWORD` | contraseña |

(Deberás ampliar `BBDD/BBDD.php` para leer esas variables si usas MySQL en producción.)

## Usuario de prueba

Tras el primer arranque con SQLite se crea o actualiza:

- **Usuario:** `admin`
- **Contraseña:** `Admin123$`

## Limitaciones en Vercel

- Las sesiones PHP se guardan en `/tmp`; en entorno serverless pueden no persistir entre todas las peticiones si hay muchas instancias. Si el login “se pierde”, considera un host con PHP tradicional (WAMP, Railway, Render).
- La base SQLite en `/tmp` se reinicia si la instancia serverless es nueva; para datos permanentes usa MySQL gestionado o despliega en un VPS.

## Probar en local (sin Vercel)

```bash
cd c:\wamp64\www\cafetin
php -S localhost:8080 api/index.php
```

Abre: http://localhost:8080/login/inicio/vista/inicio.php
