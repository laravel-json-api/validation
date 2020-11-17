<?php
/*
 * Copyright 2020 Cloud Creativity Limited
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

class JsonApiValidation
{

    /**
     * The namespace for translations.
     *
     * @var string
     */
    public static string $translationNamespace = 'jsonapi-validation';

    /**
     * Indicates if Laravel validator failed data is added to JSON API error objects.
     *
     * @var bool
     */
    public static bool $validationFailures = false;

    /**
     * Set the translation namespace.
     *
     * @param string $namespace
     * @return static
     */
    public static function useTranslationNamespace(string $namespace): self
    {
        if (empty($namespace)) {
            throw new \InvalidArgumentException('Expecting a non-empty string.');
        }

        self::$translationNamespace = $namespace;

        return new self();
    }

    /**
     * Set JSON API errors for validators to show failed data in their meta member.
     *
     * @return static
     */
    public static function showValidatorFailures(): self
    {
        self::$validationFailures = true;

        return new self();
    }
}
