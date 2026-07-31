# Despliegue

## Requisitos

- PHP 8.2 con `curl`, `fileinfo`, `intl`, `mbstring`, `mysqli`, `mysqlnd` y
  `openssl`.
- MySQL 8, Composer 2 y Apache 2.4.
- TLS válido.
- Antivirus de línea de comandos.
- SMTP institucional.

## Preparación

1. Instalar dependencias con `composer install --no-dev --prefer-dist
   --optimize-autoloader`.
2. Ejecutar `npm ci` y `npm run build:assets`.
3. Copiar `.env.example` a `.env` fuera del control de versiones.
4. Crear la base con `utf8mb4_unicode_ci`.
5. Ejecutar `php spark migrate --all` y `php spark db:seed CategorySeeder`.
6. Dar escritura al usuario de PHP únicamente sobre `writable/`.

## Raíz pública

El `DocumentRoot` debe apuntar exclusivamente a `public/`. Deshabilita listados
de directorio y no publiques `app/`, `vendor/`, `.env` ni `writable/`.

## Configuración obligatoria

- `CI_ENVIRONMENT = production`
- `app.baseURL` con HTTPS y `/` final.
- `app.forceGlobalSecureRequests = true`
- `cookie.secure = true`
- Credenciales de MySQL con privilegios mínimos.
- `adminAuth.systemKey` y URL institucional.
- Credenciales SMTP institucionales.

En el `php.ini` del servidor se deben conservar `display_errors = Off`,
`log_errors = On` y `zend.exception_ignore_args = On`. La configuración de
arranque de producción refuerza estos valores en tiempo de ejecución. Después
de un despliegue se debe reiniciar Apache o PHP-FPM y limpiar OPcache.

- `uploads.antivirusCommand` y `videoUploads.antivirusCommand`.
- `convocation.closeAt` únicamente cuando exista fecha oficial.

No desactives TLS para resolver certificados; configura `curl.cainfo`,
`openssl.cafile` o `http.caBundle`.

## Archivos de 500 MB

Configura como mínimo:

```ini
upload_max_filesize = 500M
post_max_size = 520M
max_execution_time = 600
max_input_time = 600
memory_limit = 768M
```

Replica límites y tiempos en Apache y cualquier proxy. Verifica el progreso y
los mensajes de interrupción desde un navegador móvil y de escritorio.

`post_max_size` limita la solicitud completa, no cada archivo. El formulario
acepta hasta 500 MB por carga y permite guardar el borrador varias veces para
subir video y documentos en tandas. Si PHP descarta una solicitud por exceder
su límite, la aplicación responde con HTTP 413 antes de validar CSRF.

## Cola de correo

Programa cada minuto:

```text
php /ruta/proyecto/spark email:work 50
```

Ejecuta el comando con el mismo usuario, versión de PHP y `.env` de la
aplicación.

## Verificación posterior

- `php spark migrate:status`
- `composer validate --no-check-publish`
- Portada, categorías y páginas legales con HTTP 200.
- Rutas privadas redirigen sin sesión.
- `.env`, `writable/` y archivos privados no son accesibles por HTTP.
- CSP, `nosniff`, `SAMEORIGIN`, cookies seguras y CSRF activos.
- Prueba de correo, autenticación institucional y antivirus.

Antes de desplegar el cambio de Joven Talento a participación individual,
revisa las solicitudes existentes de esa categoría que tengan más de una
persona. La actualización no elimina integrantes ni documentos históricos de
forma automática; su tratamiento debe definirse con el propietario para
conservar la auditoría.

La publicación está bloqueada mientras existan textos legales, fechas,
contenido o configuraciones institucionales pendientes.
