<?php

namespace App\OpenApi;

use OpenApi\Annotations as OA;

/**
 * @OA\OpenApi(
 *   @OA\Info(
 *     title="OnFly API",
 *     version="1.0.0",
 *     description="API para gestao de pedidos de viagem corporativa."
 *   ),
 *   @OA\Server(
 *     url="http://localhost/api",
 *     description="Servidor local"
 *   ),
 *   @OA\Tag(
 *     name="Autenticacao",
 *     description="Login e sessao"
 *   ),
 *   @OA\Tag(
 *     name="Pedidos",
 *     description="Pedidos de viagem"
 *   ),
 *   @OA\Tag(
 *     name="Aeroportos",
 *     description="Busca de aeroportos"
 *   ),
 *   @OA\Tag(
 *     name="Notificacoes",
 *     description="Notificacoes do usuario"
 *   )
 * )
 *
 * @OA\SecurityScheme(
 *   securityScheme="bearerAuth",
 *   type="http",
 *   scheme="bearer",
 *   bearerFormat="Token"
 * )
 */
class OpenApiSpec
{
}
