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
use Illuminate\Support\Collection;
use LaravelJsonApi\Contracts\Schema\Relation;
use LaravelJsonApi\Contracts\Schema\Schema;
use LaravelJsonApi\Core\Extensions\Atomic\Operations\Create;
use LaravelJsonApi\Core\Extensions\Atomic\Operations\Delete;
use LaravelJsonApi\Core\Extensions\Atomic\Operations\Update;
use LaravelJsonApi\Core\Extensions\Atomic\Operations\UpdateToMany;
use LaravelJsonApi\Core\Extensions\Atomic\Operations\UpdateToOne;
use LaravelJsonApi\Core\Query\IncludePaths;
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
     * Get include paths for loading existing data.
     *
     * @return IncludePaths
     */
    public function includePaths(): IncludePaths
    {
        $paths = Collection::make($this->schema->relationships())
            ->filter(static fn (Relation $relation): bool => $relation->isValidated())
            ->map(static fn (Relation $relation): string => $relation->name())
            ->values();

        return IncludePaths::fromArray($paths);
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
     * Get the existing resource values.
     *
     * @param object $model
     * @param array $resource
     * @return array|null
     */
    public function withExisting(object $model, array $resource): ?array
    {
        if (method_exists($this->schema, 'withExisting')) {
            return $this->schema->withExisting($model, $resource);
        }

        return null;
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
    public function afterRelationshipValidation(
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
    public function deletionRules(object $model): array
    {
        if (method_exists($this->schema, 'deletionRules')) {
            return $this->schema->deletionRules($this->request, $model);
        }

        return [];
    }

    /**
     * @param object $model
     * @return array
     */
    public function metaForDeletion(object $model): array
    {
        if (method_exists($this->schema, 'metaForDeletion')) {
            return (array) $this->schema->metaForDeletion($this->request, $model);
        }

        return [];
    }

    /**
     * @return array
     */
    public function deletionMessages(): array
    {
        $default = $this->messages();
        $extra = [];

        if (method_exists($this->schema, 'deletionMessages')) {
            $extra = $this->schema->deletionMessages();
        }

        return [...$default, ...$extra];
    }

    /**
     * @return array
     */
    public function deletionAttributes(): array
    {
        $default = $this->attributes();
        $extra = [];

        if (method_exists($this->schema, 'deletionAttributes')) {
            $extra = $this->schema->deletionAttributes();
        }

        return [...$default, ...$extra];
    }

    /**
     * @param Validator $validator
     * @param Delete $operation
     * @param object $model
     * @return void
     */
    public function withDeletionValidator(Validator $validator, Delete $operation, object $model): void
    {
        if (method_exists($this->schema, 'withDeletionValidator')) {
            $this->schema->withDeletionValidator($validator, $this->request, $operation, $model);
        }
    }

    /**
     * @param Validator $validator
     * @param Delete $operation
     * @param object $model
     * @return void
     */
    public function afterDeletionValidation(Validator $validator, Delete $operation, object $model): void
    {
        if (method_exists($this->schema, 'afterDeletionValidation')) {
            $this->schema->afterDeletionValidation($validator, $this->request, $operation, $model);
        }
    }
}
