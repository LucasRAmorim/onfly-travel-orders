<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TravelOrderListResource;
use App\Http\Resources\TravelOrderResource;
use App\Http\Requests\IndexTravelOrderRequest;
use App\Http\Requests\StoreTravelOrderRequest;
use App\Http\Requests\UpdateTravelOrderStatusRequest;
use App\Models\TravelOrder;
use App\Repositories\TravelOrderRepository;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;



class TravelOrderController extends Controller
{
    /**
     * @OA\Get(
     *     path="/travel-orders",
     *     summary="Listar pedidos",
     *     description="Lista pedidos do usuario (ou todos para admin) com filtros opcionais.",
     *     tags={"Pedidos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Status do pedido",
     *         @OA\Schema(type="string", enum={"requested","approved","canceled"})
     *     ),
     *     @OA\Parameter(
     *         name="destination",
     *         in="query",
     *         description="Destino (contém)",
     *         @OA\Schema(type="string", example="Sao")
     *     ),
     *     @OA\Parameter(
     *         name="travel_from",
     *         in="query",
     *         description="Data inicial da viagem (YYYY-MM-DD)",
     *         @OA\Schema(type="string", example="2026-03-01")
     *     ),
     *     @OA\Parameter(
     *         name="travel_to",
     *         in="query",
     *         description="Data final da viagem (YYYY-MM-DD)",
     *         @OA\Schema(type="string", example="2026-03-31")
     *     ),
     *     @OA\Parameter(
     *         name="created_from",
     *         in="query",
     *         description="Data inicial de criacao (YYYY-MM-DD)",
     *         @OA\Schema(type="string", example="2026-02-01")
     *     ),
     *     @OA\Parameter(
     *         name="created_to",
     *         in="query",
     *         description="Data final de criacao (YYYY-MM-DD)",
     *         @OA\Schema(type="string", example="2026-02-28")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Pagina",
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de pedidos",
     *         @OA\JsonContent(ref="#/components/schemas/TravelOrdersListResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Nao autorizado",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Parametros invalidos",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function index(IndexTravelOrderRequest $request, TravelOrderRepository $repository)
    {
        $user = $request->user();
        $filters = $request->validated();
        $result = $repository->listFor($user, $filters);
        $paginator = $result['paginator'];
        $counts = $result['counts'];

        return (new TravelOrderListResource($paginator))->additional([
            'meta' => [
                'status_counts' => [
                    'requested' => (int) ($counts['requested'] ?? 0),
                    'approved' => (int) ($counts['approved'] ?? 0),
                    'canceled' => (int) ($counts['canceled'] ?? 0),
                ],
            ],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/travel-orders",
     *     summary="Criar pedido",
     *     description="Cria um novo pedido de viagem.",
     *     tags={"Pedidos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/TravelOrderForm")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Pedido criado",
     *         @OA\JsonContent(ref="#/components/schemas/TravelOrderResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Nao autorizado",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Dados invalidos",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function store(StoreTravelOrderRequest $request, TravelOrderRepository $repository)
    {
        $user = $request->user();
        $order = $repository->createForUser($user, $request->validated());

        return (new TravelOrderResource($order))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * @OA\Get(
     *     path="/travel-orders/{id}",
     *     summary="Detalhar pedido",
     *     description="Recupera um pedido por id (apenas se autorizado).",
     *     tags={"Pedidos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do pedido",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pedido encontrado",
     *         @OA\JsonContent(ref="#/components/schemas/TravelOrderResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Nao autorizado",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Acesso negado",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Nao encontrado",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function show(Request $request, TravelOrder $travelOrder)
    {
        $this->authorize('view', $travelOrder);

        return new TravelOrderResource($travelOrder);
    }

    /**
     * @OA\Patch(
     *     path="/travel-orders/{id}/status",
     *     summary="Atualizar status do pedido",
     *     description="Aprova ou cancela um pedido (apenas admin).",
     *     tags={"Pedidos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do pedido",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"approved","canceled"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Status atualizado",
     *         @OA\JsonContent(ref="#/components/schemas/TravelOrderResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Nao autorizado",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Acesso negado",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Nao encontrado",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Dados invalidos",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function updateStatus(
        UpdateTravelOrderStatusRequest $request,
        TravelOrder $travelOrder,
        TravelOrderRepository $repository
    ) {
        $this->authorize('updateStatus', $travelOrder);

        $order = $repository->updateStatus($travelOrder, $request->validated()['status']);

        return new TravelOrderResource($order);
    }
}
