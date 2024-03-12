<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
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
     * @param IsValidated $field
     * @return Closure|array|null
     */
    abstract protected function extract(IsValidated $field): Closure|array|null;

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
    private function cursor(iterable $values): Generator
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
