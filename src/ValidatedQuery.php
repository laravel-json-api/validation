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

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Request;
use LaravelJsonApi\Contracts\Schema\Container as SchemaContainer;
use LaravelJsonApi\Contracts\Schema\Query as QuerySchema;
use LaravelJsonApi\Contracts\Schema\Relation;
use LaravelJsonApi\Core\Query\Input\Query;
use LaravelJsonApi\Core\Values\ResourceType;
use LaravelJsonApi\Validation\Pagination\ValidatedPaginator;

class ValidatedQuery
{
    /**
     * @var bool
     */
    private bool $hasPaginator = false;

    /**
     * @var ValidatedPaginator|null
     */
    private ?ValidatedPaginator $paginator = null;

    /**
     * @var Relation|null
     */
    private ?Relation $relation = null;

    /**
     * ValidatedQuery constructor
     *
     * @param SchemaContainer $schemas
     * @param QuerySchema $schema
     * @param Request|null $request
     */
    public function __construct(
        private readonly SchemaContainer $schemas,
        private readonly QuerySchema $schema,
        private readonly ?Request $request,
    ) {
    }

    /**
     * @param ResourceType $type
     * @param string $fieldName
     * @return void
     */
    public function withRelation(ResourceType $type, string $fieldName): void
    {
        $this->relation = $this->schemas
            ->schemaFor($type)
            ->relationship($fieldName);
    }

    /**
     * @return iterable
     */
    public function filters(): iterable
    {
        yield from $this->schema->filters();
        yield from $this->relation?->filters() ?? [];
    }

    /**
     * @return ValidatedPaginator|null
     */
    public function pagination(): ?ValidatedPaginator
    {
        if ($this->hasPaginator === true) {
            return $this->paginator;
        }

        $paginator = $this->schema->pagination();
        $this->hasPaginator = true;

        return $this->paginator = $paginator ? new ValidatedPaginator($paginator, $this->request) : null;
    }

    /**
     * @return array
     */
    public function messages(): array
    {
        if (method_exists($this->schema, 'validationMessages')) {
            return $this->schema->validationMessages() ?? [];
        }

        return [];
    }

    /**
     * @return array
     */
    public function attributes(): array
    {
        if (method_exists($this->schema, 'validationAttributes')) {
            return $this->schema->validationAttributes() ?? [];
        }

        return [];
    }

    /**
     * @param Validator $validator
     * @param Query $query
     * @return void
     */
    public function withValidator(Validator $validator, Query $query): void
    {
        if (method_exists($this->schema, 'withValidator')) {
            $this->schema->withValidator($validator, $this->request, $query);
        }
    }

    /**
     * @param Validator $validator
     * @param Query $query
     * @return void
     */
    public function withToOneValidator(Validator $validator, Query $query): void
    {
        if (method_exists($this->schema, 'withToOneValidator')) {
            $this->schema->withToOneValidator($validator, $this->request, $query);
        }
    }

    /**
     * @param Validator $validator
     * @param Query $query
     * @return void
     */
    public function withToManyValidator(Validator $validator, Query $query): void
    {
        if (method_exists($this->schema, 'withToManyValidator')) {
            $this->schema->withToManyValidator($validator, $this->request, $query);
        }
    }

    /**
     * @param Validator $validator
     * @param Query $query
     * @return void
     */
    public function afterValidation(Validator $validator, Query $query): void
    {
        if (method_exists($this->schema, 'afterValidation')) {
            $this->schema->afterValidation($validator, $this->request, $query);
        }
    }

    /**
     * @param Validator $validator
     * @param Query $query
     * @return void
     */
    public function afterToOneValidation(Validator $validator, Query $query): void
    {
        if (method_exists($this->schema, 'afterToOneValidation')) {
            $this->schema->afterToOneValidation($validator, $this->request, $query);
        }
    }

    /**
     * @param Validator $validator
     * @param Query $query
     * @return void
     */
    public function afterToManyValidation(Validator $validator, Query $query): void
    {
        if (method_exists($this->schema, 'afterToManyValidation')) {
            $this->schema->afterToManyValidation($validator, $this->request, $query);
        }
    }
}
