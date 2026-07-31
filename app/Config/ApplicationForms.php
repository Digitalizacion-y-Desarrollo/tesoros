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
            'documents_notice' => 'Los documentos de Restaurantes están pendientes de definición institucional. No se solicitarán archivos hasta contar con las bases aprobadas.',
            'documents' => [],
            'notice' => 'Formulario provisional. Los requisitos oficiales de Restaurantes siguen pendientes de aprobación institucional.',
            'fields' => [
                ['name' => 'business_name', 'label' => 'Nombre comercial', 'type' => 'text', 'required' => true, 'max' => 180],
                ['name' => 'legal_name', 'label' => 'Razón social', 'type' => 'text', 'required' => true, 'max' => 200],
                ['name' => 'municipality', 'label' => 'Municipio del establecimiento', 'type' => 'text', 'required' => true, 'max' => 120],
                ['name' => 'phone', 'label' => 'Teléfono de contacto', 'type' => 'tel', 'required' => true, 'max' => 20],
                ['name' => 'address', 'label' => 'Domicilio del establecimiento', 'type' => 'textarea', 'required' => true, 'max' => 500],
                ['name' => 'restaurant_history', 'label' => 'Historia y trayectoria', 'type' => 'textarea', 'required' => true, 'max' => 3000],
                ['name' => 'culinary_concept', 'label' => 'Concepto culinario', 'type' => 'textarea', 'required' => true, 'max' => 3000],
                ['name' => 'proposal_name', 'label' => 'Nombre de la propuesta gastronómica', 'type' => 'text', 'required' => true, 'max' => 180],
                ['name' => 'proposal_description', 'label' => 'Descripción y vínculo con el patrimonio mexiquense', 'type' => 'textarea', 'required' => true, 'max' => 4000],
                ['name' => 'video_url', 'label' => 'Video de evidencia (opcional)', 'type' => 'video', 'required' => false, 'max' => 2048],
            ],
        ],
        'joven-talento-gastronomia' => [
            'documents' => [
                ['type' => 'member_1_official_id', 'label' => 'Identificación oficial vigente de la persona responsable', 'required' => true, 'accept' => 'pdf,image'],
                ['type' => 'member_2_official_id', 'label' => 'Identificación oficial vigente de la segunda persona integrante', 'required' => true, 'accept' => 'pdf,image'],
                ['type' => 'member_1_passport', 'label' => 'Pasaporte de la persona responsable', 'required' => false, 'accept' => 'pdf,image'],
                ['type' => 'member_2_passport', 'label' => 'Pasaporte de la segunda persona integrante', 'required' => false, 'accept' => 'pdf,image'],
                ['type' => 'institution_letter', 'label' => 'Carta oficial de la institución educativa', 'required' => true, 'accept' => 'pdf'],
                ['type' => 'motivation_letter', 'label' => 'Carta de motivos del equipo (máximo una cuartilla)', 'required' => true, 'accept' => 'pdf'],
                ['type' => 'technical_sheet', 'label' => 'Ficha técnica de la propuesta de quiché', 'required' => true, 'accept' => 'pdf'],
                ['type' => 'signed_registration_form', 'label' => 'Ficha de inscripción requisitada y firmada', 'required' => true, 'accept' => 'pdf'],
            ],
            'fields' => [
                ['name' => 'institution_name', 'label' => 'Institución educativa', 'type' => 'text', 'required' => true, 'max' => 220],
                ['name' => 'campus', 'label' => 'Plantel o campus', 'type' => 'text', 'required' => true, 'max' => 180],
                ['name' => 'municipality', 'label' => 'Municipio de la institución', 'type' => 'text', 'required' => true, 'max' => 120],
                ['name' => 'phone', 'label' => 'Teléfono de contacto del responsable', 'type' => 'tel', 'required' => true, 'max' => 20],
                ['name' => 'proposal_name', 'label' => 'Nombre de la propuesta de quiché', 'type' => 'text', 'required' => true, 'max' => 180],
                ['name' => 'ingredients', 'label' => 'Ingredientes y cantidades', 'type' => 'textarea', 'required' => true, 'max' => 3000],
                ['name' => 'preparation', 'label' => 'Procedimiento', 'type' => 'textarea', 'required' => true, 'max' => 5000],
                ['name' => 'proposal_justification', 'label' => 'Justificación e identidad mexiquense', 'type' => 'textarea', 'required' => true, 'max' => 3000],
                ['name' => 'motivation', 'label' => 'Carta de motivos del equipo', 'type' => 'textarea', 'required' => true, 'max' => 4000],
                ['name' => 'video_url', 'label' => 'Video de la propuesta (opcional)', 'type' => 'video', 'required' => false, 'max' => 2048],
            ],
        ],
        'bebidas-tradicionales-ancestrales' => [
            'documents_notice' => 'La evidencia específica para acreditar producción continua y el documento fiscal definitivo siguen pendientes de confirmación institucional.',
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
