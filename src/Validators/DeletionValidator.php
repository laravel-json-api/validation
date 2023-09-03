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
use LaravelJsonApi\Contracts\Validation\DeletionValidator as DeletionValidatorContract;
use LaravelJsonApi\Core\Extensions\Atomic\Operations\Delete;
use LaravelJsonApi\Validation\Extractors\DeletionExtractor;
use LaravelJsonApi\Validation\ValidatedSchema;

class DeletionValidator implements DeletionValidatorContract
{
    /**
     * DeletionValidator constructor
     *
     * @param ValidatorFactory $factory
     * @param ValidatedSchema $schema
     * @param DeletionExtractor $extractor
     */
    public function __construct(
        private readonly ValidatorFactory $factory,
        private readonly ValidatedSchema $schema,
        private readonly DeletionExtractor $extractor,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function extract(Delete $operation, object $model): array
    {
        return $this->extractor->extract($model);
    }

    /**
     * @inheritDoc
     */
    public function make(Delete $operation, object $model): Validator
    {
        $validator = $this->factory->make(
            $this->extract($operation, $model),
            $this->schema->deletionRules($model),
            $this->schema->deletionMessages(),
            $this->schema->deletionAttributes(),
        );

        $this->schema->withDeletionValidator($validator, $operation, $model);

        $validator->after(function (Validator $v) use ($operation, $model): void {
            $this->schema->afterDeletionValidation($v, $operation, $model);
        });

        return $validator;
    }
}
