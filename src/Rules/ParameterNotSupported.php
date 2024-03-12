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

use Illuminate\Contracts\Validation\Rule;
use LaravelJsonApi\Validation\JsonApiValidation;

class ParameterNotSupported implements Rule
{
    /**
     * ParameterNotSupported constructor.
     *
     * @param string|null $name
     */
    public function __construct(private ?string $name = null)
    {
    }

    /**
     * @inheritDoc
     */
    public function passes($attribute, $value)
    {
        if ($this->name === null) {
            $this->name = $attribute;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function message()
    {
        return trans(JsonApiValidation::translationKeyForRule($this), [
            'name' => $this->name,
        ]);
    }

}
