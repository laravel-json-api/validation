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

use Illuminate\Contracts\Validation\Factory as ValidatorFactory;
use Illuminate\Http\Request;
use LaravelJsonApi\Contracts\Schema\Schema;
use LaravelJsonApi\Contracts\Server\Server;
use LaravelJsonApi\Contracts\Validation\Factory as FactoryContract;
use LaravelJsonApi\Validation\Extractors\CreationExtractor;
use LaravelJsonApi\Validation\Extractors\DeleteExtractor;
use LaravelJsonApi\Validation\Extractors\RelationshipExtractor;
use LaravelJsonApi\Validation\Extractors\UpdateExtractor;
use LaravelJsonApi\Validation\Fields\CreationRulesParser;
use LaravelJsonApi\Validation\Fields\UpdateRulesParser;
use LaravelJsonApi\Validation\Filters\QueryManyParser;
use LaravelJsonApi\Validation\Filters\QueryOneParser;
use LaravelJsonApi\Validation\QueryRules;
use LaravelJsonApi\Validation\ValidatedQuery;
use LaravelJsonApi\Validation\ValidatedSchema;

class Factory implements FactoryContract
{
    /**
     * @var Request|null
     */
    private ?Request $request = null;

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
    public function withRequest(?Request $request): static
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function queryOne(): QueryOneValidator
    {
        return new QueryOneValidator(
            $this->validatorFactory,
            new ValidatedQuery(
                $this->server->schemas(),
                $this->schema->query(),
                $this->request,
            ),
            new QueryOneParser($this->request),
            new QueryRules(
                $this->server->schemas(),
                $this->schema,
            ),
        );
    }

    /**
     * @inheritDoc
     */
    public function queryMany(): QueryManyValidator
    {
        return new QueryManyValidator(
            $this->validatorFactory,
            new ValidatedQuery(
                $this->server->schemas(),
                $this->schema->query(),
                $this->request,
            ),
            new QueryManyParser($this->request),
            new QueryRules(
                $this->server->schemas(),
                $this->schema,
            ),
        );
    }

    /**
     * @inheritDoc
     */
    public function store(): StoreValidator
    {
        return new StoreValidator(
            $this->validatorFactory,
            new ValidatedSchema($this->schema, $this->request),
            new CreationExtractor(),
            new CreationRulesParser($this->request),
        );
    }

    /**
     * @inheritDoc
     */
    public function update(): UpdateValidator
    {
        return new UpdateValidator(
            $this->validatorFactory,
            new ValidatedSchema($this->schema, $this->request),
            new UpdateExtractor($this->schema, $this->server->encoder(), $this->request),
            new UpdateRulesParser($this->request),
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
                new ValidatedSchema($this->schema, $this->request),
                new DeleteExtractor(
                    $this->schema,
                    new UpdateExtractor($this->schema, $this->server->encoder(), $this->request),
                    $this->request,
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
            new ValidatedSchema($this->schema, $this->request),
            new RelationshipExtractor($this->schema, $this->server->resources()),
            new UpdateRulesParser($this->request),
        );
    }
}
