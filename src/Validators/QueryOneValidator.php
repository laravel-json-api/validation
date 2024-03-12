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
use Illuminate\Contracts\Validation\Validator;
use LaravelJsonApi\Contracts\Validation\QueryOneValidator as QueryOneValidatorContract;
use LaravelJsonApi\Core\Query\Custom\ExtendedQueryParameters;
use LaravelJsonApi\Core\Query\Input\Query;
use LaravelJsonApi\Core\Query\Input\QueryOne;
use LaravelJsonApi\Core\Query\Input\QueryRelated;
use LaravelJsonApi\Core\Query\Input\QueryRelationship;
use LaravelJsonApi\Core\Query\Input\WillQueryOne;
use LaravelJsonApi\Validation\Filters\QueryOneParser;
use LaravelJsonApi\Validation\QueryRules;
use LaravelJsonApi\Validation\Rules\ParameterNotSupported;
use LaravelJsonApi\Validation\ValidatedQuery;

class QueryOneValidator implements QueryOneValidatorContract
{
    /**
     * QueryOneValidator constructor
     *
     * @param ValidatorFactory $factory
     * @param ValidatedQuery $schema
     * @param QueryOneParser $filterParser
     * @param QueryRules $rules
     */
    public function __construct(
        private readonly ValidatorFactory $factory,
        private readonly ValidatedQuery $schema,
        private readonly QueryOneParser $filterParser,
        private readonly QueryRules $rules,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function make(QueryOne|WillQueryOne|QueryRelated|QueryRelationship $query): Validator
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
        $this->schema->withToOneValidator($validator, $query);

        $validator->after(function (Validator $v) use ($query): void {
            $this->schema->afterValidation($v, $query);
            $this->schema->afterToOneValidation($v, $query);
        });

        return $validator;
    }

    /**
     * @param Query $query
     * @return array
     */
    private function rules(Query $query): array
    {
        $rules = [
            ...$this->defaultRules($query),
            ...$this->filterParser->with($query)->parse($this->schema->filters()),
        ];

        ksort($rules);

        return $rules;
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
            'page' => $this->rules->notSupported(),
            'sort' => $this->rules->notSupported(),
            ExtendedQueryParameters::withCount() => [
                'nullable',
                'string',
                $this->rules->countable(),
            ],
        ];
    }
}
