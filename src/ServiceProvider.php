<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Validation;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use LaravelJsonApi\Contracts\Validation as Contracts;

final class ServiceProvider extends BaseServiceProvider
{
    /**
     * Boot application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->loadTranslationsFrom(
            __DIR__ . '/../lang',
            JsonApiValidation::$translationNamespace
        );

        $this->publishes([
            __DIR__ . '/../lang' => $this->app->langPath() . '/vendor/' . JsonApiValidation::$translationNamespace,
        ]);
    }

    /**
     * Bind package services into the service container.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(Translator::class);
        $this->app->bind(Contracts\ResourceErrorFactory::class, ResourceErrorFactory::class);
        $this->app->bind(Contracts\QueryErrorFactory::class, QueryErrorFactory::class);
        $this->app->bind(Contracts\DeletionErrorFactory::class, DeletionErrorFactory::class);
        $this->app->bind(Contracts\Container::class, Container::class);
    }
}
