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
use LaravelJsonApi\Contracts\Validation\RelationshipValidator as RelationshipValidatorContract;
use LaravelJsonApi\Core\Extensions\Atomic\Operations\UpdateToMany;
use LaravelJsonApi\Core\Extensions\Atomic\Operations\UpdateToOne;
use LaravelJsonApi\Validation\Extractors\RelationshipExtractor;
use LaravelJsonApi\Validation\Fields\UpdateRulesParser;
use LaravelJsonApi\Validation\ValidatedSchema;

final readonly class RelationshipValidator implements RelationshipValidatorContract
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
        private ValidatorFactory $factory,
        private ValidatedSchema $schema,
        private RelationshipExtractor $extractor,
        private UpdateRulesParser $parser,
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
