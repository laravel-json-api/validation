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
use LaravelJsonApi\Contracts\Validation\CreationValidator as CreationValidatorContract;
use LaravelJsonApi\Core\Extensions\Atomic\Operations\Create;
use LaravelJsonApi\Validation\Extractors\CreationExtractor;
use LaravelJsonApi\Validation\Fields\CreationRulesParser;
use LaravelJsonApi\Validation\ValidatedSchema;

final readonly class CreationValidator implements CreationValidatorContract
{
    /**
     * CreationValidator constructor
     *
     * @param ValidatorFactory $factory
     * @param ValidatedSchema $schema
     * @param CreationExtractor $extractor
     * @param CreationRulesParser $parser
     */
    public function __construct(
        private ValidatorFactory $factory,
        private ValidatedSchema $schema,
        private CreationExtractor $extractor,
        private CreationRulesParser $parser,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function extract(Create $operation): array
    {
        return $this->extractor->extract($operation);
    }

    /**
     * @inheritDoc
     */
    public function make(Create $operation): Validator
    {
        $validator = $this->factory->make(
            $this->extract($operation),
            $this->parser->parse($this->schema->fields()),
            $this->schema->messages(),
            $this->schema->attributes(),
        );

        $this->schema->withValidator($validator, $operation);
        $this->schema->withCreationValidator($validator, $operation);

        $validator->after(function (Validator $v) use ($operation): void {
            $this->schema->afterValidation($v, $operation);
            $this->schema->afterCreationValidation($v, $operation);
        });

        return $validator;
    }
}
