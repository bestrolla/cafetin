# Esquema de base de datos

Referencia: **`cafetin (10).sql`** (no modificar; solo documentación).

## Tablas principales

| Tabla | Uso |
|-------|-----|
| `usuario` | Login (`usuario`, `contrasena`, `id_rol`, `id_persona`) — **sin columna `estado`** |
| `persona` | Nombre, apellido, cédula, teléfono |
| `rol` | admin, cajero, cliente |
| `ventas` | Caja / reportes |
| `credito` | Cuentas por cobrar (`estado`: pendiente, pagado, parcial) |
| `abonos` | Pagos a créditos |
| `inventario` | Productos y stock |
| `configuraciones` | Tasa, empresa, etc. |

## Conexión (WAMP)

Por defecto en `BBDD/BBDD.php`:

- Host: `127.0.0.1`
- Base: `cafetin`
- Usuario: `root`
- Contraseña: *(vacía)*

Importa tu dump en phpMyAdmin si la base está vacía.
