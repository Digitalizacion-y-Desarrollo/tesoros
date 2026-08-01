<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class ApplicationForms extends BaseConfig
{
    public string $privacyVersion = 'PROVISIONAL-DEV-2026-07';
    public string $declarationsVersion = 'PROVISIONAL-DEV-2026-07';

    /**
     * @var array<string, array{notice?: string, fields: list<array<string, mixed>>}>
     */
    public array $categories = [
        'cocineras-cocineros-tradicionales' => [
            'documents' => [
                ['type' => 'official_id', 'label' => 'Identificación oficial vigente (INE)', 'required' => true, 'accept' => 'pdf,image'],
                ['type' => 'proof_of_address', 'label' => 'Comprobante de domicilio', 'required' => true, 'accept' => 'pdf,image'],
                ['type' => 'participant_photo', 'label' => 'Fotografía reciente de la persona participante', 'required' => true, 'accept' => 'image'],
                ['type' => 'motivation_letter', 'label' => 'Carta de motivos (máximo una cuartilla)', 'required' => true, 'accept' => 'pdf'],
                ['type' => 'dish_photo', 'label' => 'Fotografía del platillo', 'required' => false, 'accept' => 'image'],
            ],
            'fields' => [
                ['name' => 'municipality', 'label' => 'Municipio de residencia', 'type' => 'text', 'required' => true, 'max' => 120],
                ['name' => 'phone', 'label' => 'Teléfono de contacto', 'type' => 'tel', 'required' => true, 'max' => 20],
                ['name' => 'address', 'label' => 'Domicilio', 'type' => 'textarea', 'required' => true, 'max' => 500],
                ['name' => 'years_experience', 'label' => 'Años de experiencia', 'type' => 'number', 'required' => true, 'min' => 0, 'maxNumber' => 100],
                ['name' => 'signature_dish', 'label' => 'Nombre de la receta o platillo insignia', 'type' => 'text', 'required' => true, 'max' => 180],
                ['name' => 'dish_origin', 'label' => 'Origen familiar o comunitario', 'type' => 'textarea', 'required' => true, 'max' => 2000],
                ['name' => 'ingredients', 'label' => 'Ingredientes principales', 'type' => 'textarea', 'required' => true, 'max' => 3000],
                ['name' => 'preparation', 'label' => 'Proceso de preparación', 'type' => 'textarea', 'required' => true, 'max' => 5000],
                ['name' => 'cultural_context', 'label' => 'Contexto cultural y vínculo con su comunidad', 'type' => 'textarea', 'required' => true, 'max' => 3000],
                ['name' => 'motivation', 'label' => 'Carta de motivos', 'type' => 'textarea', 'required' => true, 'max' => 4000],
                ['name' => 'video_url', 'label' => 'Video de la participación (opcional)', 'type' => 'video', 'required' => false, 'max' => 2048],
            ],
        ],
        'restaurantes' => [
            'video_required' => true,
            'video_help' => 'Presenta un video institucional del restaurante y de sus platillos insignia mediante archivo MP4 o enlace HTTPS.',
            'documents_notice' => 'La carta de intención debe tener máximo dos cuartillas, elaborarse en hoja membretada, incluir datos de contacto oficiales y estar firmada por el propietario, representante legal o chef ejecutivo.',
            'documents' => [
                ['type' => 'operating_license', 'label' => 'Licencia de funcionamiento', 'required' => true, 'accept' => 'pdf,image'],
                ['type' => 'operating_compliance', 'label' => 'Permisos vigentes y documentación que acredite el cumplimiento de obligaciones', 'required' => true, 'accept' => 'pdf,image'],
                ['type' => 'operation_age_evidence', 'label' => 'Evidencia de operación mínima de cinco años', 'required' => true, 'accept' => 'pdf,image'],
                ['type' => 'executive_chef_cv', 'label' => 'Currículum del chef ejecutivo', 'required' => true, 'accept' => 'pdf'],
                ['type' => 'chef_or_brigade_passport', 'label' => 'Pasaporte vigente del chef ejecutivo o de la brigada representante', 'required' => true, 'accept' => 'pdf,image'],
                ['type' => 'restaurant_photo_1', 'label' => 'Fotografía profesional principal del restaurante', 'required' => true, 'accept' => 'image'],
                ['type' => 'restaurant_photo_2', 'label' => 'Fotografía profesional adicional del restaurante', 'required' => false, 'accept' => 'image'],
                ['type' => 'signature_dish_photo_1', 'label' => 'Fotografía principal de los platillos insignia', 'required' => true, 'accept' => 'image'],
                ['type' => 'signature_dish_photo_2', 'label' => 'Fotografía adicional de los platillos insignia', 'required' => false, 'accept' => 'image'],
                ['type' => 'intent_letter', 'label' => 'Carta de intención firmada', 'required' => true, 'accept' => 'pdf'],
            ],
            'fields' => [
                ['name' => 'business_name', 'label' => 'Nombre comercial', 'type' => 'text', 'required' => true, 'max' => 180],
                ['name' => 'legal_name', 'label' => 'Razón social', 'type' => 'text', 'required' => true, 'max' => 200],
                ['name' => 'foundation_year', 'label' => 'Año de fundación', 'type' => 'number', 'required' => true, 'min' => 1900, 'maxNumber' => 2021],
                ['name' => 'branch_count', 'label' => 'Número de sucursales', 'type' => 'number', 'required' => true, 'min' => 1, 'maxNumber' => 999],
                ['name' => 'municipality', 'label' => 'Municipio del establecimiento', 'type' => 'text', 'required' => true, 'max' => 120],
                ['name' => 'address', 'label' => 'Domicilio del establecimiento', 'type' => 'textarea', 'required' => true, 'max' => 500],
                ['name' => 'phone', 'label' => 'Teléfono del restaurante', 'type' => 'tel', 'required' => true, 'max' => 20],
                ['name' => 'restaurant_email', 'label' => 'Correo electrónico del restaurante', 'type' => 'email', 'required' => true, 'max' => 254],
                ['name' => 'social_media', 'label' => 'Redes sociales', 'type' => 'textarea', 'required' => false, 'max' => 1000],
                ['name' => 'restaurant_profile', 'label' => 'Semblanza del restaurante', 'type' => 'textarea', 'required' => true, 'max' => 5000],
                ['name' => 'chef_name', 'label' => 'Nombre completo del chef ejecutivo', 'type' => 'text', 'required' => true, 'max' => 250],
                ['name' => 'chef_nationality', 'label' => 'Nacionalidad del chef ejecutivo', 'type' => 'text', 'required' => true, 'max' => 100],
                ['name' => 'chef_passport_number', 'label' => 'Número de pasaporte', 'type' => 'text', 'required' => true, 'max' => 30],
                ['name' => 'chef_phone', 'label' => 'Teléfono del chef ejecutivo', 'type' => 'tel', 'required' => true, 'max' => 20],
                ['name' => 'chef_email', 'label' => 'Correo electrónico del chef ejecutivo', 'type' => 'email', 'required' => true, 'max' => 254],
                ['name' => 'restaurant_specialty', 'label' => 'Especialidad del restaurante', 'type' => 'textarea', 'required' => true, 'max' => 2000],
                ['name' => 'traditional_dishes', 'label' => 'Platillos tradicionales', 'type' => 'textarea', 'required' => true, 'max' => 3000],
                ['name' => 'author_dishes', 'label' => 'Platillos de autor', 'type' => 'textarea', 'required' => true, 'max' => 3000],
                ['name' => 'signature_dishes', 'label' => 'Platillos insignia', 'type' => 'textarea', 'required' => true, 'max' => 3000],
                ['name' => 'culinary_concept', 'label' => 'Propuesta gastronómica e identidad culinaria mexiquense', 'type' => 'textarea', 'required' => true, 'max' => 5000],
                ['name' => 'international_promotion_strategy', 'label' => 'Estrategias de promoción antes, durante y después del festival', 'type' => 'textarea', 'required' => true, 'max' => 5000],
                ['name' => 'expected_impact', 'label' => 'Impacto turístico, cultural y comercial esperado', 'type' => 'textarea', 'required' => true, 'max' => 4000],
                ['name' => 'video_url', 'label' => 'Video institucional del restaurante y sus platillos insignia', 'type' => 'video', 'required' => false, 'max' => 2048],
            ],
        ],
        'joven-talento-gastronomia' => [
            'video_required' => true,
            'video_max_minutes' => 3,
            'video_help' => 'El video debe durar máximo tres minutos y mostrar a la alumna o alumno presentando y elaborando la quiché con la que competirá.',
            'documents' => [
                ['type' => 'official_id', 'label' => 'Identificación oficial vigente de la persona participante', 'required' => true, 'accept' => 'pdf,image'],
                ['type' => 'passport', 'label' => 'Pasaporte de la persona participante', 'required' => false, 'accept' => 'pdf,image'],
                ['type' => 'institution_letter', 'label' => 'Carta oficial de la institución educativa', 'required' => true, 'accept' => 'pdf'],
                ['type' => 'motivation_letter', 'label' => 'Carta de motivos de la persona participante (máximo una cuartilla)', 'required' => true, 'accept' => 'pdf'],
                ['type' => 'technical_sheet', 'label' => 'Ficha técnica de la propuesta de quiché', 'required' => true, 'accept' => 'pdf'],
                ['type' => 'signed_registration_form', 'label' => 'Ficha de inscripción requisitada y firmada', 'required' => true, 'accept' => 'pdf'],
            ],
            'fields' => [
                ['name' => 'institution_name', 'label' => 'Institución educativa', 'type' => 'text', 'required' => true, 'max' => 220],
                ['name' => 'campus', 'label' => 'Plantel o campus', 'type' => 'text', 'required' => true, 'max' => 180],
                ['name' => 'municipality', 'label' => 'Municipio de la institución', 'type' => 'text', 'required' => true, 'max' => 120],
                ['name' => 'phone', 'label' => 'Teléfono de contacto de la persona participante', 'type' => 'tel', 'required' => true, 'max' => 20],
                ['name' => 'proposal_name', 'label' => 'Nombre de la propuesta de quiché', 'type' => 'text', 'required' => true, 'max' => 180],
                ['name' => 'ingredients', 'label' => 'Ingredientes y cantidades', 'type' => 'textarea', 'required' => true, 'max' => 3000],
                ['name' => 'preparation', 'label' => 'Procedimiento', 'type' => 'textarea', 'required' => true, 'max' => 5000],
                ['name' => 'proposal_justification', 'label' => 'Justificación e identidad mexiquense', 'type' => 'textarea', 'required' => true, 'max' => 3000],
                ['name' => 'motivation', 'label' => 'Motivos de la persona participante', 'type' => 'textarea', 'required' => true, 'max' => 4000],
                ['name' => 'video_duration_seconds', 'label' => 'Duración del video en segundos (máximo 180)', 'type' => 'number', 'required' => true, 'min' => 1, 'maxNumber' => 180],
                ['name' => 'video_url', 'label' => 'Video de presentación y elaboración de la quiché', 'type' => 'video', 'required' => false, 'max' => 2048],
            ],
        ],
        'bebidas-tradicionales-ancestrales' => [
            'documents' => [
                ['type' => 'official_id', 'label' => 'Identificación oficial vigente', 'required' => true, 'accept' => 'pdf,image'],
                ['type' => 'proof_of_address', 'label' => 'Comprobante de domicilio', 'required' => true, 'accept' => 'pdf,image'],
                ['type' => 'producer_photo', 'label' => 'Fotografía de la persona productora', 'required' => true, 'accept' => 'image'],
                ['type' => 'beverage_photo', 'label' => 'Fotografía de la bebida', 'required' => true, 'accept' => 'image'],
                ['type' => 'process_photo', 'label' => 'Fotografía del proceso o ingredientes', 'required' => false, 'accept' => 'image'],
                ['type' => 'production_evidence', 'label' => 'Evidencia de producción continua por al menos tres años (tipo por confirmar)', 'required' => false, 'accept' => 'pdf,image'],
                ['type' => 'fiscal_document', 'label' => 'Documento fiscal del SAT (documento específico por confirmar)', 'required' => false, 'accept' => 'pdf'],
                ['type' => 'rfc_document', 'label' => 'Constancia o documento de RFC', 'required' => true, 'accept' => 'pdf'],
            ],
            'fields' => [
                ['name' => 'municipality', 'label' => 'Municipio de residencia o producción', 'type' => 'text', 'required' => true, 'max' => 120],
                ['name' => 'phone', 'label' => 'Teléfono de contacto', 'type' => 'tel', 'required' => true, 'max' => 20],
                ['name' => 'address', 'label' => 'Domicilio', 'type' => 'textarea', 'required' => true, 'max' => 500],
                ['name' => 'project_name', 'label' => 'Nombre del proyecto, marca o unidad productiva', 'type' => 'text', 'required' => true, 'max' => 180],
                ['name' => 'beverage_name', 'label' => 'Nombre de la bebida', 'type' => 'text', 'required' => true, 'max' => 180],
                ['name' => 'beverage_type', 'label' => 'Tipo de bebida', 'type' => 'text', 'required' => true, 'max' => 120],
                ['name' => 'years_experience', 'label' => 'Años de experiencia', 'type' => 'number', 'required' => true, 'min' => 0, 'maxNumber' => 100],
                ['name' => 'production_process', 'label' => 'Proceso artesanal de elaboración', 'type' => 'textarea', 'required' => true, 'max' => 5000],
                ['name' => 'history', 'label' => 'Historia y origen de la bebida', 'type' => 'textarea', 'required' => true, 'max' => 3000],
                ['name' => 'community_link', 'label' => 'Vínculo con la comunidad o territorio', 'type' => 'textarea', 'required' => true, 'max' => 3000],
                ['name' => 'motivation', 'label' => 'Motivos para participar', 'type' => 'textarea', 'required' => true, 'max' => 4000],
                ['name' => 'video_url', 'label' => 'Video de la participación (opcional)', 'type' => 'video', 'required' => false, 'max' => 2048],
            ],
        ],
    ];
}
