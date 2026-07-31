# Manual de administración

## Acceso

Abre `/administracion/acceso` e inicia sesión con la cuenta institucional
asignada a la clave del sistema. No existen cuentas administrativas locales. Si
el proveedor no responde o la cuenta no está asignada, el acceso se deniega.

## Tablero y solicitudes

El tablero muestra totales por categoría, municipio y estado. En “Solicitudes”
puedes combinar búsqueda, categoría, municipio y estado. Los listados enmascaran
correo y CURP; el detalle completo solo aparece dentro del expediente.

## Expediente

El detalle permite:

- Consultar participantes, formulario, video y documentos.
- Abrir una versión individual de un archivo privado.
- Revisar comentarios e historial.
- Corregir datos personales respetando la unicidad global.
- Agregar comentarios internos o visibles.

No es posible cambiar folio, categoría ni eliminar definitivamente una
solicitud.

## Estados

Las transiciones permitidas se calculan en el servidor. No inventes estados.
Para marcar `incompleta` debes escribir un comentario y seleccionar un documento
vigente. Solo ese documento quedará habilitado.

`seleccionada`, `rechazada`, `incompleta` y la recepción de una corrección
generan las notificaciones definidas. Un fallo de correo no revierte el cambio.

## Exportación

El botón “Exportar CSV” conserva los filtros activos. El archivo no contiene
rutas privadas, secretos ni adjuntos. Cada exportación se registra en la
bitácora.

## Bitácora

La bitácora muestra accesos, fallos, cambios, consultas de archivos y
exportaciones. No contiene contraseñas, tokens, códigos temporales ni contenido
documental.

## Cierre de sesión

Usa siempre “Cerrar sesión”. El sistema intenta cerrar la sesión institucional
y elimina la sesión local incluso si el proveedor no responde.
