# Arquitectura base

## Capas

- `app/Controllers`: recibe solicitudes HTTP, valida la intención y delega. No contiene reglas de negocio extensas.
- `app/Services`: casos de uso e integraciones desacopladas.
- `app/Models`: persistencia con `allowedFields` explícitos.
- `app/Entities`: representación tipada del dominio cuando evite arreglos ambiguos.
- `app/Validation`: reglas reutilizables de validación.
- `app/Filters`: autorización y controles transversales de rutas.
- `app/Views`: layouts, parciales y vistas renderizadas en servidor.
- `app/Database/Migrations`: esquema versionado.
- `app/Database/Seeds`: catálogos fijos y datos no personales.

## Áreas HTTP

### Pública

Controladores derivados de `PublicController`. Contiene portada, categorías y contenido institucional.

### Participante

Controladores derivados de `ParticipantController`. Las rutas privadas utilizan
`ParticipantSessionFilter`. La autenticación OTP no usa contraseñas permanentes
y mantiene sesiones revocables ligadas a una sola solicitud.

### Administración

Controladores derivados de `AdminController`. `AdminAuthFilter` consulta el
contrato `AdminAuthProviderInterface`; el proveedor Digital Neza valida
`login`, `me` y `logout`, conserva el token únicamente en sesión de servidor y
deniega acceso si la integración no está configurada o disponible.

## Reglas

- Autorouting desactivado.
- Rutas nombradas.
- Reglas de negocio dentro de servicios.
- Escrituras relacionadas dentro de transacciones.
- Archivos privados bajo `writable/private/uploads`.
- Ningún controlador entrega una ruta física.
- No registrar CURP, códigos OTP, tokens, contraseñas o contenido de documentos.
- Toda salida procedente de usuarios se escapa.
- Las credenciales viven exclusivamente en `.env`.

## Integraciones

Correo, antivirus y autenticación administrativa están encapsulados
en servicios. La cola `email_queue` desacopla notificaciones; los archivos pasan
por almacenamiento privado y análisis antivirus; el cliente HTTP institucional
centraliza TLS, tiempos de espera y errores.

## Flujo de formularios E04

`DraftApplicationService` crea el borrador inicial y
`ApplicationWorkflowService` concentra edición, validación, resumen y envío.
La sesión conserva únicamente el identificador interno de la solicitud
autorizada. Los perfiles específicos mantienen columnas consultables y un JSON
con el resto de la captura.

Cada guardado bloquea la solicitud, incrementa su versión y registra historial.
El envío vuelve a validar dentro de una transacción, registra la aceptación
vigente y cambia el estado a `enviada`. Desde ese momento el servicio rechaza
toda modificación.

## Observabilidad y reportes

`application_histories` conserva la historia del dominio y `audit_log` los
eventos operativos y de seguridad. Ninguno registra códigos, tokens o contenido
documental. `AdminApplicationService` aplica una sola definición de filtros para
listado y exportación; `CsvExportService` controla las columnas y neutraliza
fórmulas.
