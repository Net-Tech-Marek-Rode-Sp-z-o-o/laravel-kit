<?php

declare(strict_types=1);

namespace NetCode\Kit;

use Illuminate\Support\ServiceProvider;

abstract class ContextServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerBindings();
    }

    public function boot(): void
    {
        $this->bootContext();
    }

    protected function registerBindings(): void {}

    protected function bootContext(): void {}
}
