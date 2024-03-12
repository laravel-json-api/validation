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

use Illuminate\Contracts\Validation\Factory as ValidatorFactory;
use LaravelJsonApi\Contracts\Server\Server;
use LaravelJsonApi\Contracts\Validation\Container as ContainerContract;
use LaravelJsonApi\Core\Values\ResourceType;
use LaravelJsonApi\Validation\Validators\Factory;

class Container implements ContainerContract
{
    /**
     * Container constructor
     *
     * @param Server $server
     * @param ValidatorFactory $validatorFactory
     */
    public function __construct(
        private readonly Server $server,
        private readonly ValidatorFactory $validatorFactory,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function validatorsFor(ResourceType|string $resourceType): Factory
    {
        return new Factory(
            $this->server,
            $this->server->schemas()->schemaFor($resourceType),
            $this->validatorFactory,
        );
    }
}
