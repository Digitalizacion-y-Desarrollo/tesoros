# Copias de seguridad y restauración

## Alcance

Una copia consistente incluye:

- Base de datos MySQL.
- `writable/private/uploads/`.
- Configuración del entorno conservada en un almacén de secretos separado.
- Versión exacta del código y `composer.lock`.

No copies sesiones temporales ni caches como sustituto de la base y los
archivos privados.

## Respaldo

1. Activa una ventana de mantenimiento o coordina un snapshot consistente.
2. Exporta MySQL:

   ```text
   mysqldump --single-transaction --routines --triggers --default-character-set=utf8mb4 tesoros > tesoros.sql
   ```

3. Copia `writable/private/uploads/` conservando permisos.
4. Calcula SHA-256 del dump y del archivo comprimido.
5. Cifra la copia y almacénala fuera del servidor con acceso mínimo.
6. Registra fecha, responsable, versión del sistema y resultado.

La frecuencia y retención definitivas deben ser aprobadas por la institución.

## Restauración

1. Despliega la misma versión del código.
2. Crea una base vacía con `utf8mb4_unicode_ci`.
3. Importa el dump.
4. Restaura los archivos privados en su ruta original.
5. Aplica permisos al usuario de PHP.
6. Ejecuta `php spark migrate:status`; no ejecutes seeders de desarrollo.
7. Verifica conteos, folios, versiones documentales y archivos por SHA-256.
8. Prueba acceso privado, descarga autorizada, SMTP y cola.

Realiza simulacros periódicos en un entorno aislado y nunca uses datos
personales restaurados para capturas o demostraciones.
