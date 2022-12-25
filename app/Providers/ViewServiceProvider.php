<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        View::composer('company/*', function ($view) {
            $this->setLoginUser($view);
        });
        View::composer('recruit/*', function ($view) {
            $this->setLoginUser($view);
        });
        View::composer('outsource/*', function ($view) {
            $this->setLoginUser($view);
        });
        View::composer('admin/*', function ($view) {
            $this->setLoginUser($view);
        });
    }

    private function setLoginUser($view) {

        if(auth()->check()) {

            $view->with([
                'user' => auth()->user()
            ]);

        }

    }
}
