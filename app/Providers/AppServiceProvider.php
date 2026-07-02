<?php

namespace App\Providers;

use App\Listeners\CreateCustomerProfile;
use App\Listeners\LogCustomerLogin;
use App\Models\Announcement;
use App\Services\CartService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Blade::directive('money', fn ($expression) => "<?php echo \\App\\Support\\Money::format($expression); ?>");

        View::composer('layouts.shop', function ($view) {
            $view->with('cartCount', app(CartService::class)->count());
            $view->with('topBarAnnouncements', Announcement::forPosition(Announcement::POSITION_TOP_BAR));
        });

        View::composer('shop.home', function ($view) {
            $view->with('tickerAnnouncements', Announcement::forPosition(Announcement::POSITION_TICKER));
        });

        Event::listen(Login::class, function (Login $event) {
            app(CartService::class)->mergeGuestCart($event->user->id);
        });

        Event::listen(Registered::class, CreateCustomerProfile::class);
        Event::listen(Login::class, LogCustomerLogin::class);
    }
}
