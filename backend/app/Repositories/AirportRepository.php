<?php

namespace App\Repositories;

use App\Models\Airport;
use Illuminate\Database\Eloquent\Collection;

class AirportRepository
{
    /**
     * Busca aeroportos por termo.
     *
     * @param  string  $term
     * @param  int  $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function search(string $term, int $limit = 10): Collection
    {
        return Airport::searchByTerm($term, $limit);
    }
}
