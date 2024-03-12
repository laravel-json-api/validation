<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Validation\Tests\Integration;

use Illuminate\Contracts\Validation\Factory as ValidatorFactory;
use Illuminate\Foundation\Testing\Concerns\InteractsWithDeprecationHandling;
use LaravelJsonApi\Validation\Factory;
use LaravelJsonApi\Validation\ServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    use InteractsWithDeprecationHandling;

    /**
     * @var Factory
     */
    protected Factory $factory;

    /**
     * @var ValidatorFactory
     */
    protected ValidatorFactory $validatorFactory;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutDeprecationHandling();

        $this->factory = $this->app->make(Factory::class);
        $this->validatorFactory = $this->app->make(ValidatorFactory::class);
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class,
        ];
    }
}
