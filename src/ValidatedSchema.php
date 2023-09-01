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

namespace LaravelJsonApi\Validation;

use Generator;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Request;
use LaravelJsonApi\Contracts\Schema\Relation;
use LaravelJsonApi\Contracts\Schema\Schema;
use LaravelJsonApi\Core\Extensions\Atomic\Operations\Create;
use LaravelJsonApi\Core\Extensions\Atomic\Operations\Delete;
use LaravelJsonApi\Core\Extensions\Atomic\Operations\Update;
use LaravelJsonApi\Core\Extensions\Atomic\Operations\UpdateToMany;
use LaravelJsonApi\Core\Extensions\Atomic\Operations\UpdateToOne;
use LaravelJsonApi\Core\Support\Str;

class ValidatedSchema
{
    /**
     * ValidatedSchema constructor
     *
     * @param Schema $schema
     * @param Request|null $request
     */
    public function __construct(private readonly Schema $schema, private readonly Request|null $request)
    {
    }

    /**
     * @return Generator
     */
    public function fields(): Generator
    {
        yield from $this->schema->attributes();
        yield from $this->schema->relationships();
    }

    /**
     * @param string $fieldName
     * @return Relation
     */
    public function relationship(string $fieldName): Relation
    {
        return $this->schema->relationship($fieldName);
    }

    /**
     * @return array
     */
    public function messages(): array
    {
        if (method_exists($this->schema, 'validationMessages')) {
            return $this->schema->validationMessages() ?? [];
        }

        return [];
    }

    /**
     * @return array
     */
    public function attributes(): array
    {
        if (method_exists($this->schema, 'validationAttributes')) {
            return $this->schema->validationAttributes() ?? [];
        }

        return [];
    }

    /**
     * @param Validator $validator
     * @param Create|Update $operation
     * @return void
     */
    public function withValidator(Validator $validator, Create|Update $operation): void
    {
        if (method_exists($this->schema, 'withValidator')) {
            $this->schema->withValidator($validator, $this->request, $operation);
        }
    }

    /**
     * @param Validator $validator
     * @param Create $operation
     * @return void
     */
    public function withCreationValidator(Validator $validator, Create $operation): void
    {
        if (method_exists($this->schema, 'withCreationValidator')) {
            $this->schema->withCreationValidator($validator, $this->request, $operation);
        }
    }

    /**
     * @param Validator $validator
     * @param Update $operation
     * @param object $model
     * @return void
     */
    public function withUpdateValidator(Validator $validator, Update $operation, object $model): void
    {
        if (method_exists($this->schema, 'withUpdateValidator')) {
            $this->schema->withUpdateValidator($validator, $this->request, $operation, $model);
        }
    }

    /**
     * @param Validator $validator
     * @param UpdateToOne|UpdateToMany $operation
     * @param object $model
     * @return void
     */
    public function withRelationshipValidator(
        Validator $validator,
        UpdateToOne|UpdateToMany $operation,
        object $model,
    ): void
    {
        $fieldName = $operation->getFieldName();
        $fn = 'with' . Str::classify($fieldName) . 'Validator';

        if (method_exists($this->schema, $fn)) {
            $this->schema->{$fn}($validator, $this->request, $operation, $model);
        }
    }

    /**
     * @param Validator $validator
     * @param Create|Update $operation
     * @return void
     */
    public function afterValidation(Validator $validator, Create|Update $operation): void
    {
        if (method_exists($this->schema, 'afterValidation')) {
            $this->schema->afterValidation($validator, $this->request, $operation);
        }
    }

    /**
     * @param Validator $validator
     * @param Create $operation
     * @return void
     */
    public function afterCreationValidation(Validator $validator, Create $operation): void
    {
        if (method_exists($this->schema, 'afterCreationValidation')) {
            $this->schema->afterCreationValidation($validator, $this->request, $operation);
        }
    }

    /**
     * @param Validator $validator
     * @param Update $operation
     * @param object $model
     * @return void
     */
    public function afterUpdateValidation(Validator $validator, Update $operation, object $model): void
    {
        if (method_exists($this->schema, 'afterUpdateValidation')) {
            $this->schema->afterUpdateValidation($validator, $this->request, $operation, $model);
        }
    }

    /**
     * @param Validator $validator
     * @param UpdateToOne|UpdateToMany $operation
     * @param object $model
     * @return void
     */
    public function afterRelationshipValidator(
        Validator $validator,
        UpdateToOne|UpdateToMany $operation,
        object $model,
    ): void
    {
        $fieldName = $operation->getFieldName();
        $fn = 'after' . Str::classify($fieldName) . 'Validation';

        if (method_exists($this->schema, $fn)) {
            $this->schema->{$fn}($validator, $this->request, $operation, $model);
        }
    }

    /**
     * Get the rules for deleting a resource.
     *
     * @param object $model
     * @return array
     */
    public function deleteRules(object $model): array
    {
        if (method_exists($this->schema, 'deleteRules')) {
            return $this->schema->deleteRules($this->request, $model);
        }

        return [];
    }

    /**
     * @return array
     */
    public function deleteMessages(): array
    {
        $default = $this->messages();
        $extra = [];

        if (method_exists($this->schema, 'deleteMessages')) {
            $extra = $this->schema->deleteMessages();
        }

        return [...$default, ...$extra];
    }

    /**
     * @return array
     */
    public function deleteAttributes(): array
    {
        $default = $this->attributes();
        $extra = [];

        if (method_exists($this->schema, 'deleteAttributes')) {
            $extra = $this->schema->deleteAttributes();
        }

        return [...$default, ...$extra];
    }

    /**
     * @param Validator $validator
     * @param Delete $operation
     * @param object $model
     * @return void
     */
    public function withDeleteValidator(Validator $validator, Delete $operation, object $model): void
    {
        if (method_exists($this->schema, 'withDeleteValidator')) {
            $this->schema->withDeleteValidator($validator, $this->request, $operation, $model);
        }
    }

    /**
     * @param Validator $validator
     * @param Delete $operation
     * @param object $model
     * @return void
     */
    public function afterDeleteValidation(Validator $validator, Delete $operation, object $model): void
    {
        if (method_exists($this->schema, 'afterDeleteValidation')) {
            $this->schema->afterDeleteValidation($validator, $operation, $model);
        }
    }
}
