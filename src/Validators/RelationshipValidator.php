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
use LaravelJsonApi\Contracts\Validation\RelationshipValidator as RelationshipValidatorContract;
use LaravelJsonApi\Core\Extensions\Atomic\Operations\UpdateToMany;
use LaravelJsonApi\Core\Extensions\Atomic\Operations\UpdateToOne;
use LaravelJsonApi\Validation\Extractors\RelationshipExtractor;
use LaravelJsonApi\Validation\Fields\UpdateRulesParser;
use LaravelJsonApi\Validation\ValidatedSchema;

class RelationshipValidator implements RelationshipValidatorContract
{
    /**
     * RelationshipValidator constructor
     *
     * @param ValidatorFactory $factory
     * @param ValidatedSchema $schema
     * @param RelationshipExtractor $extractor
     * @param UpdateRulesParser $parser
     */
    public function __construct(
        private readonly ValidatorFactory $factory,
        private readonly ValidatedSchema $schema,
        private readonly RelationshipExtractor $extractor,
        private readonly UpdateRulesParser $parser,
    ){
    }

    /**
     * @inheritDoc
     */
    public function extract(UpdateToOne|UpdateToMany $operation, object $model): array
    {
        return $this->extractor->extract($operation, $model);
    }

    /**
     * @inheritDoc
     */
    public function make(UpdateToOne|UpdateToMany $operation, object $model): Validator
    {
        $fieldName = $operation->getFieldName();
        $rules = $this->parser->with($model)->parse([
            $this->schema->relationship($fieldName),
        ]);

        assert(!empty($rules), 'Expecting validation rules for relationship ' . $fieldName);

        $validator = $this->factory->make(
            $this->extract($operation, $model),
            $rules,
            $this->schema->messages(),
            $this->schema->attributes(),
        );

        $this->schema->withRelationshipValidator($validator, $operation, $model);

        $validator->after(function (Validator $v) use ($operation, $model): void {
            $this->schema->afterRelationshipValidation($v, $operation, $model);
        });

        return $validator;
    }
}
