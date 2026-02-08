<?php

namespace App\OpenApi;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *   schema="User",
 *   type="object",
 *   @OA\Property(property="id", type="integer", example=1),
 *   @OA\Property(property="name", type="string", example="Lucas"),
 *   @OA\Property(property="email", type="string", example="admin@onfly.local"),
 *   @OA\Property(property="role", type="string", example="admin")
 * )
 *
 * @OA\Schema(
 *   schema="AuthResponse",
 *   type="object",
 *   @OA\Property(property="token", type="string", example="token..."),
 *   @OA\Property(property="user", ref="#/components/schemas/User")
 * )
 *
 * @OA\Schema(
 *   schema="TravelOrder",
 *   type="object",
 *   @OA\Property(property="id", type="integer", example=1),
 *   @OA\Property(property="requester_name", type="string", example="Lucas"),
 *   @OA\Property(property="destination", type="string", example="Sao Paulo"),
 *   @OA\Property(property="departure_date", type="string", example="2026-03-01"),
 *   @OA\Property(property="return_date", type="string", example="2026-03-05"),
 *   @OA\Property(property="status", type="string", example="requested"),
 *   @OA\Property(property="user_id", type="integer", example=1),
 *   @OA\Property(property="created_at", type="string", example="2026-02-08T10:00:00Z"),
 *   @OA\Property(property="updated_at", type="string", example="2026-02-08T10:00:00Z")
 * )
 *
 * @OA\Schema(
 *   schema="TravelOrderForm",
 *   type="object",
 *   required={"requester_name","destination","departure_date","return_date"},
 *   @OA\Property(property="requester_name", type="string", example="Lucas"),
 *   @OA\Property(property="destination", type="string", example="Sao Paulo"),
 *   @OA\Property(property="departure_date", type="string", example="2026-03-01"),
 *   @OA\Property(property="return_date", type="string", example="2026-03-05")
 * )
 *
 * @OA\Schema(
 *   schema="PaginatedTravelOrders",
 *   type="object",
 *   @OA\Property(
 *     property="data",
 *     type="array",
 *     @OA\Items(ref="#/components/schemas/TravelOrder")
 *   ),
 *   @OA\Property(property="total", type="integer", example=15),
 *   @OA\Property(property="current_page", type="integer", example=1),
 *   @OA\Property(property="per_page", type="integer", example=10)
 * )
 *
 * @OA\Schema(
 *   schema="TravelOrderStatusCounts",
 *   type="object",
 *   @OA\Property(property="requested", type="integer", example=10),
 *   @OA\Property(property="approved", type="integer", example=3),
 *   @OA\Property(property="canceled", type="integer", example=2)
 * )
 *
 * @OA\Schema(
 *   schema="TravelOrdersListResponse",
 *   type="object",
 *   @OA\Property(property="data", ref="#/components/schemas/PaginatedTravelOrders"),
 *   @OA\Property(
 *     property="meta",
 *     type="object",
 *     @OA\Property(property="status_counts", ref="#/components/schemas/TravelOrderStatusCounts")
 *   )
 * )
 *
 * @OA\Schema(
 *   schema="TravelOrderResponse",
 *   type="object",
 *   @OA\Property(property="data", ref="#/components/schemas/TravelOrder")
 * )
 *
 * @OA\Schema(
 *   schema="Airport",
 *   type="object",
 *   @OA\Property(property="id", type="string", example="1"),
 *   @OA\Property(property="iata_code", type="string", example="GRU"),
 *   @OA\Property(property="icao_code", type="string", example="SBGR"),
 *   @OA\Property(property="name", type="string", example="Guarulhos"),
 *   @OA\Property(property="city", type="string", example="Sao Paulo"),
 *   @OA\Property(property="country", type="string", example="Brasil")
 * )
 *
 * @OA\Schema(
 *   schema="AirportsResponse",
 *   type="object",
 *   @OA\Property(
 *     property="data",
 *     type="array",
 *     @OA\Items(ref="#/components/schemas/Airport")
 *   )
 * )
 *
 * @OA\Schema(
 *   schema="NotificationData",
 *   type="object",
 *   @OA\Property(property="travel_order_id", type="integer", example=1),
 *   @OA\Property(property="status", type="string", example="approved"),
 *   @OA\Property(property="destination", type="string", example="Sao Paulo"),
 *   @OA\Property(property="departure_date", type="string", example="2026-03-01"),
 *   @OA\Property(property="return_date", type="string", example="2026-03-05")
 * )
 *
 * @OA\Schema(
 *   schema="NotificationItem",
 *   type="object",
 *   @OA\Property(property="id", type="string", example="uuid"),
 *   @OA\Property(property="created_at", type="string", example="2026-02-08T10:00:00Z"),
 *   @OA\Property(property="read_at", type="string", example="2026-02-08T10:05:00Z"),
 *   @OA\Property(property="data", ref="#/components/schemas/NotificationData")
 * )
 *
 * @OA\Schema(
 *   schema="NotificationsResponse",
 *   type="object",
 *   @OA\Property(
 *     property="data",
 *     type="array",
 *     @OA\Items(ref="#/components/schemas/NotificationItem")
 *   ),
 *   @OA\Property(
 *     property="meta",
 *     type="object",
 *     @OA\Property(property="unread_count", type="integer", example=2)
 *   )
 * )
 *
 * @OA\Schema(
 *   schema="NotificationReadResponse",
 *   type="object",
 *   @OA\Property(
 *     property="data",
 *     type="object",
 *     @OA\Property(property="id", type="string", example="uuid"),
 *     @OA\Property(property="read_at", type="string", example="2026-02-08T10:05:00Z")
 *   )
 * )
 *
 * @OA\Schema(
 *   schema="ErrorResponse",
 *   type="object",
 *   @OA\Property(property="message", type="string", example="Nao autorizado")
 * )
 */
class Schemas
{
}
