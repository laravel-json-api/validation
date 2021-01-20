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

use Illuminate\Support\Arr;
use LaravelJsonApi\Validation\Rules\AllowedFieldSets;
use LaravelJsonApi\Validation\Rules\AllowedFilterParameters;
use LaravelJsonApi\Validation\Rules\AllowedIncludePaths;
use LaravelJsonApi\Validation\Rules\AllowedPageParameters;
use LaravelJsonApi\Validation\Rules\AllowedSortParameters;
use LaravelJsonApi\Validation\Rules\ClientId;
use LaravelJsonApi\Validation\Rules\DateTimeIso8601;
use LaravelJsonApi\Validation\Rules\HasMany;
use LaravelJsonApi\Validation\Rules\HasOne;
use LaravelJsonApi\Validation\Rules\ParameterNotSupported;
use LaravelJsonApi\Core\Facades\JsonApi;
use function is_null;

class Rule
{

    /**
     * Get a validation rule for a client-generated ID.
     *
     * @return ClientId
     */
    public static function clientId(): ClientId
    {
        return new ClientId(
            JsonApi::route()->schema()
        );
    }

    /**
     * Get a date time ISO8601 validation rule instance.
     *
     * @return DateTimeIso8601
     */
    public static function dateTime(): DateTimeIso8601
    {
        return new DateTimeIso8601();
    }

    /**
     * Get a sparse field sets constraint builder instance.
     *
     * @param array|null $allowed
     * @return AllowedFieldSets
     */
    public static function fieldSets(array $allowed = null): AllowedFieldSets
    {
        if (is_null($allowed)) {
            return AllowedFieldSets::make(
                JsonApi::server()->schemas()
            );
        }

        return new AllowedFieldSets($allowed);
    }

    /**
     * Get a filter parameter constraint builder instance.
     *
     * @param string|string[]|null $allowed
     * @return AllowedFilterParameters
     */
    public static function filter($allowed = null): AllowedFilterParameters
    {
        if (!is_null($allowed)) {
            return new AllowedFilterParameters(
                Arr::wrap($allowed)
            );
        }

        $route = JsonApi::route();

        if ($route->hasRelation()) {
            return AllowedFilterParameters::forFilters(
                ...$route->inverse()->filters(),
                ...$route->relation()->filters()
            );
        }

        return AllowedFilterParameters::make(
            $route->schema()
        );
    }

    /**
     * Get an include paths constraint builder instance.
     *
     * @param string|string[]|null $allowed
     * @return AllowedIncludePaths
     */
    public static function includePaths($allowed = null): AllowedIncludePaths
    {
        if (!is_null($allowed)) {
            return new AllowedIncludePaths(
                Arr::wrap($allowed)
            );
        }

        $route = JsonApi::route();

        return AllowedIncludePaths::make(
            $route->hasRelation() ? $route->inverse() : $route->schema()
        );
    }

    /**
     * Get a not supported parameter rule instance.
     *
     * @param string|null $name
     * @return ParameterNotSupported
     */
    public static function notSupported(string $name = null): ParameterNotSupported
    {
        return new ParameterNotSupported($name);
    }

    /**
     * Get a page parameter constraint builder instance.
     *
     * @param string|string[]|null $allowed
     * @return AllowedPageParameters
     */
    public static function page($allowed = null): AllowedPageParameters
    {
        if (!is_null($allowed)) {
            return new AllowedPageParameters(
                Arr::wrap($allowed)
            );
        }

        $route = JsonApi::route();

        return AllowedPageParameters::make(
            $route->hasRelation() ? $route->inverse() : $route->schema()
        );
    }

    /**
     * Get a sort parameter constraint builder instance.
     *
     * @param string|string[]|null $allowed
     * @return AllowedSortParameters
     */
    public static function sort($allowed = null): AllowedSortParameters
    {
        if (!is_null($allowed)) {
            return new AllowedSortParameters(
                Arr::wrap($allowed)
            );
        }

        $route = JsonApi::route();

        return AllowedSortParameters::make(
            $route->hasRelation() ? $route->inverse() : $route->schema()
        );
    }

    /**
     * Get a validation rule instance for a to-many relation.
     *
     * @return HasMany
     */
    public static function toMany(): HasMany
    {
        return new HasMany(
            JsonApi::route()->schema()
        );
    }

    /**
     * Get a validation rule instance for a to-many relation.
     *
     * @return HasOne
     */
    public static function toOne(): HasOne
    {
        return new HasOne(
            JsonApi::route()->schema()
        );
    }
}
