<?php

namespace App\Providers;

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

    public function boot(): void
    {
        \Illuminate\Support\Facades\Auth::provider('customer_accounts', function ($app, array $config) {
            return new class($app['hash'], $config['model']) extends \Illuminate\Auth\EloquentUserProvider {
                public function retrieveByCredentials(#[\SensitiveParameter] array $credentials)
                {
                    $credentials = array_filter(
                        $credentials,
                        fn ($key) => ! str_contains($key, 'password'),
                        ARRAY_FILTER_USE_KEY
                    );

                    if (empty($credentials)) {
                        return null;
                    }

                    if (isset($credentials['email'])) {
                        $email = $credentials['email'];
                        return \App\Models\CustomerAccount::whereHas('customer', function ($query) use ($email) {
                            $query->where('email', $email);
                        })->first();
                    }

                    return parent::retrieveByCredentials($credentials);
                }
            };
        });
    }
}
