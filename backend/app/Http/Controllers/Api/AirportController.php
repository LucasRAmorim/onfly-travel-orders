<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchAirportRequest;
use App\Models\Airport;
use OpenApi\Annotations as OA;

class AirportController extends Controller
{
    /**
     * @OA\Get(
     *     path="/airports",
     *     summary="Buscar aeroportos",
     *     description="Retorna aeroportos com base em termo de busca.",
     *     tags={"Aeroportos"},
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="Termo de busca (min 2 caracteres)",
     *         required=true,
     *         @OA\Schema(type="string", example="gru")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Limite de resultados",
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de aeroportos",
     *         @OA\JsonContent(ref="#/components/schemas/AirportsResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Parametros invalidos",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function index(SearchAirportRequest $request)
    {
        $filters = $request->validated();
        $term = trim($filters['q']);
        $limit = $filters['limit'] ?? 10;

        $upper = strtoupper($term);
        $likeAny = '%' . $term . '%';
        $likeUpperStart = $upper . '%';
        $likeTermStart = $term . '%';

        $airports = Airport::query()
            ->where(function ($query) use ($term, $upper, $likeAny, $likeUpperStart) {
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

        return response()->json([
            'data' => $airports,
        ]);
    }
}
