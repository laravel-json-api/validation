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
use LaravelJsonApi\Contracts\Schema\ID;
use LaravelJsonApi\Contracts\Schema\Schema;
use LaravelJsonApi\Validation\JsonApiValidation;
use function is_string;

final readonly class ClientId implements Rule
{
    /**
     * @var ID
     */
    private ID $id;

    /**
     * ClientId constructor.
     *
     * @param Schema|ID $schemaOrId
     */
    public function __construct(Schema|ID $schemaOrId)
    {
        $this->id = ($schemaOrId instanceof Schema) ? $schemaOrId->id() : $schemaOrId;
    }

    /**
     * @inheritDoc
     */
    public function passes($attribute, $value)
    {
        if (is_string($value)) {
            return $this->id->match($value);
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function message()
    {
        return trans(JsonApiValidation::translationKeyForRule($this));
    }
}
