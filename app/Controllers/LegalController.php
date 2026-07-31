<?php

namespace App\Controllers;

use CodeIgniter\Exceptions\PageNotFoundException;
use Config\Database;

final class LegalController extends PublicController
{
    private const TYPES = [
        'aviso-privacidad' => 'privacy_notice',
        'terminos-condiciones' => 'terms',
        'conservacion-informacion' => 'retention_policy',
        'consentimiento-imagen' => 'image_consent',
    ];

    public function show(string $slug): string
    {
        $type = self::TYPES[$slug] ?? null;
        if ($type === null) {
            throw PageNotFoundException::forPageNotFound();
        }
        $document = Database::connect()->table('legal_documents')
            ->where('document_type', $type)
            ->where('is_active', 1)
            ->orderBy('effective_at', 'DESC')
            ->get()->getRowArray();
        if ($document === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('public/legal', [
            'title' => $document['title'],
            'document' => $document,
        ]);
    }
}
