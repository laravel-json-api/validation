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
use Illuminate\Contracts\Validation\Validator;
use LaravelJsonApi\Contracts\Validation\QueryManyValidator as QueryManyValidatorContract;
use LaravelJsonApi\Core\Query\Custom\ExtendedQueryParameters;
use LaravelJsonApi\Core\Query\Input\Query;
use LaravelJsonApi\Core\Query\Input\QueryMany;
use LaravelJsonApi\Core\Query\Input\QueryRelated;
use LaravelJsonApi\Core\Query\Input\QueryRelationship;
use LaravelJsonApi\Validation\Filters\QueryManyParser;
use LaravelJsonApi\Validation\QueryRules;
use LaravelJsonApi\Validation\Rules\ParameterNotSupported;
use LaravelJsonApi\Validation\ValidatedQuery;

class QueryManyValidator implements QueryManyValidatorContract
{
    /**
     * QueryManyValidator constructor
     *
     * @param ValidatorFactory $factory
     * @param ValidatedQuery $schema
     * @param QueryManyParser $filterParser
     * @param QueryRules $rules
     */
    public function __construct(
        private readonly ValidatorFactory $factory,
        private readonly ValidatedQuery $schema,
        private readonly QueryManyParser $filterParser,
        private readonly QueryRules $rules,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function make(QueryMany|QueryRelated|QueryRelationship $query): Validator
    {
        if ($fieldName = $query->getFieldName()) {
            $this->schema->withRelation(
                $query->type,
                $fieldName,
            );
        }

        $validator = $this->factory->make(
            $query->parameters,
            $this->rules($query),
            $this->schema->messages(),
            $this->schema->attributes(),
        );

        $this->schema->withValidator($validator, $query);
        $this->schema->withToManyValidator($validator, $query);

        $validator->after(function (Validator $v) use ($query): void {
            $this->schema->afterValidation($v, $query);
            $this->schema->afterToManyValidation($v, $query);
        });

        return $validator;
    }

    /**
     * @param Query $query
     * @return array
     */
    private function rules(Query $query): array
    {
        $page = $this->schema->pagination()?->rules($query) ?? [];

        return [
            ...$this->defaultRules($query),
            ...$this->filterParser->parse($this->schema->filters()),
            ...$page,
        ];
    }

    /**
     * @param Query $query
     * @return array
     */
    private function defaultRules(Query $query): array
    {
        return [
            'fields' => [
                'nullable',
                'array',
                $this->rules->fieldSets(),
            ],
            'filter' => [
                'nullable',
                'array',
                $this->rules->filters($query),
            ],
            'include' => [
                'nullable',
                'string',
                $this->rules->includePaths(),
            ],
            'page' => $this->schema->pagination() ? [
                'nullable',
                'array',
                $this->rules->page(),
            ] : new ParameterNotSupported(),
            'sort' => [
                'nullable',
                'string',
                $this->rules->sort(),
            ],
            ExtendedQueryParameters::withCount() => [
                'nullable',
                'string',
                $this->rules->countable(),
            ],
        ];
    }
}
