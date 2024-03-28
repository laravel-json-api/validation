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
use LaravelJsonApi\Contracts\Schema\Field;
use LaravelJsonApi\Contracts\Schema\Filter;
use LaravelJsonApi\Core\Query\Input\Query;
use LaravelJsonApi\Validation\Filters\IsValidated;

final readonly class FilterRuleMap
{
    /**
     * @var array<Filter>
     */
    private array $filters;

    /**
     * Fluent constructor.
     *
     * @param array<Filter> $filters
     * @return self
     */
    public static function make(array $filters): self
    {
        return new self(...$filters);
    }

    /**
     * FilterRuleMap constructor.
     *
     * @param Filter ...$filters
     */
    public function __construct(Filter ...$filters)
    {
        $this->filters = $filters;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(?Request $request, Query $query): array
    {
        $rules = iterator_to_array($this->cursor(
            static fn (IsValidated $filter): Closure|array|null => $filter->validationRules($request, $query),
            $request,
            $query,
        ));

        ksort($rules);

        $names = array_keys($rules);

        return count($names) > 0 ? [
            '.' => ['array:' . implode(',', $names)],
            ...$rules,
        ] : $rules;
    }

    /**
     * @param Closure(IsValidated): array $fn
     * @param mixed ...$args
     * @return Generator<string, array>
     */
    private function cursor(Closure $fn, mixed ...$args): Generator
    {
        foreach ($this->filters as $filter) {
            if (!$filter instanceof IsValidated) {
                continue;
            }

            $rules = $fn($filter) ?? [];

            if ($rules instanceof Closure) {
                $rules = $rules(...$args) ?? [];
            }

            if (count($rules) > 0) {
                yield $filter->key() => $rules;
            }
        }
    }
}