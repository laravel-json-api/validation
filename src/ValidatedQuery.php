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
use LaravelJsonApi\Contracts\Schema\Query;
use LaravelJsonApi\Contracts\Schema\Relation;
use LaravelJsonApi\Validation\Filters\ListOfFilters;
use LaravelJsonApi\Validation\Pagination\ValidatedPaginator;

class ValidatedQuery
{
    /**
     * @var ListOfFilters|null
     */
    private ?ListOfFilters $filters = null;

    /**
     * @var bool
     */
    private bool $hasPaginator = false;

    /**
     * @var ValidatedPaginator|null
     */
    private ?ValidatedPaginator $paginator = null;

    /**
     * ValidatedQuery constructor
     *
     * @param Query $schema
     * @param Relation|null $relation
     */
    public function __construct(
        private readonly Query $schema,
        private readonly ?Relation $relation = null,
    ) {
    }

    /**
     * @return ListOfFilters
     */
    public function filters(): ListOfFilters
    {
        if ($this->filters) {
            return $this->filters;
        }

        $related = $this->relation?->filters() ?? [];

        return $this->filters = new ListOfFilters(
            ...$this->schema->filters(),
            ...$related,
        );
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

        return $this->paginator = $paginator ? new ValidatedPaginator($paginator) : null;
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
     * @param Request|null $request
     * @return void
     */
    public function withValidator(Validator $validator, ?Request $request): void
    {
        if (method_exists($this->schema, 'withValidator')) {
            $this->schema->withValidator($validator, $request);
        }
    }

    /**
     * @param Validator $validator
     * @param Request|null $request
     * @return void
     */
    public function withToOneValidator(Validator $validator, ?Request $request): void
    {
        if (method_exists($this->schema, 'withToOneValidator')) {
            $this->schema->withToOneValidator($validator, $request);
        }
    }

    /**
     * @param Validator $validator
     * @param Request|null $request
     * @return void
     */
    public function withToManyValidator(Validator $validator, ?Request $request): void
    {
        if (method_exists($this->schema, 'withToManyValidator')) {
            $this->schema->withToManyValidator($validator, $request);
        }
    }

    /**
     * @param Validator $validator
     * @param Request|null $request
     * @return void
     */
    public function afterValidation(Validator $validator, ?Request $request): void
    {
        if (method_exists($this->schema, 'afterValidation')) {
            $this->schema->afterValidation($validator, $request);
        }
    }

    /**
     * @param Validator $validator
     * @param Request|null $request
     * @return void
     */
    public function afterToOneValidation(Validator $validator, ?Request $request): void
    {
        if (method_exists($this->schema, 'afterToOneValidation')) {
            $this->schema->afterToOneValidation($validator, $request);
        }
    }

    /**
     * @param Validator $validator
     * @param Request|null $request
     * @return void
     */
    public function afterToManyValidation(Validator $validator, ?Request $request): void
    {
        if (method_exists($this->schema, 'afterToManyValidation')) {
            $this->schema->afterToManyValidation($validator, $request);
        }
    }
}
