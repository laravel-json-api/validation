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

use Illuminate\Contracts\Validation\Validator;
use LaravelJsonApi\Contracts\Schema\Schema;

class Factory
{

    /**
     * @var Translator
     */
    private Translator $translator;

    /**
     * Factory constructor.
     *
     * @param Translator $translator
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Create JSON API errors for a Laravel validator.
     *
     * @param Validator $validator
     * @return ValidatorErrorIterator
     */
    public function createErrors(Validator $validator): ValidatorErrorIterator
    {
        return new ValidatorErrorIterator(
            $this->translator,
            $validator
        );
    }

    /**
     * Create JSON API errors for a resource validator.
     *
     * @param Schema $schema
     * @param Validator $validator
     * @return ErrorIterator
     */
    public function createErrorsForResource(Schema $schema, Validator $validator): ErrorIterator
    {
        return new SchemaErrorIterator(
            $this->translator,
            $schema,
            $validator
        );
    }

    /**
     * Create JSON API errors for a query parameter validator.
     *
     * @param Validator $validator
     * @return ErrorIterator
     */
    public function createErrorsForQuery(Validator $validator): ErrorIterator
    {
        return new QueryErrorIterator($this->translator, $validator);
    }

    /**
     * Create JSON API errors for a delete resource validator.
     *
     * @param Validator $validator
     * @return ErrorIterator
     */
    public function createErrorsForDeleteResource(Validator $validator): ErrorIterator
    {
        return new DeleteErrorIterator($this->translator, $validator);
    }
}
