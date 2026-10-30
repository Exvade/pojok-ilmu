<?php

namespace App\Support;

class TrustedMediaUrl
{
    /**
     * Menghasilkan URL embed hanya dari Google Drive atau YouTube yang tepercaya.
     */
    public static function embedUrl(?string $url, ?string $mediaType): ?string
    {
        if (! in_array($mediaType, ['video_gdrive', 'video_youtube'], true) || blank($url)) {
            return null;
        }

        $parts = parse_url(trim($url));
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));

        if ($scheme !== 'https') {
            return null;
        }

        return match ($mediaType) {
            'video_gdrive' => self::googleDriveEmbedUrl($url, $host),
            'video_youtube' => self::youTubeEmbedUrl($url, $host),
            default => null,
        };
    }

    public static function isTrustedEmbedUrl(?string $url, ?string $mediaType): bool
    {
        return self::embedUrl($url, $mediaType) !== null;
    }

    private static function googleDriveEmbedUrl(string $url, string $host): ?string
    {
        if (! in_array($host, ['drive.google.com', 'docs.google.com'], true)) {
            return null;
        }

        if (! preg_match('/(?:file\/d\/|[?&]id=)([A-Za-z0-9_-]+)/', $url, $matches)) {
            return null;
        }

        return 'https://drive.google.com/file/d/' . $matches[1] . '/preview';
    }

    private static function youTubeEmbedUrl(string $url, string $host): ?string
    {
        if (! in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtu.be', 'www.youtu.be', 'www.youtube-nocookie.com'], true)) {
            return null;
        }

        if (! preg_match('/(?:[?&]v=|\/embed\/|youtu\.be\/)([A-Za-z0-9_-]{6,})/', $url, $matches)) {
            return null;
        }

        return 'https://www.youtube.com/embed/' . $matches[1];
    }
}
