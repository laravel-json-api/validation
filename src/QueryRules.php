<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
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
use LaravelJsonApi\Validation\Rules\ParameterNotSupported;

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
        $isOne = $query->isOne();

        if ($fieldName = $query->getFieldName()) {
            $relation = $this->schemas
                ->schemaFor($query->type)
                ->relationship($fieldName);

            $related = $relation->filters();
            $isOne = $relation->toOne();
        }

        if ($isOne) {
            return AllowedFilterParameters::forOne(
                ...$this->schema->query()->filters(),
                ...$related,
            );
        }

        return AllowedFilterParameters::forMany(
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

    /**
     * @return ParameterNotSupported
     */
    public function notSupported(): ParameterNotSupported
    {
        return new ParameterNotSupported();
    }
}
