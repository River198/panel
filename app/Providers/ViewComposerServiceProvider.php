<?php

namespace river\Providers;

use Illuminate\Support\ServiceProvider;
use river\Http\ViewComposers\AssetComposer;

class ViewComposerServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     */
    public function boot()
    {
        $this->app->make('view')->composer('*', AssetComposer::class);
    }
}
