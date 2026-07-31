# AGENTS.md — Tesoros Gastronómicos del Estado de México

## 1. Propósito de este documento

Este archivo es la fuente principal de contexto, alcance, reglas funcionales y criterios técnicos para cualquier agente o persona que trabaje en este repositorio.

Antes de implementar una modificación:

1. Leer este archivo completo.
2. Inspeccionar el código y los mockups relacionados.
3. Conservar las decisiones funcionales aquí documentadas.
4. No inventar información institucional, reglas de convocatoria ni contratos de integraciones pendientes.
5. Consultar al propietario del proyecto cuando una decisión no esté cubierta por este documento y pueda modificar el comportamiento o alcance.

El entregable actual asociado a la inicialización del proyecto es únicamente este archivo. La implementación del sistema se realizará posteriormente.

## 2. Objetivo del proyecto

Construir una aplicación web con CodeIgniter 4 y PHP 8.2 para la convocatoria única “Tesoros Gastronómicos del Estado de México”, rumbo a París 2026.

La plataforma debe:

- Mostrar públicamente la convocatoria y sus cuatro categorías.
- Presentar las bases, requisitos, fechas, beneficios, preguntas frecuentes y demás contenido de cada categoría.
- Permitir que una persona registre una sola participación en todo el programa.
- Permitir guardar solicitudes como borrador.
- Generar un folio desde la creación del borrador.
- Recibir formularios, documentos, fotografías, videos o enlaces de video.
- Permitir consultar el estado de una solicitud mediante correo, folio y código temporal enviado por correo.
- Permitir corregir exclusivamente los documentos que un administrador desbloquee.
- Proporcionar un panel administrativo para consultar y gestionar participantes.
- Autenticar a los administradores mediante una API institucional de accesos cuya documentación será proporcionada posteriormente.
- Mantener bitácora de operaciones relevantes.
- Enviar notificaciones por correo en los eventos definidos en este documento.

No es una plataforma genérica ni reutilizable para futuras convocatorias. No se debe introducir complejidad de multiconvocatoria, multitenencia o edición dinámica de categorías.

La fecha máxima indicada para la entrega del proyecto completo es el lunes 3 de agosto de 2026.

## 3. Estado inicial del repositorio

El repositorio contiene mockups HTML terminados y aprobados, documentos fuente e imágenes.

Mockups principales:

- `Tesoros Gastronomicos Portada.dc.html`
- `Convocatoria Cocineras Tradicionales.dc.html`
- `Convocatoria Joven Talento.dc.html`
- `Convocatoria Bebidas Tradicionales.dc.html`

Otros recursos:

- `uploads/`: imágenes, logotipos y documentos fuente.
- `doc2.docx`: contenido fuente de cocineras y cocineros tradicionales.
- `support.js`: runtime utilizado por los archivos `.dc.html`; no es lógica de negocio.
- `Canvas.dc.html`: lienzo vacío sin funcionalidad relevante.

Los archivos `.dc.html` son mockups, no la arquitectura final. Deben utilizarse como referencia visual y de contenido, no como vistas de producción sin adaptar.

## 4. Fuente de verdad visual

Los mockups están aprobados.

Reglas obligatorias:

- Respetar fielmente el diseño en escritorio: identidad, paleta, jerarquía, tipografía, espaciado, imágenes, tarjetas, secciones y tono institucional.
- Adaptar el marcado a vistas de CodeIgniter 4 y eliminar la dependencia de `<x-dc>`, `<helmet>`, `support.js` y atributos propios del generador.
- Extraer estructuras repetidas a layouts y parciales.
- No mantener miles de estilos inline cuando puedan organizarse de forma reutilizable.
- Usar Bootstrap instalado localmente mediante npm. No cargar Bootstrap ni frameworks CSS desde CDN.
- En móvil, reorganizar la interfaz para ofrecer una UX adecuada; no conservar el ancho mínimo de 1280 px de los mockups.
- Mantener los recursos gráficos aprobados, salvo instrucción expresa del propietario.
- No sustituir imágenes o logotipos por alternativas no autorizadas.
- El sitio será únicamente en español.

La portada debe enlazar realmente a la página de cada categoría. Los enlaces de registro, consulta de folio, requisitos, fechas y preguntas deben apuntar a rutas o secciones existentes.

## 5. Categorías fijas

Las cuatro categorías definitivas son:

1. Cocineras y Cocineros Tradicionales.
2. Restaurantes.
3. Joven Talento Universitario en Gastronomía.
4. Productoras y Productores de Bebidas Tradicionales y Ancestrales.

Las categorías y su contenido no necesitan administración CRUD.

La categoría Restaurantes utiliza las bases y la ficha de inscripción proporcionadas por el propietario el 31 de julio de 2026. La fecha límite de inscripción y el correo electrónico oficial permanecen pendientes y deben mostrarse como tales.

También existen datos aún pendientes en otros mockups. Todo texto como “por confirmar”, “próximamente” o “pendiente de definir” debe conservarse como pendiente hasta recibir información del propietario.

## 6. Restricción de participación

Una persona solo puede participar una vez en toda la convocatoria, sin importar la categoría.

La CURP es el identificador para impedir duplicados:

- Debe normalizarse a mayúsculas y sin espacios.
- Debe validarse estructuralmente.
- Debe tener una restricción única global para participantes activos o registrados.
- La verificación no debe depender solamente de JavaScript.
- Debe realizarse también dentro de una transacción en el servidor para evitar condiciones de carrera.

En Joven Talento:

- La participación es individual.
- Cada alumna o alumno administra sus propios datos, documentos y propuesta bajo un folio individual.
- La CURP de la persona participante debe comprobarse contra la restricción de participación única.
- El video dura máximo tres minutos y presenta a la persona participante elaborando la quiché con la que competirá.

En Restaurantes, la persona responsable o representante debe identificarse mediante CURP y queda sujeta a la misma restricción.

El correo debe ser único por solicitud. Dado que una persona solo puede participar una vez, no se debe permitir que el mismo correo genere múltiples solicitudes.

## 7. Folios

El folio se genera al crear el borrador, no al enviar la solicitud.

Formatos aprobados:

- `TG-2026-CCT-000001`: Cocineras y Cocineros Tradicionales.
- `TG-2026-RES-000001`: Restaurantes.
- `TG-2026-JTG-000001`: Joven Talento Universitario en Gastronomía.
- `TG-2026-BTA-000001`: Bebidas Tradicionales y Ancestrales.

Reglas:

- La secuencia es numérica, de seis dígitos y segura ante concurrencia.
- El folio es único e inmutable.
- Nunca calcular el siguiente folio con un `MAX + 1` sin bloqueo o mecanismo transaccional.
- El folio no sustituye la llave primaria interna.
- Un folio generado permanece en auditoría aunque la solicitud sea cancelada.

## 8. Flujo del participante

### 8.1 Creación y borrador

El participante:

1. Selecciona una categoría.
2. Captura como mínimo los datos necesarios para identificar la solicitud, incluida CURP y correo.
3. Acepta los textos legales provisionales vigentes.
4. Obtiene un folio.
5. Recibe el folio por correo.
6. Continúa llenando y guardando la solicitud como borrador.

Mientras el estado sea `borrador`:

- Puede modificar todos los campos.
- Puede cargar, reemplazar o eliminar archivos.
- Puede guardar y continuar posteriormente.
- El servidor debe validar todos los datos; la validación del navegador es complementaria.

### 8.2 Envío definitivo

Antes de enviar:

- Mostrar un resumen completo.
- Validar los campos y documentos obligatorios de la categoría.
- Solicitar confirmación expresa.
- Registrar la aceptación de declaraciones y textos legales.

Después del envío:

- El estado pasa a `enviada`.
- El participante ya no puede modificar datos ni archivos.
- No se debe ofrecer edición de “datos básicos” después del envío.
- Los archivos quedan bloqueados.
- Se registra el evento en auditoría.
- Se envía confirmación por correo.

### 8.3 Consulta y acceso temporal

No habrá contraseña permanente para participantes.

Flujo obligatorio:

1. El participante captura correo y folio.
2. Si la combinación es válida, se envía un código temporal al correo registrado.
3. El participante captura el código.
4. Si es válido, obtiene una sesión temporal para consultar su solicitud.

Reglas del código:

- Vigencia: 10 minutos.
- Máximo: 5 intentos.
- Reenvío disponible después de 60 segundos.
- Guardar únicamente un hash del código.
- Invalidar el código al utilizarlo correctamente.
- Invalidar códigos anteriores cuando se emita uno nuevo.
- Aplicar límites por correo, folio, IP y sesión.
- No revelar si un correo o folio existe mediante mensajes diferentes.
- Registrar solicitudes, fallos, reenvíos y accesos exitosos.

La sesión del participante debe permitir:

- Ver el folio.
- Ver la categoría.
- Ver el estado actual.
- Ver el comentario del administrador cuando exista una corrección solicitada.
- Corregir solamente un archivo desbloqueado por el administrador.
- Cancelar la solicitud cuando el estado lo permita.
- Cerrar la sesión.

No debe exponer datos o archivos de otras solicitudes.

## 9. Estados y transiciones

Estados oficiales:

- `borrador`
- `enviada`
- `en_revision`
- `incompleta`
- `seleccionada`
- `rechazada`
- `cancelada`

Transiciones esperadas:

- Creación → `borrador`
- `borrador` → `enviada`
- `enviada` → `en_revision`
- `enviada` o `en_revision` → `incompleta`
- `incompleta` → `enviada`, después de la corrección
- `enviada` o `en_revision` → `seleccionada`
- `enviada` o `en_revision` → `rechazada`
- `borrador`, `enviada` o `incompleta` → `cancelada`

La cancelación:

- Solo está permitida en `borrador`, `enviada` o `incompleta`.
- Es irreversible para el participante.
- No elimina físicamente la solicitud ni su historial.
- Debe solicitar confirmación explícita.
- Debe quedar en auditoría.

No introducir otros estados sin autorización.

## 10. Corrección de documentos

El administrador puede marcar una solicitud como `incompleta`, escribir un comentario y desbloquear únicamente el documento que debe corregirse.

El participante:

- Ve el comentario al acceder.
- Puede reemplazar exclusivamente el archivo desbloqueado.
- No puede cambiar campos ni otros documentos.
- Debe confirmar el reenvío.

Al reenviar:

- Conservar el archivo anterior de forma privada para auditoría.
- Crear una nueva versión del documento.
- Bloquear nuevamente el campo.
- Cambiar automáticamente la solicitud a `enviada`.
- Registrar actor, fecha, comentario, versión anterior y versión nueva.
- Notificar por correo.

Los archivos nunca deben sobrescribirse físicamente sin conservar la trazabilidad.

## 11. Archivos y videos

Formatos permitidos:

- PDF
- JPG
- JPEG
- MP4

Tamaño máximo:

- 500 MB por archivo.

También se puede registrar un enlace de video:

- Debe utilizar HTTPS.
- Puede pertenecer a cualquier proveedor.
- Debe validarse como URL HTTPS válida.
- Tratarlo como contenido externo no confiable.
- No incrustar HTML proporcionado por el usuario.

Almacenamiento:

- Disco local del servidor.
- Fuera del directorio público.
- Nombres físicos aleatorios, no controlados por el usuario.
- Metadatos almacenados en base de datos.
- Descarga y visualización siempre a través de un controlador autorizado.

Seguridad obligatoria:

- Validar extensión y MIME real.
- Verificar que el tipo detectado corresponda al permitido.
- Bloquear nombres peligrosos y dobles extensiones.
- Generar nombres internos no predecibles.
- Evitar ejecución de archivos cargados.
- Incorporar análisis antivirus.
- Eliminar metadatos de imágenes cuando sea viable.
- No confiar en `Content-Type` enviado por el navegador.
- Proteger contra traversal, acceso directo y enumeración.
- Configurar explícitamente límites de PHP, servidor web y proxy para 500 MB.
- Proporcionar progreso de carga y mensajes claros ante interrupciones.

Los archivos son privados:

- Los administradores pueden visualizar y descargar documentos individuales.
- No se requiere descargar un expediente completo en un solo paquete.
- El participante puede cargar sus archivos durante el flujo autorizado, pero no obtiene URLs públicas.

La política definitiva de retención y eliminación se definirá posteriormente. Preparar la arquitectura para implementarla sin inventar plazos.

## 12. Panel administrativo

Solo existe un rol funcional: `administrador`.

El panel debe permitir:

- Ver tablero general.
- Consultar totales por categoría, municipio y estado.
- Listar solicitudes con paginación.
- Buscar y filtrar.
- Abrir el detalle de una solicitud.
- Consultar datos, documentos e historial.
- Visualizar o descargar archivos individuales.
- Editar datos personales.
- Agregar comentarios.
- Cambiar el estado conforme a las transiciones permitidas.
- Marcar una solicitud como incompleta.
- Desbloquear un documento específico para corrección.
- Consultar bitácoras.
- Exportar resultados filtrados a CSV o Excel.

El administrador no debe:

- Alterar el folio.
- Cambiar la categoría de una solicitud.
- Cambiar CURP de manera que evada la unicidad.
- Sustituir silenciosamente documentos.
- Borrar definitivamente una solicitud desde la interfaz normal.
- Inventar estados o criterios de evaluación.

No se necesita:

- Gestión de roles múltiples.
- Asignación de evaluadores.
- Rúbricas o puntajes.
- Evaluación anónima.
- CRUD de categorías o catálogos.
- Lista pública de seleccionados.
- Descarga masiva de expedientes completos.

Las personas seleccionadas serán notificadas por correo y también podrán observar el estado `seleccionada` al acceder a la consulta privada.

## 13. Autenticación administrativa mediante API

La autenticación administrativa dependerá de una API institucional de accesos.

La documentación todavía no está disponible. Por lo tanto:

- La integración queda bloqueada hasta recibir documentación, endpoints, credenciales de prueba y reglas de seguridad.
- No inventar endpoints, campos, tokens, roles ni respuestas.
- No crear credenciales administrativas predeterminadas para producción.
- No simular que la integración está terminada.

La arquitectura debe desacoplar la autenticación:

- Definir un contrato o servicio de autenticación administrativa.
- Mantener el cliente HTTP fuera de controladores.
- Centralizar timeouts, errores, validación TLS, tokens y cierre de sesión.
- Permitir implementar el proveedor real sin reescribir el panel.
- Denegar el acceso de forma segura si el proveedor no está configurado.
- Documentar claramente el bloqueo pendiente.

Cuando llegue la documentación, confirmar antes de implementar:

- Protocolo de autenticación.
- Inicio y cierre de sesión.
- Renovación y expiración.
- Estructura del usuario.
- Roles o permisos proporcionados.
- Manejo de indisponibilidad.
- MFA, si aplica.
- Ambientes y secretos.

## 14. Correos

Se utilizará una cuenta o servicio de correo institucional. La configuración concreta se proporcionará posteriormente y debe residir en variables de entorno.

Enviar correos en estos eventos:

- Creación de borrador y folio.
- Código temporal de acceso.
- Envío definitivo.
- Solicitud marcada como incompleta y documento solicitado.
- Recepción de la corrección.
- Cambio a seleccionada.
- Cambio a rechazada.
- Cancelación.

Reglas:

- Usar plantillas consistentes con la identidad visual.
- No incluir documentos personales adjuntos.
- No exponer información sensible innecesaria.
- Registrar resultado del envío y reintentos.
- Un fallo de correo no debe corromper la transacción principal.
- Diseñar una cola o mecanismo de reintento apropiado.
- Evitar duplicados mediante idempotencia.

No se requieren SMS ni WhatsApp.

## 15. Protección contra abuso en formularios públicos

Por instrucción expresa del propietario del 31 de julio de 2026, Google
reCAPTCHA fue retirado de todos los formularios. Conservar protección CSRF,
respuestas genéricas, auditoría y límites de solicitudes por IP y sesión.

## 16. Accesibilidad

La aplicación debe incluir un botón visible de accesibilidad con estas funciones:

- Aumentar y reducir tamaño de texto.
- Alto contraste.
- Escala de grises.
- Subrayado o resaltado de enlaces.
- Tipografía más legible.
- Detener o reducir animaciones.
- Restablecer preferencias.

Además:

- Usar HTML semántico.
- Mantener navegación completa por teclado.
- Proporcionar foco visible.
- Asociar correctamente etiquetas y controles.
- Usar mensajes de error comprensibles.
- Incluir textos alternativos apropiados.
- No depender únicamente del color.
- Respetar `prefers-reduced-motion`.
- Persistir preferencias de accesibilidad sin almacenar datos personales.
- No presentar el botón como sustituto de una interfaz accesible desde su base.

## 17. Contenido legal provisional

Todavía no existen textos finales de:

- Aviso de privacidad.
- Términos y condiciones.
- Política de conservación de información.
- Consentimiento definitivo de tratamiento de datos e imagen.

Se deben preparar:

- Páginas y rutas correspondientes.
- Versionado de documentos legales.
- Casillas obligatorias cuando aplique.
- Registro de versión, fecha, IP y momento de aceptación.

El contenido temporal debe estar marcado claramente como ficticio o provisional en el ambiente de desarrollo. Nunca debe publicarse en producción como texto legal definitivo. La salida a producción queda condicionada a recibir y cargar los textos aprobados.

## 18. Arquitectura técnica

### 18.1 Base

- PHP 8.2.
- Última versión estable de CodeIgniter 4 compatible con PHP 8.2 al iniciar la implementación.
- Composer.
- MySQL.
- Bootstrap instalado localmente mediante npm.
- Zona horaria: `America/Mexico_City`.
- Idioma: español.

Fijar versiones mediante `composer.lock`. No actualizar dependencias mayores sin revisar compatibilidad.

### 18.2 Convenciones de CodeIgniter

- `public/` debe ser la única raíz pública.
- Usar rutas nombradas y agrupar rutas públicas, de participante y administrativas.
- Usar controladores delgados.
- Colocar reglas de negocio en servicios.
- Usar entidades o DTO cuando ayuden a evitar arreglos ambiguos.
- Usar Models de CodeIgniter con campos permitidos explícitos.
- Utilizar migrations y seeders.
- Usar filtros para sesiones, acceso administrativo y controles aplicables.
- Configurar secretos exclusivamente mediante `.env`.
- No confirmar `.env`, credenciales, claves o datos reales.
- Habilitar protección CSRF.
- Escapar salida por defecto.
- Aplicar validación del servidor en cada operación.
- Utilizar transacciones para folios, participación única, envío y correcciones.
- Implementar manejo de errores sin mostrar trazas o secretos en producción.

### 18.3 Organización sugerida

Separar al menos:

- Sitio público y páginas de convocatoria.
- Registro y borradores.
- Acceso temporal de participantes.
- Gestión de solicitudes.
- Gestión segura de archivos.
- Administración.
- Autenticación administrativa.
- Correos.
- Auditoría.
- Exportaciones.
- Accesibilidad y componentes compartidos.

No introducir una SPA si no aporta una necesidad concreta. Priorizar vistas renderizadas en servidor con JavaScript progresivo.

## 19. Modelo de datos mínimo

El modelo final debe normalizarse y documentarse. Como mínimo considerar:

- `categories`
  - Claves fijas, nombre y prefijo de folio.
- `applications`
  - Folio, categoría, correo, CURP responsable, estado, fechas y control de versión.
- `participants`
  - Personas relacionadas con la solicitud; Joven Talento registra una sola persona participante.
- `application_personal_data`
  - Datos personales editables por administración.
- Tablas o estructuras específicas por categoría
  - Información gastronómica, institucional, comercial o de propuesta.
- `documents`
  - Tipo requerido, versión activa, bloqueo y estado de corrección.
- `document_versions`
  - Ruta privada, nombre original, MIME, tamaño, hash, versión y auditoría.
- `video_links`
  - URL HTTPS y metadatos mínimos.
- `access_codes`
  - Hash, expiración, intentos, uso y controles de abuso.
- `participant_sessions`
  - Sesiones temporales revocables.
- `admin_comments`
  - Comentarios asociados a solicitud o documento.
- `status_history`
  - Estado anterior, nuevo, actor, fecha y comentario.
- `audit_log`
  - Eventos sensibles.
- `email_queue` o equivalente
  - Plantilla, destinatario, estado, intentos e idempotencia.
- `legal_documents`
  - Tipo, versión, contenido y vigencia.
- `legal_acceptances`
  - Solicitud, versión aceptada, fecha e información de auditoría.
- `folio_sequences`
  - Secuencia segura por categoría, si se utiliza esta estrategia.

No almacenar archivos binarios grandes dentro de MySQL.

## 20. Auditoría

Registrar, como mínimo:

- Creación del borrador.
- Generación del folio.
- Guardados relevantes.
- Envío definitivo.
- Solicitud y verificación de códigos.
- Accesos del participante.
- Accesos administrativos.
- Visualización y descarga administrativa de documentos.
- Cambios de datos personales.
- Cambios de estado.
- Comentarios administrativos.
- Desbloqueo y reemplazo de documentos.
- Exportaciones.
- Cancelación.
- Intentos fallidos y eventos de seguridad relevantes.

Cada registro debe incluir:

- Tipo de actor.
- Identificador del actor cuando exista.
- Solicitud afectada.
- Acción.
- Fecha y hora.
- IP y agente de usuario cuando sea pertinente.
- Datos anteriores y nuevos de forma controlada.

No registrar códigos temporales, tokens, secretos ni contenido completo de documentos.

## 21. Fechas y cierre automático

Todas las comparaciones deben utilizar `America/Mexico_City`.

Al alcanzar la fecha de cierre:

- Bloquear automáticamente nuevas solicitudes.
- Bloquear el envío de borradores, salvo decisión posterior del propietario.
- Bloquear modificaciones normales.
- Mantener disponible la consulta de estado.
- Mantener disponibles correcciones de documentos expresamente solicitadas por administración, si el administrador las habilita.
- Mostrar mensajes claros con la fecha oficial.

No confiar solo en ocultar botones; el servidor debe hacer cumplir el cierre.

Las fechas aún no confirmadas no deben codificarse como definitivas.

## 22. Seguridad y privacidad

La aplicación manejará CURP, identificaciones, domicilios, pasaportes, material audiovisual y otros datos personales.

Aplicar:

- Principio de mínimo privilegio.
- TLS en producción.
- Cookies `Secure`, `HttpOnly` y `SameSite`.
- Regeneración de sesión.
- Protección CSRF.
- Rate limiting.
- Consultas parametrizadas.
- Escape de salida.
- CSP y cabeceras de seguridad compatibles con los CDN autorizados.
- Restricción de archivos fuera de `public/`.
- URLs de descarga autorizadas y no predecibles.
- Validación estricta de redirecciones y URLs externas.
- Protección contra enumeración de folios.
- Enmascaramiento de datos sensibles en listados y logs.
- Copias de seguridad y restauración documentadas.

No utilizar datos personales reales en seeds, capturas, ejemplos o documentación.

## 23. Exportaciones

El panel debe exportar los resultados filtrados a CSV o Excel.

La exportación:

- Respeta los filtros aplicados.
- Registra quién la realizó y cuándo.
- No incluye rutas físicas ni secretos.
- Escapa contenido que pueda producir inyección de fórmulas.
- Solo incluye datos necesarios para la gestión.
- No incorpora archivos adjuntos.

## 24. Responsive y navegadores

Soportar versiones vigentes de los navegadores más utilizados:

- Chrome.
- Edge.
- Firefox.
- Safari.
- Navegadores móviles basados en Chromium y Safari móvil.

Requisitos:

- Sin desplazamiento horizontal accidental.
- Formularios utilizables con teclado y pantallas táctiles.
- Tablas administrativas adaptables o con estrategia móvil clara.
- Imágenes optimizadas y responsivas.
- Cargas de archivo con estados de progreso, error y reintento comprensibles.
- Diseño de escritorio fiel a los mockups.
- Reordenamiento móvil orientado a legibilidad y finalización del formulario.

## 25. Pruebas y verificación

No se solicitan pruebas automatizadas como requisito del proyecto.

Sin embargo, cada cambio debe verificarse proporcionalmente mediante:

- Validación de sintaxis.
- Ejecución de migrations en una base limpia.
- Pruebas manuales de rutas y formularios.
- Verificación de permisos y acceso a archivos.
- Pruebas de estados y transiciones.
- Pruebas del flujo de código temporal.
- Pruebas de cierre de convocatoria.
- Pruebas de responsive en escritorio y móvil.
- Revisión de accesibilidad básica.
- Revisión de consola del navegador y logs del servidor.
- Verificación de que no existan secretos o datos personales en el repositorio.

No afirmar que una integración pendiente funciona si no se ha probado con el servicio real.

## 26. Documentación requerida

El proyecto final debe incluir:

- README de instalación local.
- Requisitos de PHP y extensiones.
- Configuración de MySQL.
- Variables de entorno documentadas en `.env.example`.
- Ejecución de migrations y seeders.
- Configuración de correo.
- Configuración de almacenamiento y límites de 500 MB.
- Configuración de tareas programadas o colas.
- Despliegue con `public/` como raíz.
- Copias de seguridad y restauración.
- Manual breve del participante.
- Manual del administrador.
- Descripción de estados y flujos.
- Modelo de datos.
- Documentación de la futura integración con la API de accesos.
- Lista explícita de pendientes institucionales y legales.

## 27. Fuera de alcance

No implementar sin una nueva autorización:

- Aplicación móvil nativa.
- Multidioma.
- Múltiples convocatorias o ediciones históricas administrables.
- Lista pública de seleccionados.
- Pagos.
- SMS o WhatsApp.
- Evaluadores, rúbricas o puntuaciones.
- Gestión avanzada de roles.
- Sustitución libre de documentos después del envío.
- Edición del contenido institucional mediante CMS.
- Almacenamiento en nube de archivos.
- Integración inventada con la API de accesos.

## 28. Pendientes bloqueantes conocidos

Requieren información posterior del propietario:

- Fechas y requisitos todavía marcados como pendientes.
- Beneficios y apoyos de viaje.
- Reglas definitivas de muestras o degustaciones.
- Aviso de privacidad y términos legales aprobados.
- Política de retención y eliminación.
- Credenciales y configuración del correo institucional.
- Documentación de la API institucional de accesos.
- Criterios definitivos de aceptación de la primera versión.

Estos pendientes deben mostrarse en documentación y configuración. No resolverlos mediante supuestos silenciosos.

## 29. Definición general de terminado

Hasta que el propietario establezca criterios adicionales, una funcionalidad se considera terminada cuando:

- Respeta este documento.
- Está integrada en CodeIgniter 4.
- Conserva el diseño aprobado en escritorio.
- Funciona correctamente en móvil.
- Valida datos tanto en cliente como en servidor.
- Respeta permisos, estados y auditoría.
- No expone archivos ni datos personales.
- Maneja errores de forma comprensible.
- Está documentada.
- Fue verificada manualmente en los flujos afectados.
- No depende de contenido o integraciones ficticias sin indicarlo claramente.

## 30. Regla final para agentes

Ante un conflicto:

1. Las instrucciones explícitas más recientes del propietario tienen prioridad.
2. Después, aplicar este `AGENTS.md`.
3. Después, respetar los mockups como fuente visual y de contenido.
4. Finalmente, seguir las convenciones existentes del código.

Si una solicitud implica cambiar reglas de participación, seguridad, documentos, estados, autenticación, contenido aprobado o alcance, detenerse y solicitar confirmación antes de implementarla.
