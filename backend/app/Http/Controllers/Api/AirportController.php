<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AirportResource;
use App\Http\Requests\SearchAirportRequest;
use App\Repositories\AirportRepository;
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
    public function index(SearchAirportRequest $request, AirportRepository $repository)
    {
        $filters = $request->validated();
        $term = trim($filters['q']);
        $limit = $filters['limit'] ?? 10;
        $airports = $repository->search($term, $limit);

        return AirportResource::collection($airports);
    }
}
