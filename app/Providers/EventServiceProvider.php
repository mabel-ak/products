<?php

namespace App\Providers;

use App\Events\EmailVerificationEvent;
use App\Events\PasswordResetEvent;
use App\Events\SignUpEvent;
use App\Listeners\EmailVerificationListener;
use App\Listeners\SignUpListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Listeners\LogVerifiedUser;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
                Verified::class,
                    LogVerifiedUser::class,
        ],
        SignUpEvent::class=>[
            SignUpListener::class
        ],
        EmailVerificationEvent::class=>[
            EmailVerificationListener::class
        ],
        PasswordResetEvent::class=>[
            PasswordResetListener::class
        ],

    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
