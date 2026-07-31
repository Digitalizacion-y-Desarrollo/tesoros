# Modelo de datos de solicitudes

Este documento describe el dominio persistente implementado en el épico E03.

## Relaciones

```text
categories 1 ── 1 folio_counters
categories 1 ── n applications
applications 1 ── n participants
applications 1 ── n application_histories
applications 1 ── n documents 1 ── n document_versions
applications 1 ── 0..1 application_videos
applications 1 ── n access_codes
applications 1 ── n participant_sessions
applications 1 ── n admin_comments
applications 1 ── n email_queue
applications 1 ── n audit_log
applications 1 ── 0..1 cook_profiles
applications 1 ── 0..1 restaurant_profiles
applications 1 ── 0..1 student_team_profiles
applications 1 ── 0..1 beverage_profiles
```

Cada solicitud pertenece a una categoría fija y tiene exactamente un perfil del
tipo correspondiente. Joven Talento almacena una persona participante bajo un
folio individual. Las demás categorías comienzan con una persona responsable.

## Integridad

- `applications.folio` es único e inmutable.
- `applications.email_hash` es único. Se calcula con SHA-256 sobre el correo
  normalizado en minúsculas y permite conservar el correo completo de hasta 254
  caracteres sin exceder el límite de índices del motor.
- `participants.curp` es única globalmente.
- `participants(application_id, member_number)` es único.
- Los perfiles de categoría utilizan `application_id` como llave primaria, por
  lo que solo puede existir uno por solicitud.
- Todas las tablas del dominio usan InnoDB para soportar transacciones, bloqueo
  de filas y claves foráneas.

## Normalización

La CURP se convierte a mayúsculas, elimina espacios y se valida contra su
estructura oficial de 18 caracteres antes de cualquier escritura.

El correo se recorta, se convierte a minúsculas y se valida antes de calcular
su hash.

Estas validaciones mejoran los mensajes, pero la protección final contra
duplicados se mantiene en restricciones únicas de MySQL.

## Generación de folios

La creación de un borrador usa una sola transacción:

1. Selecciona con `FOR UPDATE` el contador de la categoría.
2. Incrementa la secuencia.
3. Forma `TG-2026-{PREFIJO}-{SECUENCIA}`.
4. Inserta solicitud, participantes, perfil e historial.
5. Confirma la transacción.

Si cualquier escritura falla, el contador también se revierte. El bloqueo se
realiza por categoría, por lo que distintas categorías no compiten por la misma
fila.

## Estados

Los valores canónicos están en `App\Domain\ApplicationStatus`:

- `borrador`
- `enviada`
- `en_revision`
- `incompleta`
- `seleccionada`
- `rechazada`
- `cancelada`

Las transiciones están centralizadas en `ApplicationStatus` y
`ApplicationLifecycleService`. Los cambios se realizan con bloqueo de fila,
historial y notificaciones posteriores a la confirmación.

## Tablas operativas

- `documents` y `document_versions`: archivo lógico, versión activa y versiones
  privadas inmutables.
- `application_videos`: archivo MP4 privado o enlace HTTPS.
- `access_codes`: hash, expiración, intentos, uso e invalidación.
- `participant_sessions`: token con hash, expiración y revocación.
- `admin_comments`: comentarios internos o visibles y documento asociado.
- `email_queue`: evento, destinatario, idempotencia, intentos y resultado.
- `legal_documents` y `legal_acceptances`: versión publicada y aceptación.
- `audit_log`: actor, acción, origen y metadatos controlados.

## Pruebas

PHPUnit utiliza exclusivamente la base `tesoros_test`. Nunca debe configurarse
para usar la base de la aplicación.

```powershell
C:\wamp64\bin\php\php8.2.29\php.exe vendor/phpunit/phpunit/phpunit --no-coverage
```

La suite comprueba normalización, perfiles, participación estudiantil individual,
unicidad global, rollback del contador y creación concurrente desde cuatro
procesos independientes.

## Datos ficticios

`DevelopmentSeeder` crea una solicitud ficticia por categoría. Solo debe
ejecutarse en desarrollo:

```powershell
php spark db:seed DevelopmentSeeder
```
