<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Validation\Utils;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class KeyedSetOfRules
{
    /**
     * @var Closure|array
     */
    private Closure|array $prepend = [];

    /**
     * @var Closure|array
     */
    private Closure|array $rules = [];

    /**
     * @var Closure|array
     */
    private Closure|array $append = [];

    /**
     * Fluent constructor.
     *
     * @return self
     */
    public static function make(): self
    {
        return new self();
    }

    /**
     * Convert the object to a set of rules.
     *
     * @param Request|null $request
     * @param object|null $model
     * @return array
     */
    public function __invoke(?Request $request, object $model = null): array
    {
        $rules = $this->resolve($this->prepend, $request, $model);

        foreach ($this->resolve($this->rules, $request, $model) as $key => $value) {
            $rules[$key] = [
                ...Arr::wrap($rules[$key] ?? null),
                ...Arr::wrap($value),
            ];
        }

        foreach ($this->resolve($this->append, $request, $model) as $key => $value) {
            $rules[$key] = [
                ...Arr::wrap($rules[$key] ?? null),
                ...Arr::wrap($value),
            ];
        }

        return $rules;
    }

    /**
     * @param Closure|array|null $rules
     * @return $this
     */
    public function prepend(Closure|array|null $rules): self
    {
        $this->prepend = $rules ?? [];

        return $this;
    }

    /**
     * @param Closure|array|null $rules
     * @return $this
     */
    public function rules(Closure|array|null $rules): self
    {
        $this->rules = $rules ?? [];

        return $this;
    }

    /**
     * @param Closure|array|null $rules
     * @return $this
     */
    public function append(Closure|array|null $rules): self
    {
        $this->append = $rules ?? [];

        return $this;
    }

    /**
     * @param Closure|array $value
     * @param Request|null $request
     * @param object|null $model
     * @return array
     */
    private function resolve(Closure|array $value, ?Request $request, ?object $model): array
    {
        if ($value instanceof Closure){
            $value = $value($request, $model) ?? [];
        }

        assert(is_array($value), 'Expecting closure to return an array or null.');

        return $value;
    }
}
