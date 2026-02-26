# i-Motive Leads API

## 🚀 Quick Start

```bash
# Clone the repository
git clone git@github.com:Zuohuang-Cai/leads-api.git
# naar de juiste map
cd leads-api
# Install dependencies
composer install

# One-click deployment
php artisan app:deploy
# With database seeding (test data)
php artisan app:deploy --seed
# Fresh install (drop all tables)
php artisan app:deploy --fresh --seed

# start the server
php artisan serve
```

## 📖 API Documentation

After deployment, access the API documentation at:

```
http://localhost:8000/docs
```

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test types
php artisan test --filter=UserTest           # Unit tests
php artisan test --filter=ApiSmokeTest       # Smoke tests
php artisan test --filter=AuthIntegrationTest # Integration tests
php artisan test --filter=ApiStressTest      # Stress tests
```

# Architecturale Verantwoording & Reflectie

### 1. Waarom Domain-Driven Design (DDD)?
Dit project is geïmplementeerd volgens de principes van Domain-Driven Design (DDD). Hoewel dit voor een feature van deze omvang wellicht niet de meest pragmatische keuze is, heb ik bewust voor deze architectuur gekozen om mijn expertise op het gebied van softwarearchitectuur en Object-Oriented Programming (OOP) tastbaar te maken.

Tijdens de evaluatie van gangbare architecturen — zoals Microservices, Clean Architecture en Modular Monoliths — heb ik een weloverwogen afweging gemaakt:
- Microservices: Zouden leiden tot onnodige overhead en complexiteit (bloat), zeker gezien het feit dat database-opsplitsing binnen de gestelde tijd niet haalbaar was.

- Monolith (Standaard MVC): Hoewel efficiënt, bood een standaard controller-service-repository patroon naar mijn mening onvoldoende ruimte om mijn technische diepgang en abstractievermogen te demonstreren.

- Clean Architecture: Dit was in theorie de meest passende keuze, maar ik heb besloten de grenzen te verleggen naar DDD om een robuustere scheiding van logica te tonen.

### 2. Afwegingen en Uitdagingen van DDD
   Ik ben mij terdege bewust van de trade-offs die DDD met zich meebrengt. De abstracte concepten verhogen de instapdrempel voor nieuwe ontwikkelaars en de initiële ontwikkelsnelheid ligt lager door de "zware" aard van de boilerplate en de architecturale lagen.
### 3. DDD in de context van Laravel
Binnen een Laravel-ecosysteem worden deze uitdagingen nog prominenter. Laravel is fundamenteel ontworpen voor Rapid Application Development (RAD), waarbij de standaard Artisan-templates en de MVC-structuur leidend zijn.

### 4. Implementatie Details & Pseudo-code
Tijdens ons vorige gesprek heb je me een vraag gesteld over de event update en notify naar frontend ui. Om mijn visie hierop tastbaar te maken, heb ik in dit project een abstracte implementatie (pseudo-code) toegevoegd.

Locatie: De implementatie is te vinden in app/Http/Api/Auth/Controllers/AuthController.php op regel 58.

```php
UserCreated::dispatch(
            $persistedUser->id,
            $persistedUser->name->value,
            $persistedUser->email->value,
        );
```
Het gebruiksscenario hier is het verzenden van e-mails; afhankelijk van de implementatie kan dit natuurlijk ook worden vervangen door WebSockets of Server-Sent Events.

### technische verbeter punt
- Er wordt weinig gebruikgemaakt van libraries en functies die al door Laravel zijn aangeboden.
- In ```AuthController.php``` zie ik dat verschillende methoden direct de repository aanroepen. Het is netter om eerst de application layer aan te roepen en pas daarna de repository.
- in EloquentUserRepository.php zie ik een private methode toDomain(UserEloquentModel $model): User. De repository zou niet verantwoordelijk moeten zijn voor het converteren van een model naar een domain object. Dit hoort eigenlijk in de application layer.
```php
 private function toDomain(UserEloquentModel $model): User
    {
        return User::fromPersistence(
            id: $model->id,
            name: $model->name,
            email: $model->email,
            hashedPassword: $model->password,
            emailVerifiedAt: $model->email_verified_at
                ? new DateTimeImmutable($model->email_verified_at->toDateTimeString())
                : null,
            createdAt: new DateTimeImmutable($model->created_at->toDateTimeString()),
            updatedAt: new DateTimeImmutable($model->updated_at->toDateTimeString()),
        );
    }
```
### Conclusie
Ik heb deze opdracht niet benaderd als een standaard dagelijkse taak, maar als een technische assessment om mijn vermogen als Software Engineer te bewijzen. De keuze voor deze architectuur, inclusief een zekere mate van bewuste over-engineering, dient om aan te tonen dat ik complexe concepten niet alleen theoretisch beheers, maar ook in de praktijk kan implementeren.
