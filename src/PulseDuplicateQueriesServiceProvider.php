<?php

namespace DazzaDev\PulseDuplicateQueries;

use DazzaDev\PulseDuplicateQueries\Livewire\DuplicateQueries;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Livewire\LivewireManager;

class PulseDuplicateQueriesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'duplicate-queries');

        $this->callAfterResolving('livewire', function (LivewireManager $livewire, Application $app) {
            $livewire->component('duplicate-queries', DuplicateQueries::class);
        });
    }
}
