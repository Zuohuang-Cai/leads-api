# i-Motive Leads API

## 🚀 Quick Start

```bash
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
