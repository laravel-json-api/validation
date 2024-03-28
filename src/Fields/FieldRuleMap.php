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
use Generator;
use Illuminate\Http\Request;
use LaravelJsonApi\Contracts\Schema\Field;

final readonly class FieldRuleMap
{
    /**
     * @var array<Field>
     */
    private array $fields;

    /**
     * Fluent constructor.
     *
     * @param array<Field> $fields
     * @return self
     */
    public static function make(array $fields): self
    {
        return new self(...$fields);
    }

    /**
     * FieldRuleMap constructor.
     *
     * @param Field ...$fields
     */
    public function __construct(Field ...$fields)
    {
        $this->fields = $fields;
    }

    /**
     * Get creation rules.
     *
     * @param Request|null $request
     * @return array<string, mixed>
     */
    public function creation(?Request $request): array
    {
        return $this->all(
            static fn(IsValidated $field): Closure|array|null => $field->rulesForCreation($request),
            $request,
        );
    }

    /**
     * Get update rules.
     *
     * @param Request|null $request
     * @param object $model
     * @return array<string, mixed>
     */
    public function update(?Request $request, object $model): array
    {
        return $this->all(
            static fn(IsValidated $field): Closure|array|null => $field->rulesForUpdate($request, $model),
            $request,
            $model,
        );
    }

    /**
     * @param Closure(IsValidated): array $fn
     * @return array<string, mixed>
     */
    private function all(Closure $fn, mixed ...$args): array
    {
        $rules = iterator_to_array($this->cursor($fn, $args));

        ksort($rules);

        $names = array_keys($rules);

        return count($names) > 0 ? [
            '.' => ['array:' . implode(',', $names)],
            ...$rules,
        ] : $rules;
    }

    /**
     * @param Closure(IsValidated): array $fn
     * @param array<int, mixed> $args
     * @return Generator<string, array>
     */
    private function cursor(Closure $fn, array $args): Generator
    {
        foreach ($this->fields as $field) {
            if (!$field instanceof IsValidated) {
                continue;
            }

            $rules = $fn($field) ?? [];

            if ($rules instanceof Closure) {
                $rules = $rules(...$args) ?? [];
            }

            if (count($rules) > 0) {
                yield $field->name() => $rules;
            }
        }
    }
}