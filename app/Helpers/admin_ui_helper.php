<?php

/**
 * Etiquetas y componentes de presentación compartidos por el panel administrativo.
 * Evita duplicar los mapas de estados en cada vista.
 */

if (! function_exists('admin_statuses')) {
    /**
     * Estados de solicitud con su etiqueta en español.
     *
     * @return array<string, string>
     */
    function admin_statuses(): array
    {
        return [
            'borrador'     => 'Borrador',
            'enviada'      => 'Enviada',
            'en_revision'  => 'En revisión',
            'incompleta'   => 'Incompleta',
            'seleccionada' => 'Seleccionada',
            'rechazada'    => 'Rechazada',
            'cancelada'    => 'Cancelada',
        ];
    }
}

if (! function_exists('admin_status_label')) {
    function admin_status_label(?string $status): string
    {
        return admin_statuses()[$status] ?? (string) $status;
    }
}

if (! function_exists('admin_status_tone')) {
    /**
     * Tono visual del estado, usado como sufijo de clase CSS.
     */
    function admin_status_tone(?string $status): string
    {
        return [
            'borrador'     => 'neutral',
            'enviada'      => 'info',
            'en_revision'  => 'review',
            'incompleta'   => 'warning',
            'seleccionada' => 'success',
            'rechazada'    => 'danger',
            'cancelada'    => 'muted',
        ][$status] ?? 'neutral';
    }
}

if (! function_exists('admin_status_badge')) {
    function admin_status_badge(?string $status, bool $large = false): string
    {
        return '<span class="status-badge status-badge-' . admin_status_tone($status) . ($large ? ' status-badge-lg' : '') . '">'
            . esc(admin_status_label($status))
            . '</span>';
    }
}

if (! function_exists('admin_action_label')) {
    /**
     * Traduce los códigos de la bitácora. Los códigos desconocidos se
     * humanizan sin inventar significado.
     */
    function admin_action_label(?string $action): string
    {
        $known = [
            'draft_created'                  => 'Borrador creado',
            'draft_saved'                    => 'Borrador guardado',
            'application_submitted'          => 'Solicitud enviada',
            'admin_status_changed'           => 'Cambio de estado',
            'admin_comment_added'            => 'Comentario agregado',
            'admin_personal_data_updated'    => 'Datos personales actualizados',
            'admin_document_viewed'          => 'Documento consultado',
            'admin_video_viewed'             => 'Video consultado',
            'participant_document_viewed'    => 'Documento consultado por participante',
            'participant_video_viewed'       => 'Video consultado por participante',
            'document_correction_requested'  => 'Corrección solicitada',
            'document_correction_submitted'  => 'Corrección enviada',
        ];

        if (isset($known[$action])) {
            return $known[$action];
        }

        return ucfirst(str_replace('_', ' ', (string) $action));
    }
}

if (! function_exists('admin_actor_label')) {
    function admin_actor_label(?string $actorType): string
    {
        return [
            'admin'       => 'Administración',
            'participant' => 'Participante',
            'system'      => 'Sistema',
        ][$actorType] ?? ucfirst(str_replace('_', ' ', (string) $actorType));
    }
}

if (! function_exists('admin_field_label')) {
    /**
     * Nombre legible para las claves del formulario de categoría.
     */
    function admin_field_label(string $name): string
    {
        return ucfirst(str_replace('_', ' ', $name));
    }
}
