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
use LaravelJsonApi\Contracts\Validation\StoreValidator as StoreValidatorContract;
use LaravelJsonApi\Core\Extensions\Atomic\Operations\Create;
use LaravelJsonApi\Validation\Extractors\CreationExtractor;
use LaravelJsonApi\Validation\ValidatedSchema;

class StoreValidator implements StoreValidatorContract
{
    /**
     * StoreValidator constructor
     *
     * @param ValidatorFactory $factory
     * @param ValidatedSchema $schema
     * @param CreationExtractor $extractor
     */
    public function __construct(
        private readonly ValidatorFactory $factory,
        private readonly ValidatedSchema $schema,
        private readonly CreationExtractor $extractor,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function extract(?Request $request, Create $operation): array
    {
        return $this->extractor->extract($operation);
    }

    /**
     * @inheritDoc
     */
    public function make(?Request $request, Create $operation): Validator
    {
        $validator = $this->factory->make(
            $this->extract($request, $operation),
            $this->schema->fields()->forCreate($request),
            $this->schema->messages(),
            $this->schema->attributes(),
        );

        $this->schema->withValidator($validator, $request);
        $this->schema->withCreationValidator($validator, $request);

        $validator->after(function (Validator $v) use ($request): void {
            $this->schema->afterValidation($v, $request);
            $this->schema->afterCreationValidation($v, $request);
        });

        return $validator;
    }
}
