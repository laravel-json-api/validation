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
use Illuminate\Http\Request;
use LaravelJsonApi\Validation\Utils\KeyedSetOfRules;

trait ValidatedWithKeyedSetOfRules
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
        $rules = KeyedSetOfRules::make()
            ->prepend($this->defaultRules())
            ->rules($this->rules)
            ->append($this->creationRules);

        return $rules($request);
    }

    /**
     * @param Request|null $request
     * @param object $model
     * @return array
     */
    public function rulesForUpdate(?Request $request, object $model): array
    {
        $rules = KeyedSetOfRules::make()
            ->prepend($this->defaultRules())
            ->rules($this->rules)
            ->append($this->updateRules);

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
