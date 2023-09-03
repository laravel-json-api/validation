<?php
/*
 * Copyright 2023 Cloud Creativity Limited
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
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