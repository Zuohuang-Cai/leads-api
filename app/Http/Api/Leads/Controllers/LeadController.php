<?php

declare(strict_types=1);

namespace App\Http\Api\Leads\Controllers;

use App\Application\Lead\Actions\CreateLeadAction;
use App\Application\Lead\Actions\DeleteLeadAction;
use App\Application\Lead\Actions\GetLeadsAction;
use App\Application\Lead\Actions\UpdateLeadAction;
use App\Application\Lead\DTOs\CreateLeadDTO;
use App\Application\Lead\DTOs\UpdateLeadDTO;
use App\Domain\Lead\Repositories\LeadRepositoryInterface;
use App\Http\Api\Leads\Requests\IndexLeadRequest;
use App\Http\Api\Leads\Requests\StoreLeadRequest;
use App\Http\Api\Leads\Requests\UpdateLeadRequest;
use App\Http\Api\Leads\Resources\LeadCollection;
use App\Http\Api\Leads\Resources\LeadResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group Leads
 *
 * APIs voor het beheren van leads
 */
final class LeadController extends Controller
{
    public function __construct(
        private LeadRepositoryInterface $repository,
        private GetLeadsAction          $getLeadsAction,
        private CreateLeadAction        $createLeadAction,
        private UpdateLeadAction        $updateLeadAction,
        private DeleteLeadAction        $deleteLeadAction,
    )
    {
    }

    /**
     * Alle leads ophalen
     *
     * Retourneert een gepagineerde lijst van leads. Ondersteunt zoeken, filteren op status en sorteren.
     *
     * @authenticated
     *
     * @queryParam search string Zoek op naam of e-mail. Example: jan
     * @queryParam status string Filter op status. Example: nieuw
     * @queryParam sort string Sorteerrichting (asc of desc). Example: desc
     * @queryParam per_page integer Aantal resultaten per pagina (1-100). Example: 15
     *
     * @response 200 scenario="Succesvolle operatie" {"data": [{"id": 1, "name": "Jan de Vries", "email": "jan@example.com", "source": "website", "status": "nieuw", "created_at": "2026-02-26T10:00:00.000000Z", "updated_at": "2026-02-26T10:00:00.000000Z"}], "links": {"first": "http://localhost/api/leads?page=1", "last": "http://localhost/api/leads?page=5", "prev": null, "next": "http://localhost/api/leads?page=2"}, "meta": {"current_page": 1, "last_page": 5, "per_page": 15, "total": 73}}
     * @response 401 scenario="Niet geauthenticeerd" {"message": "Unauthenticated."}
     */
    public function index(IndexLeadRequest $request): LeadCollection
    {
        $leads = $this->getLeadsAction->execute($request->validated());

        return new LeadCollection($leads);
    }

    /**
     * Nieuwe lead aanmaken
     *
     * Maak een nieuwe lead aan met de opgegeven gegevens.
     *
     * @authenticated
     *
     * @bodyParam name string required De naam van de lead. Minimaal 2 karakters. Example: Jan de Vries
     * @bodyParam email string required Het e-mailadres van de lead. Example: jan@example.com
     * @bodyParam source string required De bron van de lead. Mogelijke waarden: website, email, telefoon, whatsapp, showroom, overig. Example: website
     * @bodyParam status string required De status van de lead. Mogelijke waarden: nieuw, opgepakt, proefrit, offerte, verkocht, afgevallen. Example: nieuw
     *
     * @response 201 scenario="Lead succesvol aangemaakt" {"data": {"id": 1, "name": "Jan de Vries", "email": "jan@example.com", "source": "website", "status": "nieuw", "created_at": "2026-02-26T10:00:00.000000Z", "updated_at": "2026-02-26T10:00:00.000000Z"}}
     * @response 401 scenario="Niet geauthenticeerd" {"message": "Unauthenticated."}
     * @response 422 scenario="Validatiefout" {"message": "The given data was invalid.", "errors": {"email": ["The email has already been taken."]}}
     */
    public function store(StoreLeadRequest $request): JsonResponse
    {
        $dto = CreateLeadDTO::fromArray($request->validated());

        $lead = $this->createLeadAction->execute($dto);

        return (new LeadResource($lead))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Lead ophalen op ID
     *
     * Retourneert de gegevens van een specifieke lead.
     *
     * @authenticated
     *
     * @urlParam id integer required Het ID van de lead. Example: 1
     *
     * @response 200 scenario="Succesvolle operatie" {"data": {"id": 1, "name": "Jan de Vries", "email": "jan@example.com", "source": "website", "status": "nieuw", "created_at": "2026-02-26T10:00:00.000000Z", "updated_at": "2026-02-26T10:00:00.000000Z"}}
     * @response 401 scenario="Niet geauthenticeerd" {"message": "Unauthenticated."}
     * @response 404 scenario="Lead niet gevonden" {"message": "Lead not found."}
     */
    public function show(int $lead): LeadResource
    {
        $leadModel = $this->repository->findById($lead);

        return new LeadResource($leadModel);
    }

    /**
     * Lead bijwerken
     *
     * Werk een bestaande lead bij. Alle velden zijn optioneel (partial update).
     *
     * @authenticated
     *
     * @urlParam id integer required Het ID van de lead. Example: 1
     *
     * @bodyParam name string De naam van de lead. Minimaal 2 karakters. Example: Piet Jansen
     * @bodyParam email string Het e-mailadres van de lead. Example: piet@example.com
     * @bodyParam source string De bron van de lead. Mogelijke waarden: website, email, telefoon, whatsapp, showroom, overig. Example: email
     * @bodyParam status string De status van de lead. Mogelijke waarden: nieuw, opgepakt, proefrit, offerte, verkocht, afgevallen. Example: opgepakt
     *
     * @response 200 scenario="Lead succesvol bijgewerkt" {"data": {"id": 1, "name": "Piet Jansen", "email": "piet@example.com", "source": "email", "status": "opgepakt", "created_at": "2026-02-26T10:00:00.000000Z", "updated_at": "2026-02-26T10:00:00.000000Z"}}
     * @response 401 scenario="Niet geauthenticeerd" {"message": "Unauthenticated."}
     * @response 404 scenario="Lead niet gevonden" {"message": "Lead not found."}
     * @response 422 scenario="Validatiefout" {"message": "The given data was invalid.", "errors": {"email": ["The email has already been taken."]}}
     */
    public function update(UpdateLeadRequest $request, int $lead): LeadResource
    {
        $dto = UpdateLeadDTO::fromArray($request->validated());

        $updatedLead = $this->updateLeadAction->execute($lead, $dto);

        return new LeadResource($updatedLead);
    }

    /**
     * Lead verwijderen
     *
     * Verwijder een lead op basis van ID.
     *
     * @authenticated
     *
     * @urlParam id integer required Het ID van de lead. Example: 1
     *
     * @response 204 scenario="Lead succesvol verwijderd"
     * @response 401 scenario="Niet geauthenticeerd" {"message": "Unauthenticated."}
     * @response 404 scenario="Lead niet gevonden" {"message": "Lead not found."}
     */
    public function destroy(int $lead): JsonResponse
    {
        $this->deleteLeadAction->execute($lead);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}

