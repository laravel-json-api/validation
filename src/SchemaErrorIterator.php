<?php
/*
 * Copyright 2023 Cloud Creativity Limited
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

namespace LaravelJsonApi\Validation;

use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use LaravelJsonApi\Contracts\Schema\Schema;
use LaravelJsonApi\Core\Document\Error;

class SchemaErrorIterator extends ErrorIterator
{

    /**
     * @var Schema
     */
    private Schema $schema;

    /**
     * SchemaErrorIterator constructor.
     *
     * @param Translator $translator
     * @param Schema $schema
     * @param ValidatorContract $validator
     */
    public function __construct(Translator $translator, Schema $schema, ValidatorContract $validator)
    {
        parent::__construct($translator, $validator);
        $this->schema = $schema;
    }

    /**
     * @param string $key
     * @param string $message
     * @param array $failed
     * @return Error
     */
    protected function createError(string $key, string $message, array $failed): Error
    {
        return $this->translator->invalidResource(
            SchemaSourcePointer::make($this->schema, $key)->withPrefix('/data')->toString(),
            $message,
            $failed
        );
    }

}
