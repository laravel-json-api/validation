<?php
/*
 * Copyright 2022 Cloud Creativity Limited
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

namespace LaravelJsonApi\Validation\Rules;

use Illuminate\Contracts\Validation\Rule;
use LaravelJsonApi\Validation\JsonApiValidation;
use function filter_var;
use function is_bool;

class JsonBoolean implements Rule
{
    /**
     * @var bool
     */
    private bool $asString = false;

    /**
     * Mark the rule as validating a string boolean (e.g. from query parameters).
     *
     * @return $this
     */
    public function asString(): self
    {
        $this->asString = true;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function passes($attribute, $value): bool
    {
        if (true === $this->asString) {
            return is_bool(filter_var(
                $value,
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE,
            ));
        }

        return is_bool($value);
    }

    /**
     * @inheritDoc
     */
    public function message(): string
    {
        $key = $this->asString ?
            JsonApiValidation::qualifyTranslationKey('boolean_string') :
            JsonApiValidation::translationKeyForRule($this);

        return trans($key);
    }
}
