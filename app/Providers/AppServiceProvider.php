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
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->ensureStorageDirectoriesExist();

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

    /**
     * Hostinger / FTP uploads often omit empty storage folders.
     * Create them early so Artisan (storage:link, etc.) does not crash.
     */
    private function ensureStorageDirectoriesExist(): void
    {
        foreach ([
            storage_path('app/public'),
            storage_path('framework/cache/data'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            storage_path('logs'),
            base_path('bootstrap/cache'),
        ] as $path) {
            if (! is_dir($path)) {
                @mkdir($path, 0775, true);
            }
        }
    }

    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Password::defaults(function () {
            return Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers();
        });

        Mail::extend('brevo-api', fn () => new BrevoApiTransport(app(BrevoEmailService::class)));

        Blade::directive('money', fn ($expression) => "<?php echo \\App\\Support\\Money::format($expression); ?>");

        View::composer('layouts.shop', function ($view) {
            $user = Auth::user();
            $wishlist = app(WishlistService::class);

            $view->with('cartCount', app(CartService::class)->count());
            $view->with('wishlistCount', $wishlist->countForUser($user));
            $view->with('wishlistProductIds', $wishlist->productIdsForUser($user));
            $view->with('topBarAnnouncements', Announcement::forPosition(Announcement::POSITION_TOP_BAR));

            $menuCategories = Category::query()
                ->where('is_active', true)
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
