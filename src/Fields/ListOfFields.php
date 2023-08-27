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

namespace LaravelJsonApi\Validation\Fields;

use Closure;
use Generator;
use Illuminate\Http\Request;
use IteratorAggregate;
use LaravelJsonApi\Contracts\Schema\Field;

/**
 * @implements IteratorAggregate<int,Field&IsValidated>
 */
class ListOfFields implements IteratorAggregate
{
    /**
     * @var array<int,Field>
     */
    private readonly array $fields;

    /**
     * @param Field ...$fields
     */
    public function __construct(Field ...$fields)
    {
        $this->fields = $fields;
    }

    /**
     * @return Generator<int,Field&IsValidated>
     */
    public function getIterator(): Generator
    {
        foreach ($this->fields as $field) {
            if ($field instanceof IsValidated) {
                yield $field;
            }
        }
    }

    /**
     * Get validation rules for creating a resource.
     *
     * @param Request|null $request
     * @return array
     */
    public function forCreate(?Request $request): array
    {
        return iterator_to_array($this->cursor(
            static fn (Field&IsValidated $field): Closure|array|null => $field->rulesForCreate($request),
            $request,
            null,
        ));
    }

    /**
     * Get validation rules for updating a resource.
     *
     * @param Request|null $request
     * @param object $model
     * @return array
     */
    public function forUpdate(?Request $request, object $model): array
    {
        return iterator_to_array($this->cursor(
            static fn(Field&IsValidated $field): Closure|array|null => $field->rulesForUpdate($request, $model),
            $request,
            $model,
        ));
    }

    /**
     * @param Request|null $request
     * @param object $model
     * @param string $fieldName
     * @return array
     */
    public function forRelation(?Request $request, object $model, string $fieldName): array
    {
        return iterator_to_array($this->cursor(
            static function (Field&IsValidated $field) use ($request, $model, $fieldName): Closure|array|null {
                if ($field->name() === $fieldName) {
                    return $field->rulesForUpdate($request, $model);
                }
                return null;
            },
            $request,
            $model,
        ));
    }

    /**
     * @param Closure(Field&IsValidated): (Closure|array|null) $callback
     * @param Request|null $request
     * @param object|null $model
     * @return Generator
     */
    private function cursor(Closure $callback, ?Request $request, ?object $model): Generator
    {
        foreach ($this as $field) {
            $rules = $callback($field);

            if ($rules instanceof Closure) {
                $rules = $rules($request, $model);
                assert($rules === null || is_array($rules), sprintf(
                    'Validation rules closure for field %s must return an array or null.',
                    $field->name(),
                ));
            }

            if (empty($rules)) {
                continue;
            }

            if (array_is_list($rules)) {
                yield $field->name() => $rules;
                continue;
            }

            yield from $rules;
        }
    }
}
