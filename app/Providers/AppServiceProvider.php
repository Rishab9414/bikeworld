<?php

namespace App\Providers;

use App\Listeners\CreateCustomerProfile;
use App\Listeners\LogCustomerLogin;
use App\Mail\Transport\BrevoApiTransport;
use App\Models\Announcement;
use App\Models\BikeModel;
use App\Models\Category;
use App\Models\VehicleBrand;
use App\Services\BrevoEmailService;
use App\Services\CartService;
use App\Services\WishlistService;
use Brevo\Brevo;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Brevo::class, function () {
            return new Brevo(
                (string) config('brevo.api_key', ''),
                [
                    'timeout' => (float) config('brevo.timeout', 30),
                    'maxRetries' => (int) config('brevo.max_retries', 3),
                    'client' => new Client(['timeout' => (float) config('brevo.timeout', 30)]),
                ]
            );
        });

        $this->app->singleton(BrevoEmailService::class);
    }

    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Mail::extend('brevo-api', fn () => new BrevoApiTransport(app(BrevoEmailService::class)));

        Blade::directive('money', fn ($expression) => "<?php echo \\App\\Support\\Money::format($expression); ?>");

        View::composer('layouts.shop', function ($view) {
            $user = Auth::user();
            $wishlist = app(WishlistService::class);

            $view->with('cartCount', app(CartService::class)->count());
            $view->with('wishlistCount', $wishlist->countForUser($user));
            $view->with('wishlistProductIds', $wishlist->productIdsForUser($user));
            $view->with('topBarAnnouncements', Announcement::forPosition(Announcement::POSITION_TOP_BAR));

            $rootCategory = Category::where('slug', 'bike-accessories')->first();
            $menuCategories = Category::query()
                ->when($rootCategory, fn ($q) => $q->where('parent_id', $rootCategory->id))
                ->when(! $rootCategory, fn ($q) => $q->whereNotNull('parent_id'))
                ->where('is_active', true)
                ->where('show_in_menu', true)
                ->orderBy('display_order')
                ->orderBy('name')
                ->get();

            $view->with('menuCategories', $menuCategories);
        });

        View::composer(['components.product-card', 'components.wishlist-button'], function ($view) {
            $user = Auth::user();
            $wishlist = app(WishlistService::class);
            $view->with('wishlistProductIds', $wishlist->productIdsForUser($user));
        });

        View::composer('shop.home', function ($view) {
            $view->with('tickerAnnouncements', Announcement::forPosition(Announcement::POSITION_TICKER));
        });

        Event::listen(Login::class, function (Login $event) {
            app(CartService::class)->mergeGuestCart($event->user->id);
        });

        Event::listen(Registered::class, CreateCustomerProfile::class);
        Event::listen(Login::class, LogCustomerLogin::class);

        Route::bind('bikeModel', function (string $value, $route) {
            $vehicleBrand = $route->parameter('vehicleBrand');

            if ($vehicleBrand instanceof VehicleBrand) {
                return $vehicleBrand->bikeModels()->where('slug', $value)->firstOrFail();
            }

            return BikeModel::where('slug', $value)->firstOrFail();
        });
    }
}
