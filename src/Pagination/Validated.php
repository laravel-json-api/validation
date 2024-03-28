<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Validation\Pagination;

use Closure;
use Illuminate\Http\Request;
use LaravelJsonApi\Core\Query\Input\Query;
use LaravelJsonApi\Validation\Utils\KeyedSetOfRules;

trait Validated
{
    /**
     * @var Closure|array
     */
    private Closure|array $rules = [];

    /**
     * @param Closure|array $rules
     * @return $this
     */
    public function rules(Closure|array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    /**
     * @param Request|null $request
     * @param Query $query
     * @return array
     */
    public function validationRules(?Request $request, Query $query): array
    {
        return KeyedSetOfRules::make()
            ->prepend($this->defaultRules())
            ->rules($this->rules)
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