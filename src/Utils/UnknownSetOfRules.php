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

final class UnknownSetOfRules
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
     * @param mixed ...$args
     * @return array
     */
    public function __invoke(mixed ...$args): array
    {
        $defaults = match (true) {
            $this->defaults instanceof Closure => ($this->defaults)(...$args) ?? [],
            default => $this->defaults,
        };

        /**
         * If we have default rules, we can infer whether it is a keyed set or
         * list of rules from those defaults.
         */
        if (count($defaults) > 0) {
            return array_is_list($defaults) ? $this->toList(
                    args: $args,
                    defaults: $defaults,
                    rules: $this->rules,
                    append: $this->append,
                ) : $this->toKeyedSet(
                    args: $args,
                    defaults: $defaults,
                    rules: $this->rules,
                    append: $this->append,
                );
        }

        /**
         * Next we will attempt to infer whether it is a keyed set or list of
         * rules from the rules themselves.
         */
        $rules = match (true) {
            $this->rules instanceof Closure => ($this->rules)(...$args) ?? [],
            default => $this->rules,
        };

        if (count($rules) > 0) {
            return array_is_list($rules) ? $this->toList(
                args: $args,
                defaults: $defaults,
                rules: $rules,
                append: $this->append,
            ) : $this->toKeyedSet(
                args: $args,
                defaults: $defaults,
                rules: $rules,
                append: $this->append,
            );
        }

        /**
         * Finally we can infer the type from the appended rules.
         */
        $append = match (true) {
            $this->append instanceof Closure => ($this->append)(...$args) ?? [],
            default => $this->append,
        };

        if (count($append) === 0 || array_is_list($append)) {
            return $this->toList(
                args: $args,
                defaults: $defaults,
                rules: $rules,
                append: $append,
            );
        }

        return $this->toKeyedSet(
            args: $args,
            defaults: $defaults,
            rules: $rules,
            append: $append,
        );
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
     * @param mixed ...$args
     * @return $this
     */
    public function prepend(mixed ...$args): self
    {
        return $this->defaults(...$args);
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
        return match (true) {
            count($args) === 1 && $args[0] instanceof Closure,
            count($args) === 1 && is_array($args[0]) && !array_is_list($args[0]) => $args[0],
            count($args) === 1 => Arr::wrap($args[0]),
            default => $args,
        };
    }

    /**
     * @param array<int, mixed> $args
     * @param array $defaults
     * @param Closure|array $rules
     * @param Closure|array $append
     * @return array
     */
    private function toList(
        array $args,
        array $defaults,
        Closure|array $rules,
        Closure|array $append,
    ): array
    {
        return ListOfRules::make()
            ->defaults($defaults)
            ->rules(...Arr::wrap($rules))
            ->append(...Arr::wrap($append))
            ->all(...$args);
    }

    /**
     * @param array<int, mixed> $args
     * @param array $defaults
     * @param Closure|array $rules
     * @param Closure|array $append
     * @return array
     */
    private function toKeyedSet(
        array $args,
        array $defaults,
        Closure|array $rules,
        Closure|array $append
    ): array
    {
        return KeyedSetOfRules::make()
            ->prepend($defaults)
            ->rules($rules)
            ->append($append)
            ->all(...$args);
    }
}