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
use LaravelJsonApi\Validation\Utils\KeyedSetOfRules;

trait ValidatedWithArrayKeys
{
    /**
     * @var Closure|array
     */
    private Closure|array $rules = [];

    /**
     * @var Closure|array
     */
    private Closure|array $creationRules = [];

    /**
     * @var Closure|array
     */
    private Closure|array $updateRules = [];

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
     * @param Closure|array $rules
     * @return $this
     */
    public function creationRules(Closure|array $rules): static
    {
        $this->creationRules = $rules;

        return $this;
    }

    /**
     * @param Closure|array $rules
     * @return $this
     */
    public function updateRules(Closure|array $rules): static
    {
        $this->updateRules = $rules;

        return $this;
    }

    /**
     * @param Request|null $request
     * @return array
     */
    public function rulesForCreation(?Request $request): array
    {
        return KeyedSetOfRules::make()
            ->prepend($this->defaultRules())
            ->rules($this->rules)
            ->append($this->creationRules)
            ->all($request);
    }

    /**
     * @param Request|null $request
     * @param object $model
     * @return array
     */
    public function rulesForUpdate(?Request $request, object $model): array
    {
        return KeyedSetOfRules::make()
            ->prepend($this->defaultRules())
            ->rules($this->rules)
            ->append($this->updateRules)
            ->all($request, $model);
    }

    /**
     * @return Closure|array
     */
    protected function defaultRules(): Closure|array
    {
        return [];
    }
}
