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
use function is_float;
use function is_int;

class JsonNumber implements Rule
{
    /**
     * @var bool
     */
    private bool $onlyIntegers = false;

    /**
     * @return $this
     */
    public function onlyIntegers(): self
    {
        $this->onlyIntegers = true;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function passes($attribute, $value): bool
    {
        if (true === $this->onlyIntegers) {
            return is_int($value);
        }

        return is_int($value) || is_float($value);
    }

    /**
     * @inheritDoc
     */
    public function message(): string
    {
        $key = $this->onlyIntegers ? 'json_integer' : 'json_number';

        return  trans(JsonApiValidation::qualifyTranslationKey($key));
    }
}
