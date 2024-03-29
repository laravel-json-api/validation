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
use Illuminate\Http\Request;
use LaravelJsonApi\Core\Query\Input\Query;
use LaravelJsonApi\Validation\Utils\UnknownSetOfRules;

trait ValidatedWithRules
{
    use Validated;

    /**
     * @var array<int, mixed>
     */
    private array $rules = [];

    /**
     * @param mixed ...$args
     * @return $this
     */
    public function rules(mixed ...$args): static
    {
        $this->rules = $args;

        return $this;
    }

    /**
     * @param Request|null $request
     * @param Query $query
     * @return array
     */
    public function validationRules(?Request $request, Query $query): array
    {
        return UnknownSetOfRules::make()
            ->defaults($this->defaultRules())
            ->rules(...$this->rules)
            ->all($request, $query);
    }

    /**
     * @return Closure|array
     */
    protected function defaultRules(): Closure|array
    {
        return [];
    }
}