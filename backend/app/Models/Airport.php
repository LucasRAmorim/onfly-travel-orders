<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Collection;

class Airport extends Model
{
    use HasFactory;

    protected $fillable = [
        'iata_code',
        'icao_code',
        'name',
        'city',
        'country',
    ];

    /**
     * Busca aeroportos por termo, priorizando codigo e correspondencias parciais.
     *
     * @param  string  $term
     * @param  int  $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function searchByTerm(string $term, int $limit = 10): Collection
    {
        $trimmed = trim($term);
        $upper = strtoupper($trimmed);
        $likeAny = '%' . $trimmed . '%';
        $likeUpperStart = $upper . '%';
        $likeTermStart = $trimmed . '%';

        return static::query()
            ->where(function ($query) use ($trimmed, $upper, $likeAny, $likeUpperStart) {
                $query->where('iata_code', $upper)
                    ->orWhere('icao_code', $upper)
                    ->orWhere('iata_code', 'like', $likeUpperStart)
                    ->orWhere('icao_code', 'like', $likeUpperStart)
                    ->orWhere('name', 'like', $likeAny)
                    ->orWhere('city', 'like', $likeAny)
                    ->orWhere('country', 'like', $likeAny);
            })
            ->orderByRaw(
                'CASE '
                    . 'WHEN iata_code = ? THEN 0 '
                    . 'WHEN icao_code = ? THEN 1 '
                    . 'WHEN iata_code LIKE ? THEN 2 '
                    . 'WHEN icao_code LIKE ? THEN 3 '
                    . 'WHEN city LIKE ? THEN 4 '
                    . 'WHEN name LIKE ? THEN 5 '
                    . 'ELSE 6 END',
                [$upper, $upper, $likeUpperStart, $likeUpperStart, $likeTermStart, $likeTermStart]
            )
            ->limit($limit)
            ->get(['id', 'iata_code', 'icao_code', 'name', 'city', 'country']);
    }
}
