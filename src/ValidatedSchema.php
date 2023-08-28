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

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Request;
use LaravelJsonApi\Contracts\Schema\Schema;
use LaravelJsonApi\Core\Support\Str;
use LaravelJsonApi\Validation\Fields\ListOfFields;

class ValidatedSchema
{
    /**
     * @var ListOfFields|null
     */
    private ?ListOfFields $fields = null;

    /**
     * ValidatedSchema constructor
     *
     * @param Schema $schema
     */
    public function __construct(private readonly Schema $schema)
    {
    }

    /**
     * @return ListOfFields
     */
    public function fields(): ListOfFields
    {
        if ($this->fields) {
            return $this->fields;
        }

        return $this->fields = new ListOfFields(
            ...$this->schema->attributes(),
            ...$this->schema->relationships(),
        );
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
     * @param Request|null $request
     * @return void
     */
    public function withValidator(Validator $validator, ?Request $request): void
    {
        if (method_exists($this->schema, 'withValidator')) {
            $this->schema->withValidator($validator, $request);
        }
    }

    /**
     * @param Validator $validator
     * @param Request|null $request
     * @return void
     */
    public function withCreationValidator(Validator $validator, ?Request $request): void
    {
        if (method_exists($this->schema, 'withCreationValidator')) {
            $this->schema->withCreationValidator($validator, $request);
        }
    }

    /**
     * @param Validator $validator
     * @param Request|null $request
     * @param object $model
     * @return void
     */
    public function withUpdateValidator(Validator $validator, ?Request $request, object $model): void
    {
        if (method_exists($this->schema, 'withUpdateValidator')) {
            $this->schema->withUpdateValidator($validator, $request, $model);
        }
    }

    /**
     * @param Validator $validator
     * @param Request|null $request
     * @param object $model
     * @param string $fieldName
     * @return void
     */
    public function withRelationshipValidator(
        Validator $validator,
        ?Request $request,
        object $model,
        string $fieldName,
    ): void
    {
        $fn = 'with' . Str::classify($fieldName) . 'Validator';

        if (method_exists($this->schema, $fn)) {
            $this->schema->{$fn}($validator, $request, $model);
        }
    }

    /**
     * @param Validator $validator
     * @param Request|null $request
     * @return void
     */
    public function afterValidation(Validator $validator, ?Request $request): void
    {
        if (method_exists($this->schema, 'afterValidation')) {
            $this->schema->afterValidation($validator, $request);
        }
    }

    /**
     * @param Validator $validator
     * @param Request|null $request
     * @return void
     */
    public function afterCreationValidation(Validator $validator, ?Request $request): void
    {
        if (method_exists($this->schema, 'afterCreationValidation')) {
            $this->schema->afterCreationValidation($validator, $request);
        }
    }

    /**
     * @param Validator $validator
     * @param Request|null $request
     * @param object $model
     * @return void
     */
    public function afterUpdateValidation(Validator $validator, ?Request $request, object $model): void
    {
        if (method_exists($this->schema, 'afterUpdateValidation')) {
            $this->schema->afterUpdateValidation($validator, $request, $model);
        }
    }

    /**
     * @param Validator $validator
     * @param Request|null $request
     * @param object $model
     * @param string $fieldName
     * @return void
     */
    public function afterRelationshipValidator(
        Validator $validator,
        ?Request $request,
        object $model,
        string $fieldName,
    ): void
    {
        $fn = 'after' . Str::classify($fieldName) . 'Validation';

        if (method_exists($this->schema, $fn)) {
            $this->schema->{$fn}($validator, $request, $model);
        }
    }

    /**
     * Get the rules for deleting a resource.
     *
     * @param Request|null $request
     * @param object $model
     * @return array
     */
    public function deleteRules(?Request $request, object $model): array
    {
        if (method_exists($this->schema, 'deleteRules')) {
            return $this->schema->deleteRules($request, $model);
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
     * @param Request|null $request
     * @param object $model
     * @return void
     */
    public function withDeleteValidator(Validator $validator, ?Request $request, object $model): void
    {
        if (method_exists($this->schema, 'withDeleteValidator')) {
            $this->schema->withDeleteValidator($validator, $request, $model);
        }
    }

    /**
     * @param Validator $validator
     * @param Request|null $request
     * @param object $model
     * @return void
     */
    public function afterDeleteValidation(Validator $validator, ?Request $request, object $model): void
    {
        if (method_exists($this->schema, 'afterDeleteValidation')) {
            $this->schema->afterDeleteValidation($validator, $request, $model);
        }
    }
}
