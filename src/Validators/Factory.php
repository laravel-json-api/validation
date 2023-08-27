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

namespace LaravelJsonApi\Validation\Validators;

use LaravelJsonApi\Contracts\Schema\Schema;
use LaravelJsonApi\Contracts\Server\Server;
use LaravelJsonApi\Contracts\Validation\Factory as FactoryContract;
use LaravelJsonApi\Contracts\Validation\QueryManyValidator;
use LaravelJsonApi\Contracts\Validation\QueryOneValidator;
use Illuminate\Contracts\Validation\Factory as ValidatorFactory;
use LaravelJsonApi\Validation\Extractors\CreationExtractor;
use LaravelJsonApi\Validation\Extractors\DeleteExtractor;
use LaravelJsonApi\Validation\Extractors\RelationshipExtractor;
use LaravelJsonApi\Validation\Extractors\UpdateExtractor;
use LaravelJsonApi\Validation\ValidatedSchema;

class Factory implements FactoryContract
{
    /**
     * Factory constructor
     *
     * @param Server $server
     * @param Schema $schema
     * @param ValidatorFactory $validatorFactory
     */
    public function __construct(
        private readonly Server $server,
        private readonly Schema $schema,
        private readonly ValidatorFactory $validatorFactory,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function queryMany(): QueryManyValidator
    {
        // TODO: Implement queryMany() method.
    }

    /**
     * @inheritDoc
     */
    public function queryOne(): QueryOneValidator
    {
        // TODO: Implement queryOne() method.
    }

    /**
     * @inheritDoc
     */
    public function store(): StoreValidator
    {
        return new StoreValidator(
            $this->validatorFactory,
            new ValidatedSchema($this->schema),
            new CreationExtractor(),
        );
    }

    /**
     * @inheritDoc
     */
    public function update(): UpdateValidator
    {
        return new UpdateValidator(
            $this->validatorFactory,
            new ValidatedSchema($this->schema),
            new UpdateExtractor($this->schema, $this->server->encoder()),
        );
    }

    /**
     * @inheritDoc
     */
    public function destroy(): ?DestroyValidator
    {
        if (method_exists($this->schema, 'deleteRules')) {
            return new DestroyValidator(
                $this->validatorFactory,
                new ValidatedSchema($this->schema),
                new DeleteExtractor(
                    $this->schema,
                    new UpdateExtractor($this->schema, $this->server->encoder()),
                ),
            );
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function relation(): RelationshipValidator
    {
        return new RelationshipValidator(
            $this->validatorFactory,
            new ValidatedSchema($this->schema),
            new RelationshipExtractor($this->schema, $this->server->resources()),
        );
    }
}
