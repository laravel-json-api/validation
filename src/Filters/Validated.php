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

trait Validated
{
    /**
     * @var array<int, mixed>
     */
    private array $rules = [];

    /**
     * @var bool
     */
    private bool $validateToOne = true;

    /**
     * @var bool
     */
    private bool $validateToMany = true;

    /**
     * Only validate this filter when a query will return zero-to-one resources.
     *
     * @return $this
     */
    public function onlyToOne(): static
    {
        $this->validateToOne = true;
        $this->validateToMany = false;

        return $this;
    }

    /**
     * Only validate this filter when a query will return zero-to-many resources.
     *
     * @return $this
     */
    public function onlyToMany(): static
    {
        $this->validateToOne = false;
        $this->validateToMany = true;

        return $this;
    }

    /**
     * Is the filter validated when a query will return zero-to-one resources?
     *
     * @return bool
     */
    public function isValidatedForOne(): bool
    {
        return $this->validateToOne;
    }

    /**
     * Is the filter validated when a query will return zero-to-many resources?
     *
     * @return bool
     */
    public function isValidatedForMany(): bool
    {
        return $this->validateToMany;
    }

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