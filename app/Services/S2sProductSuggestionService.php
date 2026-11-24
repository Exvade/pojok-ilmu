<?php

namespace App\Services;

use App\Models\S2sProduct;
use App\Models\Training;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class S2sProductSuggestionService
{
    /** Kata yang tidak membedakan produk sehingga diabaikan saat mencocokkan nama. */
    private const IGNORED_WORDS = ['ASSY', 'SUB', 'SET', 'TYPE', 'TRAINING', 'TRN', 'LEVEL', 'LV', 'L1', 'L2', 'L3', 'DAN', 'AND', 'THE'];

    /** Kemiripan minimal (0-1) agar produk disarankan. */
    private const MINIMUM_SCORE = 0.5;

    /**
     * Produk S2S aktif yang namanya mirip dengan judul training, urut dari yang paling mirip.
     *
     * @param  Collection<int, S2sProduct>  $products
     * @return Collection<int, S2sProduct>
     */
    public function suggest(Training $training, Collection $products, int $limit = 8): Collection
    {
        $trainingWords = $this->words($training->judul);

        if ($trainingWords === []) {
            return collect();
        }

        return $products
            ->map(function (S2sProduct $product) use ($trainingWords): array {
                $productWords = $this->words((string) $product->nama_part);
                $commonCount = count(array_intersect($trainingWords, $productWords));
                $unionCount = count(array_unique([...$trainingWords, ...$productWords]));

                return ['product' => $product, 'score' => $unionCount > 0 ? $commonCount / $unionCount : 0];
            })
            ->filter(fn (array $match): bool => $match['score'] >= self::MINIMUM_SCORE)
            ->sort(fn (array $first, array $second): int => [$second['score'], $first['product']->kode_part] <=> [$first['score'], $second['product']->kode_part])
            ->take($limit)
            ->pluck('product')
            ->values();
    }

    /** @return array<int, string> */
    private function words(string $text): array
    {
        return collect(preg_split('/[^A-Z0-9]+/', Str::upper($text), -1, PREG_SPLIT_NO_EMPTY))
            ->reject(fn (string $word): bool => in_array($word, self::IGNORED_WORDS, true) || ctype_digit($word))
            ->unique()
            ->values()
            ->all();
    }
}
