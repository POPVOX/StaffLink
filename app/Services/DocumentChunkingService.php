<?php namespace App\Services;

namespace App\Services;

class DocumentChunkingService
{
    public function splitText(string $text, int $chunkSize = 300, int $overlap = 50): array
    {
        $chunks = [];
        $words = explode(' ', $text);
        $start = 0;

        while ($start < count($words)) {
            $chunk = array_slice($words, $start, $chunkSize);
            $chunks[] = implode(' ', $chunk);
            $start += ($chunkSize - $overlap);
        }

        return $chunks;
    }
}
