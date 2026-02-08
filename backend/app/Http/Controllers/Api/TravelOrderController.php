<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexTravelOrderRequest;
use App\Http\Requests\StoreTravelOrderRequest;
use App\Actions\UpdateTravelOrderStatusAction;
use App\Http\Requests\UpdateTravelOrderStatusRequest;
use App\Notifications\TravelOrderStatusChanged;
use App\Models\TravelOrder;
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
    public function index(IndexTravelOrderRequest $request)
    {
        $user = $request->user();
        $filters = $request->validated();

        $query = TravelOrder::query();

        if (! $user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        $query->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status));
        $query->when($filters['destination'] ?? null, fn ($q, $dest) => $q->where('destination', 'like', "%{$dest}%"));

        $travelFrom = $filters['travel_from'] ?? null;
        $travelTo = $filters['travel_to'] ?? null;
        if ($travelFrom || $travelTo) {
            $travelFrom = $travelFrom ?: '1900-01-01';
            $travelTo = $travelTo ?: '2999-12-31';

            $query->where(function ($q) use ($travelFrom, $travelTo) {
                $q->whereBetween('departure_date', [$travelFrom, $travelTo])
                  ->orWhereBetween('return_date', [$travelFrom, $travelTo])
                  ->orWhere(function ($q2) use ($travelFrom, $travelTo) {
                      $q2->where('departure_date', '<=', $travelFrom)
                         ->where('return_date', '>=', $travelTo);
                  });
            });
        }

        $createdFrom = $filters['created_from'] ?? null;
        $createdTo = $filters['created_to'] ?? null;
        if ($createdFrom) $query->whereDate('created_at', '>=', $createdFrom);
        if ($createdTo) $query->whereDate('created_at', '<=', $createdTo);

        $baseQuery = clone $query;

        $counts = $baseQuery
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $paginator = $query->orderByDesc('id')->paginate(10);

        return response()->json([
            'data' => $paginator,
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
    public function store(StoreTravelOrderRequest $request)
    {
        $user = $request->user();

        // dd($request->all());
        $order = TravelOrder::create([
            'user_id' => $user->id,
            'requester_name' => $request->validated()['requester_name'],
            'destination' => $request->validated()['destination'],
            'departure_date' => $request->validated()['departure_date'],
            'return_date' => $request->validated()['return_date'],
            'status' => 'requested',
        ]);

        return response()->json([
            'data' => $order,
        ], 201);
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

        return response()->json([
            'data' => $travelOrder,
        ]);
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
        UpdateTravelOrderStatusAction $action
    ) {
        $this->authorize('updateStatus', $travelOrder);

        $order = $action->execute(
            $travelOrder,
            $request->validated()['status']
        );

        $order->user->notify(new TravelOrderStatusChanged($order));

        return response()->json([
            'data' => $order,
        ]);
    }
}
