<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class NotificationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/notifications",
     *     summary="Listar notificacoes",
     *     description="Retorna as ultimas notificacoes do usuario.",
     *     tags={"Notificacoes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de notificacoes",
     *         @OA\JsonContent(ref="#/components/schemas/NotificationsResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Nao autorizado",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return response()->json([
                'data' => [],
                'meta' => [
                    'unread_count' => 0,
                ],
            ]);
        }

        $notifications = $user->notifications()
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'read_at' => $notification->read_at?->toISOString(),
                    'created_at' => $notification->created_at?->toISOString(),
                    'data' => $notification->data,
                ];
            });

        return response()->json([
            'data' => $notifications,
            'meta' => [
                'unread_count' => $user->unreadNotifications()->count(),
            ],
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/notifications/{notificationId}/read",
     *     summary="Marcar notificacao como lida",
     *     tags={"Notificacoes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="notificationId",
     *         in="path",
     *         required=true,
     *         description="ID da notificacao",
     *         @OA\Schema(type="string", example="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notificacao marcada como lida",
     *         @OA\JsonContent(ref="#/components/schemas/NotificationReadResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Nao autorizado",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Admin nao possui notificacoes",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Nao encontrado",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function markAsRead(Request $request, string $notificationId)
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return response()->json([
                'message' => 'Admins nao possuem notificacoes.',
            ], 403);
        }

        $notification = $user->notifications()->findOrFail($notificationId);
        $notification->markAsRead();

        return response()->json([
            'data' => [
                'id' => $notification->id,
                'read_at' => $notification->read_at?->toISOString(),
            ],
        ]);
    }
}
