<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Validation\Filters;

use Closure;
use Generator;
use Illuminate\Http\Request;
use LaravelJsonApi\Contracts\Schema\Filter;
use LaravelJsonApi\Core\Query\Input\Query;
use RuntimeException;

abstract class FilterRulesParser
{
    /**
     * @var Query|null
     */
    private ?Query $query = null;

    /**
     * @var string
     */
    private string $position = 'filter';

    /**
     * @param IsValidated $filter
     * @return bool
     */
    abstract protected function isValidated(IsValidated $filter): bool;

    /**
     * FilterRulesParser constructor
     *
     * @param Request|null $request
     */
    public function __construct(protected readonly ?Request $request)
    {
    }

    /**
     * @param Query $query
     * @return $this
     */
    public function with(Query $query): self
    {
        $this->query = $query;

        return $this;
    }

    /**
     * @param iterable $filters
     * @return array
     */
    public function parse(iterable $filters): array
    {
        return iterator_to_array($this->cursor($filters));
    }

    /**
     * @param iterable $values
     * @return Generator
     */
    private function cursor(iterable $values): Generator
    {
        foreach ($values as $key => $value) {
            if ($value instanceof Filter) {
                $key = is_int($key) ? $value->key() : $key;
                $value = $this->validationRules($value);
            }

            if ($value instanceof Closure) {
                $value = $value($this->request, $this->query);
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
     * @param Filter $filter
     * @return Closure|array|null
     */
    private function validationRules(Filter $filter): Closure|array|null
    {
        if ($filter instanceof IsValidated && $this->isValidated($filter)) {
            return $filter->validationRules($this->request, $this->query);
        }

        return null;
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
