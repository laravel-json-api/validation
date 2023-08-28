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

use LaravelJsonApi\Contracts\Schema\Container as SchemaContainer;
use LaravelJsonApi\Contracts\Schema\Schema;
use LaravelJsonApi\Core\Query\Input\Query;
use LaravelJsonApi\Validation\Rules\AllowedCountableFields;
use LaravelJsonApi\Validation\Rules\AllowedFieldSets;
use LaravelJsonApi\Validation\Rules\AllowedFilterParameters;
use LaravelJsonApi\Validation\Rules\AllowedIncludePaths;
use LaravelJsonApi\Validation\Rules\AllowedPageParameters;
use LaravelJsonApi\Validation\Rules\AllowedSortParameters;

class QueryRules
{
    /**
     * QueryRules constructor
     *
     * @param SchemaContainer $schemas
     * @param Schema $schema
     */
    public function __construct(
        private readonly SchemaContainer $schemas,
        private readonly Schema $schema,
    ) {
    }

    /**
     * @return AllowedFieldSets
     */
    public function fieldSets(): AllowedFieldSets
    {
        return AllowedFieldSets::make($this->schemas);
    }

    /**
     * @param Query $query
     * @return AllowedFilterParameters
     */
    public function filters(Query $query): AllowedFilterParameters
    {
        $related = [];

        if ($fieldName = $query->getFieldName()) {
            $related = $this->schemas
                ->schemaFor($query->type)
                ->relationship($fieldName)
                ->filters();
        }

        return AllowedFilterParameters::forFilters(
            ...$this->schema->query()->filters(),
            ...$related,
        );
    }

    /**
     * @return AllowedIncludePaths
     */
    public function includePaths(): AllowedIncludePaths
    {
        return AllowedIncludePaths::make($this->schema);
    }

    /**
     * @return AllowedPageParameters
     */
    public function page(): AllowedPageParameters
    {
        return AllowedPageParameters::make($this->schema);
    }

    /**
     * @return AllowedSortParameters
     */
    public function sort(): AllowedSortParameters
    {
        return AllowedSortParameters::make($this->schema);
    }

    /**
     * @return AllowedCountableFields
     */
    public function countable(): AllowedCountableFields
    {
        return AllowedCountableFields::make($this->schema);
    }
}
