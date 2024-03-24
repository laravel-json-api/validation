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
use LaravelJsonApi\Contracts\Validation\UpdateValidator as UpdateValidatorContract;
use LaravelJsonApi\Core\Extensions\Atomic\Operations\Update;
use LaravelJsonApi\Validation\Extractors\UpdateExtractor;
use LaravelJsonApi\Validation\Fields\UpdateRulesParser;
use LaravelJsonApi\Validation\ValidatedSchema;

final readonly class UpdateValidator implements UpdateValidatorContract
{
    /**
     * UpdateValidator constructor
     *
     * @param ValidatorFactory $factory
     * @param ValidatedSchema $schema
     * @param UpdateExtractor $extractor
     * @param UpdateRulesParser $parser
     */
    public function __construct(
        private ValidatorFactory $factory,
        private ValidatedSchema $schema,
        private UpdateExtractor $extractor,
        private UpdateRulesParser $parser,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function extract(Update $operation, object $model): array
    {
        return $this->extractor->extract($operation, $model);
    }

    /**
     * @inheritDoc
     */
    public function make(Update $operation, object $model): Validator
    {
        $validator = $this->factory->make(
            $this->extract($operation, $model),
            $this->parser->with($model)->parse($this->schema->fields()),
            $this->schema->messages(),
            $this->schema->attributes(),
        );

        $this->schema->withValidator($validator, $operation);
        $this->schema->withUpdateValidator($validator, $operation, $model);

        $validator->after(function (Validator $v) use ($operation, $model): void {
            $this->schema->afterValidation($v, $operation);
            $this->schema->afterUpdateValidation($v, $operation, $model);
        });

        return $validator;
    }
}
