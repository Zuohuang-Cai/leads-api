# i-Motive Leads API

## 🚀 Quick Start (One-Click Deploy)

```bash
# Install dependencies
composer install

# One-click deployment
php artisan app:deploy

# With database seeding (test data)
php artisan app:deploy --seed

# Fresh install (drop all tables)
php artisan app:deploy --fresh --seed
```

## 📋 Manual Setup

### Environment Variables
Copy `.env.example` to `.env` and configure:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=leads
DB_USERNAME=your_mysql_username
DB_PASSWORD=your_mysql_password
```

### Manual Commands
```bash
# Generate app key
php artisan key:generate

# Run migrations
php artisan migrate

# Seed database (optional)
php artisan db:seed

# Generate API docs
php artisan scribe:generate
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

## 📁 Project Structure

```
app/
├── Domain/           # Business logic (Entities, Value Objects, Repositories)
├── Application/      # Use cases (Actions, DTOs)
├── Infrastructure/   # External services (Eloquent, Email)
└── Http/Api/         # API layer (Controllers, Requests, Resources)
```
