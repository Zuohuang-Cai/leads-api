<?php

declare(strict_types=1);

namespace App\Http\Api\Auth\Controllers;

use App\Domain\User\Events\UserCreated;
use App\Domain\User\Services\EmailVerificationServiceInterface;
use App\Domain\User\User;
use App\Http\Api\Auth\Requests\LoginRequest;
use App\Http\Api\Auth\Requests\RegisterRequest;
use App\Infrastructure\User\Repositories\EloquentUserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group Auth
 *
 * APIs voor authenticatie
 */
final class AuthController extends Controller
{
    public function __construct(
        private readonly EloquentUserRepository $userRepository,
        private readonly EmailVerificationServiceInterface $emailVerificationService,
    ) {}

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

        // Create domain user
        $domainUser = User::create(
            name: $validated['name'],
            email: $validated['email'],
            password: $validated['password'],
        );

        // Persist and get back with ID
        $persistedUser = $this->userRepository->create($domainUser);

        // Dispatch UserCreated event (triggers SendVerificationEmailListener)
        UserCreated::dispatch(
            $persistedUser->id,
            $persistedUser->name->value,
            $persistedUser->email->value,
        );

        // Get Eloquent model for Sanctum token
        $eloquentUser = $this->userRepository->findEloquentById($persistedUser->id);
        $token = $eloquentUser->createToken('auth-token')->plainTextToken;

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
        $domainUser = $this->userRepository->findByEmail($request->email);

        if ($domainUser === null || !$domainUser->verifyPassword($request->password)) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Get Eloquent model for Sanctum token
        $eloquentUser = $this->userRepository->findEloquentByEmail($request->email);
        $token = $eloquentUser->createToken('auth-token')->plainTextToken;

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
        $eloquentUser = $request->user();

        return response()->json([
            'id' => $eloquentUser->id,
            'name' => $eloquentUser->name,
            'email' => $eloquentUser->email,
            'email_verified_at' => $eloquentUser->email_verified_at,
            'created_at' => $eloquentUser->created_at,
            'updated_at' => $eloquentUser->updated_at,
        ]);
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

    /**
     * Verstuur verificatie e-mail
     *
     * Verstuur een nieuwe verificatie e-mail naar de ingelogde gebruiker.
     *
     * @authenticated
     *
     * @response 200 scenario="E-mail verstuurd" {"message": "Verification email sent."}
     * @response 400 scenario="Al geverifieerd" {"message": "Email already verified."}
     * @response 401 scenario="Niet geauthenticeerd" {"message": "Unauthenticated."}
     */
    public function sendVerificationEmail(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->email_verified_at !== null) {
            return response()->json([
                'message' => 'Email already verified.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $this->emailVerificationService->sendVerificationEmail($user->id);

        return response()->json([
            'message' => 'Verification email sent.',
        ]);
    }

    /**
     * Verifieer e-mailadres
     *
     * Verifieer het e-mailadres met de ontvangen token.
     *
     * @unauthenticated
     *
     * @queryParam user_id integer required De gebruiker ID. Example: 1
     * @queryParam token string required De verificatie token. Example: abc123...
     *
     * @response 200 scenario="Succesvol geverifieerd" {"message": "Email verified successfully."}
     * @response 400 scenario="Ongeldige token" {"message": "Invalid or expired token."}
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer',
            'token' => 'required|string',
        ]);

        $verified = $this->emailVerificationService->verify(
            (int) $request->input('user_id'),
            $request->input('token'),
        );

        if (!$verified) {
            return response()->json([
                'message' => 'Invalid or expired token.',
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'message' => 'Email verified successfully.',
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

