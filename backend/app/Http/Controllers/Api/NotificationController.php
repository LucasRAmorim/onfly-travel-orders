<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MessageResource;
use App\Http\Resources\NotificationReadResource;
use App\Http\Resources\NotificationResource;
use App\Repositories\NotificationRepository;
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
     *     @OA\Parameter(
     *         name="only_unread",
     *         in="query",
     *         description="Quando true, retorna apenas notificacoes nao lidas",
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Limite de resultados (0 para retornar todas)",
     *         @OA\Schema(type="integer", example=10)
     *     ),
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
    public function index(Request $request, NotificationRepository $repository)
    {
        $user = $request->user();
        $onlyUnread = filter_var($request->query('only_unread', false), FILTER_VALIDATE_BOOLEAN);
        $limit = (int) $request->query('limit', 10);
        $limit = max(0, $limit);
        $result = $repository->listFor($user, $limit, $onlyUnread);

        return NotificationResource::collection($result['notifications'])
            ->additional([
                'meta' => [
                    'unread_count' => $result['unreadCount'],
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
    public function markAsRead(Request $request, string $notificationId, NotificationRepository $repository)
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return (new MessageResource('Admins nao possuem notificacoes.'))
                ->response()
                ->setStatusCode(403);
        }

        $notification = $repository->markAsRead($user, $notificationId);

        return new NotificationReadResource($notification);
    }
}
