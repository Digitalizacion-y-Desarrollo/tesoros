# Tesoros Gastronómicos del Estado de México

Aplicación web para publicar y gestionar la convocatoria única “Tesoros Gastronómicos del Estado de México”, rumbo a París 2026.

La especificación funcional y técnica completa está en `AGENTS.md`.

## Estado

Los épicos E01 a E12 están implementados y se encuentran en validación:

- CodeIgniter 4.7.4.
- Compatibilidad fijada con PHP 8.2.29.
- MySQL.
- Rutas públicas, de participante y administrativas.
- Autenticación administrativa integrada con la API institucional.
- Layouts públicos y administrativos.
- Bootstrap 5 instalado localmente mediante npm.
- CSP, CSRF y cabeceras seguras.
- Migración y seeder idempotente de las cuatro categorías, sin reiniciar folios existentes.
- Almacenamiento privado fuera de `public/`.
- Portada adaptada desde el mockup aprobado.
- Páginas públicas completas para las cuatro categorías.
- Recursos gráficos institucionales publicados localmente.
- Diseño responsive sin ancho mínimo fijo.
- Navegación, preguntas frecuentes y rutas de inicio de registro.
- Panel de accesibilidad con tamaño de texto, contraste, grises, enlaces, tipografía y reducción de movimiento.
- Dominio persistente de solicitudes, participantes y perfiles por categoría.
- CURP única global, correo normalizado único y folios transaccionales por categoría.
- Acceso temporal con código enviado por correo, vigencia de 10 minutos y máximo de 5 intentos.
- Sesiones privadas revocables y aisladas por solicitud.
- Límites de solicitud por correo, folio, IP y sesión de navegador.
- Correo de confirmación con folio al crear el registro, separado del código temporal de acceso.
- Videos mediante archivo MP4 privado o enlace HTTPS en las cuatro categorías.
- Archivos privados, versiones y correcciones controladas.
- Panel administrativo, auditoría y exportación CSV filtrada.
- Cola de correos idempotente con reintentos.
- Documentos legales versionados y marcados como provisionales.

## Requisitos

- PHP 8.2 o superior. El proyecto se resuelve con plataforma Composer PHP 8.2.29.
- Extensiones: `curl`, `fileinfo`, `intl`, `json`, `mbstring`, `mysqli`, `mysqlnd` y `openssl`.
- Composer 2.
- Node.js y npm para instalar y copiar los recursos frontend.
- MySQL 8.
- Apache 2.4 con `mod_rewrite`.

## Instalación local

1. Instalar dependencias:

   ```powershell
   C:\wamp64\bin\php\php8.2.29\php.exe C:\ProgramData\ComposerSetup\bin\composer.phar install --no-dev --prefer-dist --optimize-autoloader
   ```

2. Instalar y preparar Bootstrap local:

   ```powershell
   npm install
   npm run build:assets
   ```

3. Copiar el archivo de entorno:

   ```powershell
   Copy-Item .env.example .env
   ```

4. Ajustar en `.env` la URL y las credenciales de MySQL.

5. Crear una base vacía:

   ```sql
   CREATE DATABASE tesoros
     CHARACTER SET utf8mb4
     COLLATE utf8mb4_unicode_ci;
   ```

6. Ejecutar migraciones y datos iniciales:

   ```powershell
   C:\wamp64\bin\php\php8.2.29\php.exe spark migrate --all
   C:\wamp64\bin\php\php8.2.29\php.exe spark db:seed CategorySeeder
   ```

   Para cargar solicitudes completamente ficticias en desarrollo:

   ```powershell
   C:\wamp64\bin\php\php8.2.29\php.exe spark db:seed DevelopmentSeeder
   ```

7. Abrir `http://localhost/Tesoros/`.

## Raíz pública

En producción, el `DocumentRoot` debe apuntar exclusivamente a:

```text
C:/ruta/del/proyecto/public
```

Ejemplo de VirtualHost:

```apache
<VirtualHost *:80>
    ServerName tesoros.example
    DocumentRoot "C:/ruta/del/proyecto/public"

    <Directory "C:/ruta/del/proyecto/public">
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

La instalación local incluye una regla de respaldo en `.htaccess` para que `/Tesoros/` se comporte como la raíz pública aunque WAMP continúe apuntando a `www`. Esa protección no reemplaza el `DocumentRoot` correcto en producción.

## Entornos

Variables principales:

- `CI_ENVIRONMENT`: `development`, `testing` o `production`.
- `app.baseURL`: URL canónica con `/` final.
- `app.forceGlobalSecureRequests`: debe ser `true` en producción con TLS configurado.
- `app.CSPEnabled`: debe permanecer `true`.
- `database.default.*`: conexión MySQL.
- `cookie.secure`: debe ser `true` en producción con HTTPS.
- `MAIL_*`: servidor SMTP. El entorno local puede usar un buzón de pruebas; producción sigue pendiente del correo institucional.
- `videoUploads.antivirusCommand`: comando antivirus con el marcador `{file}`; es obligatorio en producción.
- `videoUploads.allowDevelopmentAntivirusBypass`: permite omitir antivirus únicamente fuera de producción.

Nunca confirmar `.env`, tokens, contraseñas o datos personales.

## Rutas base

- `/`: portada pública de la convocatoria.
- `/convocatorias/{categoria}`: bases y contenido público por categoría.
- `/participante/registro/{categoria}`: inicio de registro para la categoría seleccionada.
- `/participante/acceso`: solicita el código con correo y folio.
- `/participante/acceso/codigo`: verifica o reenvía el código temporal.
- `/participante/salir`: revoca la sesión temporal.
- `/participante/solicitud`: área protegida del participante.
- `/participante/registro/{categoria}`: crea el borrador con correo, CURP y aceptación provisional.
- `/participante/borrador`: captura y reanuda el formulario asociado a la sesión.
- `/participante/borrador/resumen`: valida y presenta el resumen previo al envío.
- `/administracion`: área protegida administrativa.
- `/administracion/acceso`: inicio de sesión mediante la API institucional.
- `/legal/{documento}`: consulta la versión legal activa.

El autorouting está desactivado.

## Categorías iniciales

| Código | Categoría | Prefijo |
|---|---|---|
| `cocineras-cocineros-tradicionales` | Cocineras y Cocineros Tradicionales | `CCT` |
| `restaurantes` | Restaurantes | `RES` |
| `joven-talento-gastronomia` | Joven Talento Universitario en Gastronomía | `JTG` |
| `bebidas-tradicionales-ancestrales` | Productoras y Productores de Bebidas Tradicionales y Ancestrales | `BTA` |

## Almacenamiento

Los documentos privados deben guardarse bajo:

```text
writable/private/uploads/
```

Nunca deben trasladarse a `public/` ni entregarse directamente por URL. Los controladores de descarga deberán autorizar cada petición.

El límite de 500 MB debe mantenerse alineado en PHP, Apache y cualquier proxy.

Los videos MP4 se guardan bajo `writable/private/uploads/videos/{solicitud}/`
con nombres físicos aleatorios. La base de datos conserva la ruta privada,
nombre original saneado, MIME, tamaño y SHA-256. Nunca se expone la ruta física:
la consulta utiliza una ruta protegida que comprueba la sesión y la solicitud.
Como alternativa, se conserva una URL HTTPS externa, tratada como contenido no
confiable y abierta sin incrustar HTML del participante.

Apache se configura en `public/.htaccess` con límites de 500 MB y 10 minutos
para la carga. En otros servidores o proxies deben replicarse explícitamente
estos valores. En producción, la carga queda bloqueada si no se configura el
análisis antivirus.

## Seguridad

- CSRF global activado.
- Cabeceras seguras activadas.
- CSP restrictiva compatible con Bootstrap local y Google Fonts.
- Cookies `HttpOnly` y `SameSite=Lax`.
- Regeneración destructiva de sesión.
- Rutas administrativas bloqueadas si la API institucional no está configurada o no responde.
- `.htaccess` de respaldo bloquea código, configuración y dependencias.

Los logs no deben incluir CURP, códigos OTP, tokens, contraseñas ni contenido de documentos. En producción se debe reducir `logger.threshold` y desactivar la barra de depuración mediante `CI_ENVIRONMENT=production`.

### Protección contra abuso

Los formularios públicos no utilizan reCAPTCHA por decisión expresa del
propietario. Se conservan CSRF, respuestas genéricas y límites de solicitudes
por IP y sesión.

## Flujo de formularios (E04)

1. La persona selecciona una de las cuatro categorías.
2. Captura correo y datos de la persona responsable; en Joven Talento la participación es individual.
3. Acepta el aviso provisional.
4. El servidor crea transaccionalmente el borrador, folio, participantes, perfil y aceptación.
5. Se envía un correo de registro exitoso con el folio; este mensaje no contiene un código temporal.
6. La sesión queda asociada exclusivamente a esa solicitud.
7. El formulario admite guardados parciales y conserva el estado `borrador`.
8. Cada video puede proporcionarse como archivo MP4 o como enlace HTTPS, pero no mediante ambas opciones simultáneamente.
9. El resumen solo se habilita cuando todos los campos obligatorios son válidos.
10. La confirmación registra la versión de declaraciones y cambia la solicitud a `enviada`.
11. Los servicios rechazan cualquier guardado posterior al envío.

La reanudación desde otro navegador o después de perder la sesión utiliza el
código temporal del E05. No existe un acceso alternativo que permita consultar
una solicitud sin verificar el control del correo.

Los textos legales se muestran como provisionales. La fecha límite y el correo
oficial de Restaurantes permanecen pendientes. La carga de PDF, JPG, JPEG y MP4 se integra en E06; el E04 valida enlaces
externos HTTPS sin incrustar contenido proporcionado por usuarios.

### Catálogo de municipios

Los cuatro formularios reutilizan un catálogo central con los 125 municipios
del Estado de México y muestran un `datalist` en cada campo de municipio. El
servidor no confía únicamente en las sugerencias del navegador: rechaza valores
ajenos al catálogo y guarda el nombre oficial con su capitalización canónica.
El catálogo está identificado mediante las claves 15001 a 15125 publicadas por
el Consejo Estatal de Población del Estado de México.

## Acceso temporal (E05)

1. La persona captura correo y folio.
2. La respuesta pública es genérica, exista o no la combinación.
3. Si los datos corresponden, se envía un código de seis dígitos por SMTP.
4. El código vence en 10 minutos, permite 5 intentos y se guarda únicamente como hash.
5. Un reenvío está disponible después de 60 segundos e invalida códigos anteriores.
6. Al verificarlo, se crea una sesión temporal revocable vinculada a una sola solicitud.
7. La consulta muestra folio, categoría, estado y el comentario administrativo visible, cuando exista.
8. Cerrar sesión revoca el registro persistente y destruye la sesión del navegador.

Las solicitudes, fallos, reenvíos, accesos y cierres se registran sin guardar el
código ni el token en texto plano. La configuración de prueba reside únicamente
en `.env`; `.env.example` contiene variables vacías.

## Estados, correcciones y cancelación (E07)

`ApplicationLifecycleService` aplica en transacciones el grafo oficial de
estados. Administración puede iniciar revisión, seleccionar, rechazar o marcar
incompleta únicamente desde los estados autorizados. Marcar incompleta exige un
comentario visible y desbloquea un solo documento; todos los demás permanecen
bloqueados. La corrección conserva la versión anterior y retorna la solicitud a
`enviada`.

La persona participante puede cancelar únicamente desde `borrador`, `enviada`
o `incompleta`. Debe confirmar la operación y escribir el folio; la cancelación
es irreversible y no elimina datos ni archivos.

`convocation.closeAt` acepta la fecha oficial local en formato
`YYYY-MM-DD HH:MM:SS`, zona `America/Mexico_City`. Si está vacía no se inventa
una fecha. Al alcanzarla, el servidor bloquea altas, guardados y envíos, pero
mantiene consultas y correcciones expresamente habilitadas.

## Panel administrativo (E08)

El área protegida `/administracion` incluye tablero con totales por categoría,
municipio y estado, listado paginado, búsqueda, filtros, detalle, participantes,
documentos, versiones, video, comentarios e historial. Permite editar datos
personales respetando la unicidad de correo y CURP, cambiar estados autorizados
y solicitar la corrección de un documento.

Los listados enmascaran correo y CURP. No existen operaciones para eliminar
solicitudes, modificar folios o cambiar categorías. La autenticación utiliza la
API institucional configurada y no incluye usuarios administrativos ficticios.

### API institucional de accesos

La integración utiliza `POST /api/auth/login`, `GET /api/auth/me`,
`POST /api/auth/logout` y `POST /api/auth/forgot-password` del proveedor
institucional. Configura exclusivamente en `.env`:

```ini
adminAuth.baseUrl = https://accesos.digitalneza.com/
adminAuth.systemKey =
adminAuth.timeoutSeconds = 10
adminAuth.connectTimeoutSeconds = 5
adminAuth.verifyTls = true
http.caBundle =
```

El token Bearer se conserva en la sesión del servidor, nunca en HTML o
JavaScript. Cada petición protegida valida el token mediante `/api/auth/me` y
comprueba que la clave del sistema devuelta coincida con la configurada. Si el
proveedor no responde, el acceso se deniega. El cierre central se intenta
siempre y la sesión local se elimina aun si ese intento falla.

Si PHP no tiene configurados `curl.cainfo` u `openssl.cafile`, establece
`http.caBundle` con la ruta absoluta a un paquete CA vigente. No desactives la
verificación TLS para resolver errores de certificados.

No se exige un nombre de rol remoto específico porque el contrato no define uno
para Tesoros; toda cuenta activa asignada a la clave de este sistema representa
el único rol funcional local `administrador`.

## Notificaciones y correo (E09)

La tabla `email_queue` conserva destinatario, evento, plantilla, estado,
intentos, disponibilidad, resultado e identificador de idempotencia. Los eventos
cubiertos son:

- Registro exitoso y folio.
- Código temporal de acceso.
- Envío definitivo.
- Solicitud de corrección.
- Recepción de la corrección.
- Selección.
- Rechazo.
- Cancelación.

Los eventos normales se encolan después de confirmar la operación principal y
se intenta un primer envío inmediato. Un fallo SMTP no revierte el registro ni
el cambio de estado; la notificación queda pendiente con espera exponencial,
hasta cinco intentos. El código temporal se envía inmediatamente y solo se
registra su resultado: el valor del código nunca se almacena en la cola.

Configura el servidor mediante las variables `MAIL_*` de `.env`. En producción,
programa el siguiente comando cada minuto para procesar pendientes y recuperar
trabajos interrumpidos:

```powershell
C:\wamp64\bin\php\php8.2.29\php.exe spark email:work 50
```

Los mensajes usan plantillas HTML institucionales con alternativa de texto y
no adjuntan documentos personales.

## Seguridad, auditoría y legales (E10)

`audit_log` registra accesos, fallos, consultas de archivos, cambios sensibles y
exportaciones con actor, fecha, IP y agente de usuario cuando corresponde. Los
metadatos descartan contraseñas, tokens, códigos y secretos.

`legal_documents` conserva tipo, versión, vigencia y carácter provisional. Las
páginas de privacidad, términos, conservación y consentimiento de datos e
imagen muestran una advertencia explícita mientras no existan textos aprobados.

## Reportes y exportaciones (E11)

El listado administrativo combina búsqueda, categoría, estado y municipio. La
exportación CSV respeta esos filtros, neutraliza fórmulas y excluye rutas
privadas, hashes internos, secretos y adjuntos. Cada descarga queda auditada.

## Documentación operativa (E12)

- [Manual de participante](docs/participant-manual.md)
- [Manual de administración](docs/admin-manual.md)
- [Despliegue](docs/deployment.md)
- [Respaldos y restauración](docs/backup-restore.md)
- [Modelo de datos](docs/data-model.md)
- [Arquitectura](docs/architecture.md)
- [Informe de validación](docs/validation-report.md)
- [Pendientes institucionales](docs/institutional-pending.md)

## Archivos privados y versionado (E06)

Los documentos PDF, JPG y JPEG se guardan bajo
`writable/private/uploads/documents/{solicitud}/{tipo}/`; los MP4 permanecen
bajo `writable/private/uploads/videos/{solicitud}/`. Ninguna ruta física se
publica. La consulta se realiza mediante controladores protegidos que validan la
sesión del participante o la autenticación administrativa y registran el acceso.

Cada sustitución documental crea una fila nueva en `document_versions`. Quitar
un archivo del borrador solo desactiva su versión vigente y conserva el
historial privado. Al enviar, los documentos quedan bloqueados. La arquitectura
de correcciones crea otra versión, vuelve a bloquear el documento y retorna la
solicitud de `incompleta` a `enviada`.

La validación comprueba límite de 500 MB, extensión simple y MIME real. Los
JPG/JPEG se vuelven a codificar para retirar metadatos cuando GD y sus
dimensiones lo permiten. En producción, `uploads.antivirusCommand` debe contener
el marcador `{file}` y devolver código 0 para aceptar el archivo. El bypass
`uploads.allowDevelopmentAntivirusBypass` solo funciona fuera de producción.

Restaurantes solicita el expediente operativo, trayectoria, fotografías, carta
de intención y video institucional indicados en sus bases. Los requisitos no
confirmados de Bebidas permanecen opcionales y se muestran como provisionales.

## Comandos útiles

```powershell
# Revisar rutas
C:\wamp64\bin\php\php8.2.29\php.exe spark routes

# Estado de migraciones
C:\wamp64\bin\php\php8.2.29\php.exe spark migrate:status

# Revertir el último lote
C:\wamp64\bin\php\php8.2.29\php.exe spark migrate:rollback

# Validar dependencias
C:\wamp64\bin\php\php8.2.29\php.exe C:\ProgramData\ComposerSetup\bin\composer.phar validate --no-check-publish

# Ejecutar pruebas en la base independiente tesoros_test
C:\wamp64\bin\php\php8.2.29\php.exe vendor/phpunit/phpunit/phpunit --no-coverage

# Verificar SMTP fuera de producción con un mensaje ficticio
C:\wamp64\bin\php\php8.2.29\php.exe spark email:test-participant qa@tesoros.test

# Verificar la plantilla de registro exitoso
C:\wamp64\bin\php\php8.2.29\php.exe spark email:test-registration qa@tesoros.test

# Procesar hasta 50 correos pendientes o reintentos
C:\wamp64\bin\php\php8.2.29\php.exe spark email:work 50
```

La suite se detiene antes de ejecutar migraciones si la conexión `tests` apunta
a la misma base que `default`. Esto evita que una actualización de pruebas
borre solicitudes o catálogos de la instalación local.

## Verificación manual

- La portada responde con HTTP 200.
- Las cuatro rutas de categoría responden con HTTP 200.
- Las rutas de inicio de registro conservan la categoría seleccionada.
- La portada y las categorías cargan Bootstrap, estilos, scripts e imágenes desde el propio proyecto.
- El portal se reorganiza para móvil sin scroll horizontal.
- El panel de accesibilidad conserva preferencias en el navegador sin datos personales.
- `/participante/solicitud` redirige al acceso cuando no hay sesión.
- `/administracion` redirige al acceso institucional cuando no hay sesión.
- `.env`, `env`, `composer.json`, `app/`, `system/`, `vendor/` y `writable/` no se sirven por HTTP.
- La migración se ejecuta sobre una base limpia.
- El seeder genera exactamente cuatro categorías.
- No existen secretos dentro de archivos versionables.
- Las cuatro rutas de registro muestran formulario, token CSRF y viewport responsive.
- Un borrador admite guardados parciales y conserva el folio.
- Joven Talento registra una sola persona participante bajo un folio individual.
- Los cuatro campos de municipio ofrecen los 125 valores oficiales mediante `datalist`.
- El servidor rechaza municipios inexistentes y evita duplicados en el catálogo.
- El resumen rechaza campos obligatorios incompletos y enlaces de video sin HTTPS.
- El envío registra aceptación, fecha e historial y cambia el estado a `enviada`.
- Una solicitud enviada rechaza cambios posteriores en el servidor.
- El alta inicial envía la confirmación de registro y no crea ningún código temporal.
- Las cuatro categorías muestran archivo MP4 y enlace HTTPS como alternativas de video.
- El servidor rechaza ambos medios simultáneos, archivos mayores de 500 MB, MIME incorrecto y extensiones dobles.
- Los MP4 se guardan fuera de `public/` y solo su solicitud puede consultarlos.
- Una combinación válida envía el código al buzón SMTP de pruebas.
- Una combinación desconocida muestra exactamente la misma confirmación pública.
- El reenvío permanece bloqueado durante 60 segundos e invalida el código anterior.
- Un código vence después de 10 minutos y se invalida al quinto intento fallido.
- Un código correcto solo puede utilizarse una vez y abre exclusivamente su solicitud.
- Cerrar sesión impide reutilizar el token de la sesión temporal.

## Pendientes externos

- Configuración del correo institucional de producción; el SMTP local de pruebas ya está verificado.
- Textos legales definitivos.
- Fecha límite y correo oficial de Restaurantes, además de otras decisiones institucionales marcadas como pendientes.

La lista completa y vigente está en [docs/institutional-pending.md](docs/institutional-pending.md).
