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
use Illuminate\Http\Request;
use LaravelJsonApi\Contracts\Validation\QueryOneValidator as QueryOneValidatorContract;
use LaravelJsonApi\Core\Query\Custom\ExtendedQueryParameters;
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
     * @param QueryRules $rules
     */
    public function __construct(
        private readonly ValidatorFactory $factory,
        private readonly ValidatedQuery $schema,
        private readonly QueryRules $rules,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function forRequest(Request $request): Validator
    {
        return $this->make($request, (array) $request->query());
    }

    /**
     * @inheritDoc
     */
    public function make(?Request $request, array $parameters): Validator
    {
        $validator = $this->factory->make(
            $parameters,
            $this->rules($request),
            $this->schema->messages(),
            $this->schema->attributes(),
        );

        $this->schema->withValidator($validator, $request);
        $this->schema->withToOneValidator($validator, $request);

        $validator->after(function (Validator $v) use ($request): void {
            $this->schema->afterValidation($v, $request);
            $this->schema->afterToOneValidation($v, $request);
        });

        return $validator;
    }

    /**
     * @param Request|null $request
     * @return array
     */
    private function rules(?Request $request): array
    {
        return [
            ...$this->defaultRules(),
            ...$this->schema->filters()->forOne($request),
        ];
    }

    /**
     * @return array
     */
    private function defaultRules(): array
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
                $this->rules->filters(),
            ],
            'include' => [
                'nullable',
                'string',
                $this->rules->includePaths(),
            ],
            'page' => new ParameterNotSupported(),
            'sort' => new ParameterNotSupported(),
            ExtendedQueryParameters::withCount() => [
                'nullable',
                'string',
                $this->rules->countable(),
            ],
        ];
    }
}
