<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\User\Events\UserCreated;
use App\Domain\User\Listeners\SendVerificationEmailListener;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(
            UserCreated::class,
            SendVerificationEmailListener::class,
        );
    }
}
