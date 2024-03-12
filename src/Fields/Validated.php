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
use Illuminate\Http\Request;

trait Validated
{
    /**
     * @var array
     */
    private array $rules = [];

    /**
     * @var array
     */
    private array $creationRules = [];

    /**
     * @var array
     */
    private array $updateRules = [];

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
     * @param mixed ...$args
     * @return $this
     */
    public function creationRules(mixed ...$args): static
    {
        $this->creationRules = $args;

        return $this;
    }

    /**
     * @param mixed ...$args
     * @return $this
     */
    public function updateRules(mixed ...$args): static
    {
        $this->updateRules = $args;

        return $this;
    }

    /**
     * @param Request|null $request
     * @return array
     */
    public function rulesForCreation(?Request $request): array
    {
        $rules = FieldRules::make()
            ->always($this->defaultRules())
            ->rules(...$this->rules)
            ->append(...$this->creationRules);

        return $rules($request);
    }

    /**
     * @param Request|null $request
     * @param object $model
     * @return array
     */
    public function rulesForUpdate(?Request $request, object $model): array
    {
        $rules = FieldRules::make()
            ->always($this->defaultRules())
            ->rules(...$this->rules)
            ->append(...$this->updateRules);

        return $rules($request, $model);
    }

    /**
     * @return Closure|array
     */
    protected function defaultRules(): Closure|array
    {
        return [];
    }
}
