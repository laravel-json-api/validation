<?php
/*
 * Copyright 2021 Cloud Creativity Limited
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
use Illuminate\Support\Str;
use LaravelJsonApi\Contracts\Schema\Schema;
use LaravelJsonApi\Validation\JsonApiValidation;
use function is_string;

class ClientId implements Rule
{

    /**
     * @var Schema
     */
    private Schema $schema;

    /**
     * ClientId constructor.
     *
     * @param Schema $schema
     */
    public function __construct(Schema $schema)
    {
        $this->schema = $schema;
    }

    /**
     * @inheritDoc
     */
    public function passes($attribute, $value)
    {
        if (is_string($value)) {
            return $this->schema->id()->match($value);
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function message()
    {
        $namespace = JsonApiValidation::$translationNamespace;
        $name = Str::snake(class_basename($this));

        return trans("{$namespace}::validation.{$name}");
    }

}