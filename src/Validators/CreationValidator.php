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
use LaravelJsonApi\Contracts\Validation\CreationValidator as CreationValidatorContract;
use LaravelJsonApi\Core\Extensions\Atomic\Operations\Create;
use LaravelJsonApi\Validation\Extractors\CreationExtractor;
use LaravelJsonApi\Validation\Fields\CreationRulesParser;
use LaravelJsonApi\Validation\ValidatedSchema;

class CreationValidator implements CreationValidatorContract
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
        private readonly ValidatorFactory $factory,
        private readonly ValidatedSchema $schema,
        private readonly CreationExtractor $extractor,
        private readonly CreationRulesParser $parser,
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
