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
