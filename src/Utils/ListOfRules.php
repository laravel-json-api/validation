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

class ListOfRules
{
    /**
     * @var Closure|array
     */
    private Closure|array $defaults = [];

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
        $rules = [
            ...$this->resolve($this->rules, $request, $model),
            ...$this->resolve($this->append, $request, $model),
        ];

        $defaults = $this->resolve($this->defaults, $request, $model);

        if (empty($defaults)) {
            return $rules;
        }

        $startAt = array_search('required', $rules, true);
        $startAt = ($startAt === false) ? array_search('nullable', $rules, true) : $startAt;

        if ($startAt === false) {
            return [...$defaults, ...$rules];
        }

        return [
            ...array_slice($rules, 0, $startAt + 1),
            ...$defaults,
            ...array_slice($rules, $startAt + 1),
        ];
    }

    /**
     * Set rules that must always be set.
     *
     * @param mixed ...$args
     * @return $this
     */
    public function defaults(mixed ...$args): self
    {
        $this->defaults = $this->parse($args);

        return $this;
    }

    /**
     * @param mixed ...$args
     * @return $this
     */
    public function rules(mixed ...$args): self
    {
        $this->rules = $this->parse($args);

        return $this;
    }

    /**
     * @param mixed ...$args
     * @return $this
     */
    public function append(mixed ...$args): self
    {
        $this->append = $this->parse($args);

        return $this;
    }

    /**
     * @param mixed $args
     * @return Closure|array
     */
    private function parse(array $args): Closure|array
    {
        if (count($args) === 1 && (is_array($args[0]) || $args[0] instanceof Closure)) {
            return $args[0];
        }

        return $args;
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

        return array_values((array) array_filter($value, static fn($item): bool => $item !== null));
    }
}
