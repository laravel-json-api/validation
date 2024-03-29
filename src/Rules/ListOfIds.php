<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Validation\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use LaravelJsonApi\Contracts\Schema\ID;
use LaravelJsonApi\Validation\JsonApiValidation;

final readonly class ListOfIds implements ValidationRule
{
    /**
     * ListOfIds constructor.
     *
     * @param ID $id
     * @param string $delimiter
     */
    public function __construct(private ID $id, private string $delimiter = '')
    {
    }

    /**
     * @inheritDoc
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $valid = match (true) {
            is_string($value) && strlen($this->delimiter) > 0 => $this->id->match($value, $this->delimiter),
            is_array($value) => $this->id->matchAll($value),
            default => false,
        };

        if ($valid === false) {
            $fail(JsonApiValidation::translationKeyForRule($this))->translate();
        }
    }
}