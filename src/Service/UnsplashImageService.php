<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class UnsplashImageService
{
    private const UNSPLASH_API_URL = 'https://api.unsplash.com/search/photos';

    public function __construct(
        private HttpClientInterface $httpClient,
        private string $unsplashAccessKey = ''
    ) {
    }

    /**
     * Search for an image on Unsplash
     * 
     * @param string $entityType Type of entity (monster, spell, item, etc.)
     * @param string $entityName Name of the entity
     * @return string|null URL of the first image found, or null
     */
    public function searchImage(string $entityType, string $entityName): ?string
    {
        if (empty($this->unsplashAccessKey)) {
            return null;
        }

        try {
            $query = $this->buildSearchQuery($entityType, $entityName);

            $response = $this->httpClient->request('GET', self::UNSPLASH_API_URL, [
                'query' => [
                    'query' => $query,
                    'per_page' => 1,
                    'orientation' => 'landscape',
                ],
                'headers' => [
                    'Authorization' => 'Client-ID ' . $this->unsplashAccessKey,
                ],
            ]);

            $data = $response->toArray();

            if (isset($data['results'][0]['urls']['regular'])) {
                return $data['results'][0]['urls']['regular'];
            }

            return null;
        } catch (\Exception $e) {
            // Log error but don't break the page
            return null;
        }
    }

    /**
     * Build search query based on entity type
     */
    private function buildSearchQuery(string $entityType, string $entityName): string
    {
        $typeMap = [
            'monster' => 'DND RPG fantasy monster creature',
            'spell' => 'DND RPG magic spell fantasy',
            'magic_item' => 'DND RPG magic item artifact fantasy',
            'equipment' => 'DND RPG equipment weapon armor fantasy',
            'class' => 'DND RPG class character fantasy',
            'feat' => 'DND RPG ability power fantasy',
            'background' => 'DND RPG background story fantasy',
        ];

        $prefix = $typeMap[$entityType] ?? 'DND RPG fantasy';

        return $prefix . ' ' . $entityName;
    }

    /**
     * Get placeholder image if Unsplash fails
     */
    public function getPlaceholderImage(string $entityType): string
    {
        $placeholders = [
            'monster' => 'https://via.placeholder.com/800x400/1e293b/f1f5f9?text=Monster',
            'spell' => 'https://via.placeholder.com/800x400/4c1d95/f3e8ff?text=Spell',
            'magic_item' => 'https://via.placeholder.com/800x400/7c2d12/fef3c7?text=Magic+Item',
            'equipment' => 'https://via.placeholder.com/800x400/374151/f3f4f6?text=Equipment',
            'class' => 'https://via.placeholder.com/800x400/1e40af/dbeafe?text=Class',
        ];

        return $placeholders[$entityType] ?? 'https://via.placeholder.com/800x400/64748b/f1f5f9?text=D%26D';
    }
}
