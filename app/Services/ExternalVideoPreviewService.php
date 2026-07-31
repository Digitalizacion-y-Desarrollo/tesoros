<?php

namespace App\Services;

final class ExternalVideoPreviewService
{
    /**
     * @return array{kind:string, provider:string, url:string, embed_url:?string, host:string}
     */
    public function describe(?string $url): array
    {
        $url = trim((string) $url);
        $parts = parse_url($url);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));

        if ($scheme !== 'https' || $host === '') {
            return [
                'kind' => 'unavailable',
                'provider' => 'Enlace no disponible',
                'url' => '',
                'embed_url' => null,
                'host' => '',
            ];
        }

        $youtubeId = $this->youtubeId($host, $parts);
        if ($youtubeId !== null) {
            return [
                'kind' => 'embed',
                'provider' => 'YouTube',
                'url' => $url,
                'embed_url' => 'https://www.youtube-nocookie.com/embed/' . rawurlencode($youtubeId),
                'host' => $host,
            ];
        }

        $vimeoId = $this->vimeoId($host, (string) ($parts['path'] ?? ''));
        if ($vimeoId !== null) {
            return [
                'kind' => 'embed',
                'provider' => 'Vimeo',
                'url' => $url,
                'embed_url' => 'https://player.vimeo.com/video/' . $vimeoId,
                'host' => $host,
            ];
        }

        return [
            'kind' => 'external',
            'provider' => 'Video externo',
            'url' => $url,
            'embed_url' => null,
            'host' => $host,
        ];
    }

    /** @param array<string, mixed> $parts */
    private function youtubeId(string $host, array $parts): ?string
    {
        $host = preg_replace('/^www\./', '', $host) ?? $host;
        $path = trim((string) ($parts['path'] ?? ''), '/');
        $candidate = null;

        if ($host === 'youtu.be') {
            $candidate = explode('/', $path)[0] ?? null;
        } elseif (in_array($host, ['youtube.com', 'm.youtube.com'], true)) {
            if ($path === 'watch') {
                parse_str((string) ($parts['query'] ?? ''), $query);
                $candidate = $query['v'] ?? null;
            } elseif (preg_match('~^(?:shorts|embed)/([^/]+)~', $path, $matches) === 1) {
                $candidate = $matches[1];
            }
        }

        return is_string($candidate) && preg_match('/^[A-Za-z0-9_-]{6,15}$/', $candidate) === 1
            ? $candidate
            : null;
    }

    private function vimeoId(string $host, string $path): ?string
    {
        $host = preg_replace('/^www\./', '', $host) ?? $host;
        if (! in_array($host, ['vimeo.com', 'player.vimeo.com'], true)) {
            return null;
        }

        return preg_match('~(?:^|/)(?:video/)?([0-9]+)(?:$|/)~', trim($path, '/'), $matches) === 1
            ? $matches[1]
            : null;
    }
}
