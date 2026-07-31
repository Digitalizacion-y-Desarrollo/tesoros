<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Portal extends BaseConfig
{
    /**
     * Public editorial content adapted from the approved HTML mockups.
     *
     * @var array<string, array<string, mixed>>
     */
    public array $categories = [
        'cocineras-cocineros-tradicionales' => [
            'number'      => '01',
            'name'        => 'Cocineras y Cocineros Tradicionales',
            'shortName'   => 'Cocineras tradicionales',
            'accent'      => 'wine',
            'image'       => 'hero-cocineras.png',
            'cardImage'   => 'categoria-cocineras.png',
            'eyebrow'     => 'Convocatoria estatal · Categoría 01',
            'subtitle'    => 'Saberes, recetas y técnicas que mantienen viva nuestra identidad.',
            'description' => 'Reconocemos a quienes preservan y comparten la cocina tradicional de las regiones del Estado de México.',
            'introTitle'  => 'Una cocina que cuenta nuestra historia',
            'intro'       => 'Buscamos cocineras y cocineros portadores de recetas, técnicas, ingredientes y memorias comunitarias. Su propuesta deberá expresar el arraigo familiar, municipal y cultural de la cocina mexiquense.',
            'facts'       => [
                ['title' => '¿Quiénes participan?', 'text' => 'Personas mayores de edad residentes del Estado de México con trayectoria en cocina tradicional.'],
                ['title' => '¿Qué debes presentar?', 'text' => 'Expediente, carta de motivos, video y la información de una receta o platillo insignia.'],
                ['title' => '¿Qué se evaluará?', 'text' => 'Autenticidad, preservación, legado familiar, identidad, presentación e impacto cultural.'],
                ['title' => 'Representa al Estado de México', 'text' => 'Las personas seleccionadas formarán parte de la presencia gastronómica mexiquense rumbo a París 2026.'],
            ],
            'steps' => [
                ['number' => '01', 'title' => 'Registro digital', 'text' => 'Captura tus datos y reúne la documentación solicitada.'],
                ['number' => '02', 'title' => 'Revisión documental', 'text' => 'El equipo de la convocatoria verificará que el expediente esté completo.'],
                ['number' => '03', 'title' => 'Valoración', 'text' => 'Las propuestas serán revisadas conforme a los criterios publicados.'],
                ['number' => '04', 'title' => 'Resultados', 'text' => 'Las personas seleccionadas recibirán notificación directa por correo.'],
            ],
            'requirements' => [
                'Ser mayor de 18 años y residir en alguno de los 125 municipios del Estado de México.',
                'Contar con experiencia comprobable como cocinera o cocinero tradicional.',
                'Presentar una receta o platillo vinculado con la memoria familiar o comunitaria.',
                'Tener disponibilidad para participar en las actividades derivadas de la selección.',
                'Aceptar las bases, declaraciones y tratamiento de datos de la convocatoria.',
            ],
            'documents' => [
                ['title' => 'Expediente digital', 'text' => 'Identificación, CURP, comprobante de domicilio y documentación indicada en el formulario.'],
                ['title' => 'Carta de motivos', 'text' => 'Explica tu trayectoria, vínculo con la cocina tradicional y razones para representar al estado.'],
                ['title' => 'Video de presentación', 'text' => 'Presenta tu historia, tu comunidad y la relevancia de tu práctica culinaria.'],
                ['title' => 'Información de la receta', 'text' => 'Nombre, origen, ingredientes, proceso, contexto cultural y fotografías del platillo.'],
            ],
            'criteriaTitle' => 'Seis criterios de valoración',
            'criteria' => [
                ['title' => 'Autenticidad', 'text' => 'Vínculo genuino con saberes y prácticas tradicionales.'],
                ['title' => 'Preservación', 'text' => 'Conservación de ingredientes, procesos y técnicas.'],
                ['title' => 'Legado familiar', 'text' => 'Transmisión de conocimientos entre generaciones.'],
                ['title' => 'Identidad municipal', 'text' => 'Representación del territorio y de su comunidad.'],
                ['title' => 'Presentación', 'text' => 'Claridad y calidad del expediente y del video.'],
                ['title' => 'Impacto cultural y turístico', 'text' => 'Capacidad de compartir el patrimonio gastronómico mexiquense.'],
            ],
            'benefits' => [
                'Reconocimiento como representante de la cocina tradicional mexiquense.',
                'Acompañamiento institucional durante el proceso de preparación.',
                'Participación en actividades de difusión rumbo a París 2026.',
            ],
            'faq' => [
                ['question' => '¿Puedo participar si ya concursé en otra convocatoria?', 'answer' => 'Cada persona puede registrar una sola participación en todo el programa, sin importar la categoría.'],
                ['question' => '¿Puedo guardar mi registro y continuarlo después?', 'answer' => 'Sí. Al crear el borrador recibirás un folio para continuar mientras la convocatoria permanezca abierta.'],
                ['question' => '¿Qué sucede después de enviar?', 'answer' => 'La solicitud queda bloqueada y pasa a revisión. Podrás consultar su estado mediante correo, folio y código temporal.'],
            ],
        ],
        'restaurantes' => [
            'number'      => '02',
            'name'        => 'Restaurantes',
            'shortName'   => 'Restaurantes',
            'accent'      => 'gold',
            'image'       => 'categoria-restaurantes.png',
            'cardImage'   => 'categoria-restaurantes.png',
            'eyebrow'     => 'Convocatoria estatal · Categoría 02',
            'subtitle'    => 'Patrimonio culinario mexiquense con propuesta contemporánea.',
            'description' => 'Categoría dirigida a establecimientos que reinterpretan la cocina del Estado de México con identidad y propuesta de autor.',
            'introTitle'  => 'Una propuesta gastronómica con identidad',
            'intro'       => 'La estructura de esta categoría está preparada para presentar bases, requisitos, documentación y criterios bajo el mismo lenguaje visual de las demás convocatorias.',
            'notice'      => 'Contenido provisional. Las bases definitivas de esta categoría serán publicadas cuando la institución las proporcione.',
            'facts'       => [
                ['title' => '¿Quiénes participan?', 'text' => 'Restaurantes establecidos en el Estado de México. Información provisional.'],
                ['title' => '¿Qué deben presentar?', 'text' => 'Expediente del establecimiento y propuesta gastronómica. Información provisional.'],
                ['title' => '¿Qué se evaluará?', 'text' => 'Identidad, trayectoria y propuesta culinaria. Criterios pendientes de confirmación.'],
                ['title' => 'Representa al Estado de México', 'text' => 'La información definitiva sobre selección y participación será publicada próximamente.'],
            ],
            'steps' => [
                ['number' => '01', 'title' => 'Registro digital', 'text' => 'Estructura prevista; requisitos definitivos pendientes.'],
                ['number' => '02', 'title' => 'Revisión documental', 'text' => 'La documentación oficial está pendiente de confirmación.'],
                ['number' => '03', 'title' => 'Valoración', 'text' => 'Los criterios serán publicados por la institución.'],
                ['number' => '04', 'title' => 'Resultados', 'text' => 'La notificación será privada mediante correo y consulta de folio.'],
            ],
            'requirements' => [
                'Estar establecido en el Estado de México. Requisito provisional.',
                'Designar a una persona responsable identificada mediante CURP.',
                'Presentar información del establecimiento y su propuesta gastronómica.',
                'Consultar nuevamente cuando se publiquen las bases definitivas.',
            ],
            'documents' => [
                ['title' => 'Datos del establecimiento', 'text' => 'Razón social, nombre comercial, municipio y datos de contacto. Contenido provisional.'],
                ['title' => 'Persona responsable', 'text' => 'Identificación y datos de quien administrará la solicitud.'],
                ['title' => 'Propuesta gastronómica', 'text' => 'Descripción, historia, ingredientes y vínculo con el patrimonio mexiquense.'],
                ['title' => 'Evidencias', 'text' => 'Fotografías y documentos por confirmar en las bases definitivas.'],
            ],
            'criteriaTitle' => 'Criterios pendientes de confirmación',
            'criteria' => [
                ['title' => 'Identidad', 'text' => 'Vínculo de la propuesta con el Estado de México.'],
                ['title' => 'Trayectoria', 'text' => 'Experiencia del establecimiento.'],
                ['title' => 'Propuesta', 'text' => 'Claridad y personalidad gastronómica.'],
                ['title' => 'Presentación', 'text' => 'Calidad del expediente presentado.'],
            ],
            'benefits' => [
                'Los beneficios definitivos serán publicados por la institución.',
                'La plataforma mostrará aquí la información aprobada sin modificar su estructura.',
            ],
            'faq' => [
                ['question' => '¿Las bases de Restaurantes ya son definitivas?', 'answer' => 'No. Esta página presenta una estructura provisional y no sustituye las bases oficiales.'],
                ['question' => '¿Puedo iniciar el registro?', 'answer' => 'El registro se habilitará cuando la institución confirme requisitos y contenido.'],
            ],
        ],
        'joven-talento-gastronomia' => [
            'number'      => '03',
            'name'        => 'Joven Talento Universitario en Gastronomía',
            'shortName'   => 'Joven talento',
            'accent'      => 'wine',
            'image'       => 'categoria-joven-talento.png',
            'cardImage'   => 'categoria-joven-talento.png',
            'eyebrow'     => 'Convocatoria estatal · Categoría 03',
            'subtitle'    => 'Talento joven, cocina mexiquense y técnica francesa.',
            'description' => 'Equipos universitarios desarrollan una propuesta que reúne ingredientes mexiquenses, creatividad y formación gastronómica.',
            'introTitle'  => 'Talento joven, cocina mexiquense y técnica francesa',
            'intro'       => 'Dos estudiantes integran un equipo y una de las personas será responsable del registro, los datos y los documentos de ambas. Juntos desarrollarán una propuesta para los retos culinarios de la convocatoria.',
            'facts'       => [
                ['title' => 'Equipos de dos', 'text' => 'Dos estudiantes de gastronomía pertenecientes a una institución del Estado de México.'],
                ['title' => 'Una persona responsable', 'text' => 'Un integrante administra el expediente completo y recibe las comunicaciones.'],
                ['title' => 'Propuesta culinaria', 'text' => 'El equipo presenta una quiché inspirada en ingredientes y cocina mexiquense.'],
                ['title' => 'Retos culinarios', 'text' => 'Las etapas combinan investigación, técnica, creatividad y presentación.'],
            ],
            'steps' => [
                ['number' => '01', 'title' => 'Registro del equipo', 'text' => 'Captura institución, responsable e información de ambos integrantes.'],
                ['number' => '02', 'title' => 'Selección documental', 'text' => 'Se revisan trayectoria, carta de motivos, video y ficha técnica.'],
                ['number' => '03', 'title' => 'Reto culinario', 'text' => 'Los equipos seleccionados presentan su propuesta ante el comité.'],
                ['number' => '04', 'title' => 'Resultado final', 'text' => 'La selección se comunica directamente al correo registrado.'],
            ],
            'requirements' => [
                'Integrar un equipo de dos estudiantes mayores de edad.',
                'Estar inscritas o inscritos en una licenciatura en gastronomía.',
                'Pertenecer a una institución educativa ubicada en el Estado de México.',
                'Designar a una persona responsable del expediente de ambos integrantes.',
                'Ningún integrante puede participar en otra categoría de la convocatoria.',
            ],
            'documents' => [
                ['title' => 'Expediente del equipo', 'text' => 'Identificación, CURP, constancia académica y documentación de ambos integrantes.'],
                ['title' => 'Carta de motivos', 'text' => 'Explica el interés del equipo y su relación con la cocina mexiquense.'],
                ['title' => 'Video de presentación', 'text' => 'Ambos integrantes presentan su experiencia, motivación y forma de trabajo.'],
                ['title' => 'Ficha técnica de la quiché', 'text' => 'Ingredientes, procedimiento, costos, fotografía y justificación de la propuesta.'],
            ],
            'criteriaTitle' => 'Cómo se evalúa cada etapa',
            'criteria' => [
                ['title' => 'Identidad mexiquense', 'text' => 'Uso significativo de ingredientes y referentes del estado.'],
                ['title' => 'Técnica', 'text' => 'Dominio de procesos culinarios y ejecución.'],
                ['title' => 'Creatividad', 'text' => 'Interpretación original y coherente del reto.'],
                ['title' => 'Trabajo en equipo', 'text' => 'Organización, colaboración y claridad en la presentación.'],
                ['title' => 'Viabilidad', 'text' => 'Consistencia de la ficha técnica y posibilidad de reproducción.'],
                ['title' => 'Presentación', 'text' => 'Calidad visual y argumentación de la propuesta.'],
            ],
            'benefits' => [
                'Experiencia formativa y acompañamiento durante los retos.',
                'Difusión del talento universitario y de su institución.',
                'Participación en actividades derivadas de la selección rumbo a París 2026.',
            ],
            'faq' => [
                ['question' => '¿El registro es individual?', 'answer' => 'No. La participación corresponde a un equipo de dos integrantes y comparte un solo folio.'],
                ['question' => '¿Quién puede modificar el expediente?', 'answer' => 'La persona responsable administra los datos y documentos de todo el equipo.'],
                ['question' => '¿Ambas CURP se validan?', 'answer' => 'Sí. Ninguna persona integrante puede participar en otra solicitud o categoría.'],
            ],
        ],
        'bebidas-tradicionales-ancestrales' => [
            'number'      => '04',
            'name'        => 'Productoras y Productores de Bebidas Tradicionales y Ancestrales',
            'shortName'   => 'Bebidas ancestrales',
            'accent'      => 'green',
            'image'       => 'hero-bebidas.png',
            'cardImage'   => 'categoria-bebidas.png',
            'eyebrow'     => 'Convocatoria estatal · Categoría 04',
            'subtitle'    => 'Bebidas que cuentan la historia de nuestra tierra.',
            'description' => 'Reconocemos bebidas tradicionales y ancestrales elaboradas mediante procesos artesanales con arraigo comunitario.',
            'introTitle'  => 'Bebidas que cuentan la historia de nuestra tierra',
            'intro'       => 'La convocatoria reúne a productoras y productores que preservan procesos, ingredientes, conocimientos y expresiones culturales vinculadas con sus comunidades.',
            'facts'       => [
                ['title' => '¿Quiénes participan?', 'text' => 'Personas productoras del Estado de México con experiencia en bebidas tradicionales o ancestrales.'],
                ['title' => '¿Qué puedes registrar?', 'text' => 'Una bebida y su proceso, historia, origen, identidad y relación con la comunidad.'],
                ['title' => 'Experiencia comprobable', 'text' => 'La trayectoria deberá acreditarse con documentos, fotografías o evidencias verificables.'],
                ['title' => '¿Qué se evaluará?', 'text' => 'Tradición, proceso artesanal, trayectoria, identidad, presentación y proyección.'],
            ],
            'steps' => [
                ['number' => '01', 'title' => 'Registro digital', 'text' => 'Captura los datos de la persona productora, proyecto y bebida.'],
                ['number' => '02', 'title' => 'Revisión documental', 'text' => 'Se valida el expediente y la evidencia de trayectoria.'],
                ['number' => '03', 'title' => 'Valoración', 'text' => 'La bebida se revisa conforme a siete criterios publicados.'],
                ['number' => '04', 'title' => 'Resultados', 'text' => 'Las personas seleccionadas reciben una comunicación privada.'],
            ],
            'requirements' => [
                'Ser mayor de edad y residir en el Estado de México.',
                'Elaborar una bebida tradicional o ancestral mediante un proceso artesanal.',
                'Acreditar experiencia y vínculo con la comunidad o territorio.',
                'Contar con la documentación fiscal o regulatoria que corresponda.',
                'Tener disponibilidad para atender las actividades de la convocatoria.',
            ],
            'documents' => [
                ['title' => 'Expediente digital', 'text' => 'Identificación, CURP, comprobante de domicilio y datos generales del proyecto.'],
                ['title' => 'Evidencia de trayectoria', 'text' => 'Constancias, reconocimientos, publicaciones o documentos que acrediten experiencia.'],
                ['title' => 'Documento fiscal', 'text' => 'Información fiscal aplicable a la persona, marca, empresa o proyecto.'],
                ['title' => 'Video y fotografías', 'text' => 'Presentación de la bebida, proceso artesanal, entorno de producción y producto final.'],
            ],
            'criteriaTitle' => 'Siete criterios de valoración',
            'criteria' => [
                ['title' => 'Tradición y autenticidad', 'text' => 'Continuidad de saberes y prácticas del territorio.'],
                ['title' => 'Proceso artesanal', 'text' => 'Participación directa y cuidado en la elaboración.'],
                ['title' => 'Trayectoria', 'text' => 'Experiencia comprobable de la persona productora.'],
                ['title' => 'Representatividad cultural', 'text' => 'Relación con la comunidad y la identidad mexiquense.'],
                ['title' => 'Originalidad e identidad', 'text' => 'Características propias de la bebida y su relato.'],
                ['title' => 'Presentación y video', 'text' => 'Claridad y calidad de la evidencia presentada.'],
                ['title' => 'Proyección internacional', 'text' => 'Capacidad de comunicar su valor en otros contextos.'],
            ],
            'benefits' => [
                'Reconocimiento y difusión de la bebida y su proceso artesanal.',
                'Acompañamiento institucional durante la preparación.',
                'Participación en actividades derivadas de la selección rumbo a París 2026.',
            ],
            'faq' => [
                ['question' => '¿Qué bebidas pueden registrarse?', 'answer' => 'Bebidas tradicionales o ancestrales vinculadas con el Estado de México y elaboradas mediante procesos artesanales.'],
                ['question' => '¿Puedo presentar un enlace de video?', 'answer' => 'Sí. El formulario permitirá cargar un archivo MP4 o registrar una URL HTTPS válida.'],
                ['question' => '¿Los resultados serán públicos?', 'answer' => 'No habrá una lista pública. Cada persona podrá consultar su estado y recibirá comunicación por correo.'],
            ],
        ],
    ];
}
