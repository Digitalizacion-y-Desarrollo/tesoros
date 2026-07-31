# Informe de validación

Fecha: 30 de julio de 2026  
Entorno: desarrollo local, PHP 8.2.29, MySQL y Apache/WAMP.

## Controles ejecutados

- Sintaxis PHP de archivos modificados.
- Migraciones aplicadas sobre la base local.
- Migraciones recreadas por la suite sobre la base exclusiva `tesoros_test`.
- Pruebas de folios, unicidad, formularios, OTP, estados, archivos, correo,
  auditoría y exportaciones.
- SMTP validado contra Mailtrap.
- Rutas públicas y legales comprobadas por HTTP.
- Rutas de participante y administración comprobadas sin sesión.
- Acceso directo a almacenamiento privado bloqueado.
- Cabeceras CSP, `nosniff` y `SAMEORIGIN` comprobadas.
- Escaneo de archivos versionables para evitar secretos conocidos.
- `composer audit` y `npm audit`: cero vulnerabilidades conocidas.

## Resultados

- Sintaxis: cero errores en los archivos PHP de `app/`.
- Suite: 58 pruebas y 232 aserciones correctas.
- Composer: configuración válida.
- Bootstrap: recursos locales reconstruidos correctamente.
- Portada, cuatro categorías, acceso, registro y cuatro páginas legales: HTTP 200.
- Áreas privadas sin sesión: redirección HTTP 302.
- POST sin CSRF: HTTP 403.
- Ruta inexistente: HTTP 404.
- `.env`, `app/`, `vendor/` y `writable/private/`: HTTP 403.
- SMTP de registro y código temporal confirmado por Mailtrap.
- Logs: sin errores inesperados; los avisos de antivirus corresponden al bypass
  explícito de desarrollo y una colisión de correo corresponde a la prueba de
  unicidad.

## Validación visual pendiente de entorno

Debe repetirse antes de producción en Chrome, Edge, Firefox, Safari y móviles:

- Fidelidad a mockups en escritorio.
- Formularios completos por teclado y pantalla táctil.
- Ausencia de desplazamiento horizontal.
- Progreso de archivos cercanos a 500 MB.
- Consola sin errores.

El navegador integrado no estuvo disponible durante esta ejecución. Se
comprobaron de forma alternativa `lang="es"`, viewport, enlace de salto y
`main` semántico en ocho rutas representativas, sin detectar anchos mínimos
fijos de escritorio en el CSS. Esta evidencia no sustituye la revisión visual
multinavegador.

## Conclusión

La implementación técnica puede pasar a validación del propietario. La salida a
producción continúa bloqueada por los elementos de
`docs/institutional-pending.md`.
