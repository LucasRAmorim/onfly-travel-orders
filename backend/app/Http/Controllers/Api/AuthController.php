<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuthResource;
use App\Http\Resources\MessageResource;
use App\Http\Resources\UserProfileResource;
use App\Repositories\AuthRepository;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/login",
     *     summary="Login",
     *     description="Autentica usuario e retorna token de acesso.",
     *     tags={"Autenticacao"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", example="admin@onfly.local"),
     *             @OA\Property(property="password", type="string", example="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login realizado",
     *         @OA\JsonContent(ref="#/components/schemas/AuthResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Credenciais invalidas",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function login(Request $request, AuthRepository $auth)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $result = $auth->attemptLogin($credentials['email'], $credentials['password']);
        $user = $result['user'];
        $token = $result['token'];

        return new AuthResource([
            'token' => $token,
            'user' => $user,
        ]);
    }

    public function me(Request $request)
    {
        return new UserProfileResource($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return new MessageResource('Logout realizado com sucesso.');
    }
}
