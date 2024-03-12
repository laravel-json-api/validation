<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Validation\Validators;

use Illuminate\Contracts\Validation\Factory as ValidatorFactory;
use Illuminate\Http\Request;
use LaravelJsonApi\Contracts\Schema\Schema;
use LaravelJsonApi\Contracts\Server\Server;
use LaravelJsonApi\Contracts\Validation\Factory as FactoryContract;
use LaravelJsonApi\Validation\Extractors\CreationExtractor;
use LaravelJsonApi\Validation\Extractors\DeletionExtractor;
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
            $this->query(),
            new QueryOneParser($this->request),
            new QueryRules($this->server->schemas(), $this->schema),
        );
    }

    /**
     * @inheritDoc
     */
    public function queryMany(): QueryManyValidator
    {
        return new QueryManyValidator(
            $this->validatorFactory,
            $this->query(),
            new QueryManyParser($this->request),
            new QueryRules($this->server->schemas(), $this->schema),
        );
    }

    /**
     * @inheritDoc
     */
    public function store(): CreationValidator
    {
        return new CreationValidator(
            $this->validatorFactory,
            $this->schema(),
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
            $schema = $this->schema(),
            new UpdateExtractor($schema, $this->server->encoder(), $this->request),
            new UpdateRulesParser($this->request),
        );
    }

    /**
     * @inheritDoc
     */
    public function destroy(): ?DeletionValidator
    {
        if (method_exists($this->schema, 'deleteRules')) {
            return new DeletionValidator(
                $this->validatorFactory,
                $schema = $this->schema(),
                new DeletionExtractor(
                    $schema,
                    new UpdateExtractor($schema, $this->server->encoder(), $this->request),
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
            $this->schema(),
            new RelationshipExtractor(),
            new UpdateRulesParser($this->request),
        );
    }

    /**
     * @return ValidatedSchema
     */
    private function schema(): ValidatedSchema
    {
        return new ValidatedSchema($this->schema, $this->request);
    }

    /**
     * @return ValidatedQuery
     */
    private function query(): ValidatedQuery
    {
        return new ValidatedQuery(
            $this->server->schemas(),
            $this->schema->query(),
            $this->request,
        );
    }
}
