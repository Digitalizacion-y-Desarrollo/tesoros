# Inventario de información solicitada para el aviso de privacidad

Fecha de revisión técnica: 31 de julio de 2026.

Este documento describe la información que la aplicación solicita o genera en
su implementación actual. Su propósito es servir como insumo para elaborar el
aviso de privacidad de la convocatoria. **No constituye un aviso de privacidad
ni sustituye la revisión y aprobación jurídica institucional.**

## 1. Información común a todas las categorías

### Datos proporcionados al crear la solicitud

| Información | Obligatoriedad | Observaciones |
|---|---:|---|
| Correo electrónico de la solicitud | Obligatorio | Se utiliza como identificador de contacto y debe ser único por solicitud. |
| Nombre o nombres | Obligatorio | Corresponde a la persona participante, responsable o representante. |
| Primer apellido | Obligatorio | Dato de identificación. |
| Segundo apellido | Opcional | Dato de identificación. |
| CURP | Obligatorio | Se normaliza y se utiliza para impedir participaciones duplicadas en toda la convocatoria. |
| Categoría elegida | Obligatorio | Se asocia permanentemente con la solicitud. |
| Aceptación del aviso de privacidad | Obligatorio | Se registra la versión aceptada y el momento de aceptación. |

### Información generada y asociada con la solicitud

- Folio único.
- Estado de la solicitud y fechas de creación, actualización, envío o
  cancelación.
- Historial de cambios de estado, correcciones y operaciones relevantes.
- Comentarios administrativos, incluidos los que sean visibles para la persona
  participante.
- Versión y fecha de aceptación de términos, declaraciones, consentimiento de
  imagen y demás documentos legales aplicables.
- Resultado e historial operativo de notificaciones por correo.

### Datos técnicos y de seguridad

Según la operación realizada, el sistema registra o relaciona:

- Dirección IP.
- Agente de usuario del navegador.
- Fecha y hora de la operación.
- Identificadores y hashes de sesión.
- Hash del correo y del folio para controles de acceso y límites de solicitudes.
- Solicitudes, intentos, reenvíos y resultado de códigos temporales de acceso.
- Hash del código temporal; el código en texto claro no se conserva.
- Eventos de auditoría y seguridad.

### Metadatos de documentos y videos

Por cada archivo cargado se pueden conservar:

- Nombre original saneado.
- Tipo MIME detectado.
- Tamaño del archivo.
- Hash SHA-256.
- Versión del documento y fecha de carga.
- Tipo de documento y estado de bloqueo o corrección.
- Ruta privada generada por el sistema.

Para los videos se conserva el archivo MP4 privado o la URL HTTPS externa,
según la opción seleccionada. El participante debe proporcionar solamente una
de las dos opciones.

## 2. Cocineras y Cocineros Tradicionales

### Información capturada en el formulario

| Información | Obligatoriedad |
|---|---:|
| Municipio de residencia | Obligatoria |
| Teléfono de contacto | Obligatoria |
| Domicilio | Obligatoria |
| Años de experiencia | Obligatoria |
| Nombre de la receta o platillo insignia | Obligatoria |
| Origen familiar o comunitario | Obligatoria |
| Ingredientes principales | Obligatoria |
| Proceso de preparación | Obligatoria |
| Contexto cultural y vínculo con la comunidad | Obligatoria |
| Carta de motivos capturada en el formulario | Obligatoria |
| Video de la participación mediante MP4 o URL HTTPS | Opcional |

### Documentos e imágenes

| Archivo | Obligatoriedad |
|---|---:|
| Identificación oficial vigente (INE) | Obligatorio |
| Comprobante de domicilio | Obligatorio |
| Fotografía reciente de la persona participante | Obligatoria |
| Carta de motivos, máximo una cuartilla | Obligatoria |
| Fotografía del platillo | Opcional |

## 3. Restaurantes

Además de los datos comunes, la persona responsable o representante proporciona
su CURP y datos de identificación. El formulario también solicita información
del establecimiento y del chef ejecutivo.

### Datos del restaurante

| Información | Obligatoriedad |
|---|---:|
| Nombre comercial | Obligatoria |
| Razón social | Obligatoria |
| Año de fundación | Obligatoria |
| Número de sucursales | Obligatoria |
| Municipio del establecimiento | Obligatoria |
| Domicilio del establecimiento | Obligatoria |
| Teléfono del restaurante | Obligatoria |
| Correo electrónico del restaurante | Obligatoria |
| Redes sociales | Opcional |
| Semblanza del restaurante | Obligatoria |

### Datos del chef ejecutivo

| Información | Obligatoriedad |
|---|---:|
| Nombre completo | Obligatoria |
| Nacionalidad | Obligatoria |
| Número de pasaporte | Obligatoria |
| Teléfono | Obligatoria |
| Correo electrónico | Obligatoria |

### Información gastronómica y de promoción

| Información | Obligatoriedad |
|---|---:|
| Especialidad del restaurante | Obligatoria |
| Platillos tradicionales | Obligatoria |
| Platillos de autor | Obligatoria |
| Platillos insignia | Obligatoria |
| Propuesta gastronómica e identidad culinaria mexiquense | Obligatoria |
| Estrategias de promoción antes, durante y después del festival | Obligatoria |
| Impacto turístico, cultural y comercial esperado | Obligatoria |
| Video institucional y de platillos insignia mediante MP4 o URL HTTPS | Obligatorio |

### Documentos e imágenes

| Archivo | Obligatoriedad |
|---|---:|
| Licencia de funcionamiento | Obligatorio |
| Permisos vigentes y documentación de cumplimiento de obligaciones | Obligatorio |
| Evidencia de operación mínima de cinco años | Obligatorio |
| Currículum del chef ejecutivo | Obligatorio |
| Pasaporte vigente del chef ejecutivo o de la brigada representante | Obligatorio |
| Fotografía profesional principal del restaurante | Obligatoria |
| Fotografía profesional adicional del restaurante | Opcional |
| Fotografía principal de los platillos insignia | Obligatoria |
| Fotografía adicional de los platillos insignia | Opcional |
| Carta de intención firmada | Obligatoria |

La semblanza del restaurante se captura como texto dentro del formulario; no se
solicita como archivo.

## 4. Joven Talento Universitario en Gastronomía

La participación es individual. Todos los datos corresponden a una sola alumna
o alumno participante.

### Información capturada en el formulario

| Información | Obligatoriedad |
|---|---:|
| Institución educativa | Obligatoria |
| Plantel o campus | Obligatoria |
| Municipio de la institución | Obligatoria |
| Teléfono de contacto de la persona participante | Obligatoria |
| Nombre de la propuesta de quiché | Obligatoria |
| Ingredientes y cantidades | Obligatoria |
| Procedimiento | Obligatoria |
| Justificación e identidad mexiquense | Obligatoria |
| Motivos de la persona participante | Obligatoria |
| Duración declarada del video en segundos | Obligatoria; máximo 180 segundos |
| Video de presentación y elaboración de la quiché mediante MP4 o URL HTTPS | Obligatorio |

### Documentos

| Archivo | Obligatoriedad |
|---|---:|
| Identificación oficial vigente | Obligatorio |
| Pasaporte de la persona participante | Opcional |
| Carta oficial de la institución educativa | Obligatoria |
| Carta de motivos, máximo una cuartilla | Obligatoria |
| Ficha técnica de la propuesta de quiché | Obligatoria |
| Ficha de inscripción requisitada y firmada | Obligatoria |

## 5. Productoras y Productores de Bebidas Tradicionales y Ancestrales

### Información capturada en el formulario

| Información | Obligatoriedad |
|---|---:|
| Municipio de residencia o producción | Obligatoria |
| Teléfono de contacto | Obligatoria |
| Domicilio | Obligatoria |
| Nombre del proyecto, marca o unidad productiva | Obligatoria |
| Nombre de la bebida | Obligatoria |
| Tipo de bebida | Obligatoria |
| Años de experiencia | Obligatoria |
| Proceso artesanal de elaboración | Obligatoria |
| Historia y origen de la bebida | Obligatoria |
| Vínculo con la comunidad o territorio | Obligatoria |
| Motivos para participar | Obligatoria |
| Video de la participación mediante MP4 o URL HTTPS | Opcional |

### Documentos e imágenes

| Archivo | Obligatoriedad |
|---|---:|
| Identificación oficial vigente | Obligatorio |
| Comprobante de domicilio | Obligatorio |
| Fotografía de la persona productora | Obligatoria |
| Fotografía de la bebida | Obligatoria |
| Fotografía del proceso o ingredientes | Opcional |
| Evidencia de producción continua por al menos tres años | Opcional; el tipo documental está por confirmar |
| Documento fiscal del SAT | Opcional; el documento específico está por confirmar |
| Constancia o documento de RFC | Obligatorio |

## 6. Información que puede aparecer dentro de archivos de contenido libre

Las cartas, identificaciones, comprobantes, currículums, pasaportes, permisos,
fotografías y videos pueden contener información adicional que no se captura en
campos estructurados. Dependiendo del documento proporcionado, puede incluir:

- Firma autógrafa.
- Imagen y voz.
- Domicilio completo.
- Fecha de nacimiento, fotografía y claves de identificación.
- Trayectoria académica o profesional.
- Información fiscal o de operación comercial.
- Datos de terceras personas que aparezcan en documentos, brigadas, imágenes o
  material audiovisual.

La institución debe valorar jurídicamente la clasificación y el tratamiento de
estos datos, en particular CURP, identificaciones oficiales, pasaportes,
domicilios, firmas, fotografías, voz y material audiovisual.

## 7. Definiciones necesarias para elaborar el aviso definitivo

El área jurídica e institucional todavía debe proporcionar o confirmar:

- Identidad y domicilio de la persona responsable del tratamiento.
- Finalidades primarias y, si existen, finalidades secundarias por categoría.
- Fundamento legal aplicable.
- Datos que se considerarán sensibles o que requieran consentimiento expreso.
- Transferencias nacionales o internacionales, destinatarios y finalidades.
- Tratamiento de información de terceras personas incluida en documentos o
  material audiovisual.
- Plazos de conservación, bloqueo y eliminación.
- Medios para ejercer derechos ARCO y revocar el consentimiento.
- Procedimiento para limitar el uso o divulgación de los datos.
- Medios para comunicar cambios al aviso de privacidad.
- Datos de contacto de la unidad o área responsable.
- Reglas definitivas de autorización de uso de imagen y voz.

Hasta recibir estas definiciones, este inventario debe considerarse únicamente
una descripción técnica del tratamiento implementado.
