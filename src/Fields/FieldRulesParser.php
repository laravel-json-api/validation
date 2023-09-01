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
use LaravelJsonApi\Contracts\Schema\Field;
use RuntimeException;

abstract class FieldRulesParser
{
    /**
     * @var object|null
     */
    protected ?object $model = null;

    /**
     * @var string
     */
    private string $position = '';

    /**
     * @param IsValidated&Field $field
     * @return Closure|array|null
     */
    abstract protected function extract(Field&IsValidated $field): Closure|array|null;

    /**
     * FieldRulesParser constructor
     *
     * @param Request|null $request
     */
    public function __construct(protected readonly ?Request $request)
    {
    }

    /**
     * @param iterable $fields
     * @return array
     */
    public function parse(iterable $fields): array
    {
        return iterator_to_array($this->cursor($fields));
    }

    /**
     * @param iterable $values
     * @return Generator
     */
    protected function cursor(iterable $values): Generator
    {
        foreach ($values as $key => $value) {
            if ($value instanceof Field) {
                $key = is_int($key) ? $value->name() : $key;
                $value = $value instanceof IsValidated ? $this->extract($value) : null;
            }

            if ($value instanceof \Closure) {
                $value = $value($this->request, $this->model);
            }

            assert(
                $value === null || is_array($value),
                'Expecting value to resolve to an array or null.',
            );

            if (empty($value)) {
                continue;
            }

            $path = $this->path($key);

            if (array_is_list($value)) {
                yield $path => $value;
                continue;
            }

            yield from $this->nested($path)->cursor($value);
        }
    }

    /**
     * @param string $key
     * @return string
     */
    private function path(string $key): string
    {
        if ($key === '.') {
            return $this->position ?? throw new RuntimeException('Not expecting key "." at the root of schema fields.');
        }

        return $this->position ? "{$this->position}.{$key}" : $key;
    }

    /**
     * @param string $path
     * @return $this
     */
    private function nested(string $path): static
    {
        $copy = clone $this;
        $copy->position = $path;

        return $copy;
    }
}
