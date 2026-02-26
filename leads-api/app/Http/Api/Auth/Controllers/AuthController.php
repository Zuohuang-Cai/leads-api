<?php

declare(strict_types=1);

namespace App\Http\Api\Auth\Controllers;

use App\Http\Api\Auth\Requests\LoginRequest;
use App\Http\Api\Auth\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group Auth
 *
 * APIs voor authenticatie
 */
final class AuthController extends Controller
{
    /**
     * Registreer een nieuwe gebruiker
     *
     * Maakt een nieuw account aan en retourneert een API-token.
     *
     * @unauthenticated
     *
     * @bodyParam name string required De naam van de gebruiker. Minimaal 2 karakters. Example: Jan de Vries
     * @bodyParam email string required Het e-mailadres. Example: jan@example.com
     * @bodyParam password string required Het wachtwoord. Minimaal 8 karakters. Example: wachtwoord123
     * @bodyParam password_confirmation string required Bevestiging van het wachtwoord. Example: wachtwoord123
     *
     * @response 201 scenario="Gebruiker succesvol geregistreerd" {"access_token": "1|abc123def456...", "token_type": "bearer"}
     * @response 422 scenario="Validatiefout" {"message": "The given data was invalid.", "errors": {"email": ["The email has already been taken."]}}
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => $validated['password'],
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return $this->respondWithToken($token, Response::HTTP_CREATED);
    }

    /**
     * Inloggen
     *
     * Authenticeer met e-mail en wachtwoord, retourneert een API-token.
     *
     * @unauthenticated
     *
     * @bodyParam email string required Het e-mailadres. Example: jan@example.com
     * @bodyParam password string required Het wachtwoord. Example: wachtwoord123
     *
     * @response 200 scenario="Succesvol ingelogd" {"access_token": "1|abc123def456...", "token_type": "bearer"}
     * @response 401 scenario="Ongeldige inloggegevens" {"message": "Invalid credentials."}
     * @response 422 scenario="Validatiefout" {"message": "The given data was invalid.", "errors": {"email": ["The email field is required."]}}
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return $this->respondWithToken($token);
    }

    /**
     * Huidige gebruiker ophalen
     *
     * Retourneert de gegevens van de ingelogde gebruiker.
     *
     * @authenticated
     *
     * @response 200 scenario="Succesvolle operatie" {"id": 1, "name": "Jan de Vries", "email": "jan@example.com", "email_verified_at": null, "created_at": "2026-02-26T10:00:00.000000Z", "updated_at": "2026-02-26T10:00:00.000000Z"}
     * @response 401 scenario="Niet geauthenticeerd" {"message": "Unauthenticated."}
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    /**
     * Uitloggen
     *
     * Invalideer het huidige API-token.
     *
     * @authenticated
     *
     * @response 200 scenario="Succesvol uitgelogd" {"message": "Successfully logged out."}
     * @response 401 scenario="Niet geauthenticeerd" {"message": "Unauthenticated."}
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out.',
        ]);
    }

    private function respondWithToken(string $token, int $status = Response::HTTP_OK): JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
        ], $status);
    }
}

