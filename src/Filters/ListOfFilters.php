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

namespace LaravelJsonApi\Validation\Filters;

use Closure;
use Generator;
use Illuminate\Http\Request;
use IteratorAggregate;
use LaravelJsonApi\Contracts\Schema\Filter;
use LaravelJsonApi\Core\Query\Input\Query;

/**
 * @implements IteratorAggregate<int,Filter&IsValidated>
 */
class ListOfFilters implements IteratorAggregate
{
    /**
     * @var array<int,Filter>
     */
    private readonly array $filters;

    /**
     * @var Request|null
     */
    private ?Request $request = null;

    /**
     * ListOfFilters constructor
     *
     * @param Filter ...$filters
     */
    public function __construct(Filter ...$filters)
    {
        $this->filters = $filters;
    }

    /**
     * @param Request|null $request
     * @return $this
     */
    public function withRequest(?Request $request): self
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @return Generator<int,Filter&IsValidated>
     */
    public function getIterator(): Generator
    {
        foreach ($this->filters as $field) {
            if ($field instanceof IsValidated) {
                yield $field;
            }
        }
    }

    /**
     * @return Filter[]
     */
    public function all(): array
    {
        return $this->filters;
    }

    /**
     * Get validation rules for querying zero-to-one resources.
     *
     * @param Query $query
     * @return array
     */
    public function forOne(Query $query): array
    {
        return iterator_to_array($this->cursor(
            static fn (Filter&IsValidated $field): Closure|array|null => $field->rulesForOne($this->request, $query),
            $query,
        ));
    }

    /**
     * Get validation rules for querying zero-to-many resources.
     *
     * @return array
     */
    public function forMany(Query $query): array
    {
        return iterator_to_array($this->cursor(
            static fn(Filter&IsValidated $field): Closure|array|null => $field->rulesForMany($this->request, $query),
            $query,
        ));
    }

    /**
     * @param Closure(Filter&IsValidated): (Closure|array|null) $callback
     * @param Query $query
     * @return Generator
     */
    private function cursor(Closure $callback, Query $query): Generator
    {
        foreach ($this as $filter) {
            $rules = $callback($filter);

            if ($rules instanceof Closure) {
                $rules = $rules($this->request, $query);
                assert($rules === null || is_array($rules), sprintf(
                    'Validation rules closure for filter %s must return an array or null.',
                    $filter->key(),
                ));
            }

            if (empty($rules)) {
                continue;
            }

            if (array_is_list($rules)) {
                yield 'filter.' . $filter->key() => $rules;
                continue;
            }

            foreach ($rules as $key => $value) {
                yield 'filter.' . $key => $value;
            }
        }
    }
}
