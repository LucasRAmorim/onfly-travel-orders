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



class TravelOrderController extends Controller
{
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

    public function show(Request $request, TravelOrder $travelOrder)
    {
        $this->authorize('view', $travelOrder);

        return response()->json([
            'data' => $travelOrder,
        ]);
    }

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
