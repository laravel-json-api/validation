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
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

final class KeyedSetOfRules
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
     * @param mixed ...$args
     * @return array<string, mixed>
     */
    public function __invoke(mixed ...$args): array
    {
        $rules = [];

        foreach ($this->resolve($this->prepend, $args) as $key => $value) {
            $rules[$key] = Arr::wrap($value);
        }

        foreach ($this->resolve($this->rules, $args) as $key => $value) {
            $rules[$key] = [
                ...$rules[$key] ?? [],
                ...Arr::wrap($value),
            ];
        }

        foreach ($this->resolve($this->append, $args) as $key => $value) {
            $rules[$key] = [
                ...$rules[$key] ?? [],
                ...Arr::wrap($value),
            ];
        }

        $base = Arr::pull($rules, '.') ?? [];
        $keys = $this->mustAddArrayRule($base) ? $this->keys($rules) : [];

        if (count($keys) > 0) {
            $base = ListOfRules::make()
                ->defaults('array:' . implode(',', $keys))
                ->rules($base)
                ->all();
        }

        ksort($rules);

        return count($base) === 0 ? $rules : ['.' => $base, ...$rules];
    }

    /**
     * @param mixed ...$args
     * @return array<string, mixed>
     */
    public function all(mixed ...$args): array
    {
        return $this(...$args);
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
     * @param array<int, mixed> $args
     * @return array
     */
    private function resolve(Closure|array $value, array $args): array
    {
        if ($value instanceof Closure){
            $value = $value(...$args) ?? [];
        }

        assert(is_array($value), 'Expecting closure to return an array or null.');

        return $value;
    }

    /**
     * @param array<int, mixed> $rules
     * @return bool
     */
    private function mustAddArrayRule(array $rules): bool
    {
        foreach ($rules as $rule) {
            if (is_string($rule) && Str::startsWith($rule, 'array')) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<string, array<int, mixed>> $rules
     * @return array
     */
    private function keys(array $rules): array
    {
        $keys = [];

        foreach ($rules as $key => $value) {
            if ($key !== '.' && !Str::startsWith($key, '*')) {
                $keys[] = Str::before($key, '.');
            }
        }

        $keys = array_values(array_unique($keys));

        ksort($keys);

        return $keys;
    }
}
