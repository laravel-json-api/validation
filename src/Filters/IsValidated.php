<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Validation\Filters;

use Closure;
use Illuminate\Http\Request;
use LaravelJsonApi\Core\Query\Input\Query;

interface IsValidated
{
    /**
     * Get validation rules for the filter.
     *
     * If the method returns an empty array or null, this MUST be interpreted as
     * the filter not being validated.
     *
     * @param Request|null $request
     * @param Query $query
     * @return Closure|array|null
     */
    public function validationRules(?Request $request, Query $query): Closure|array|null;

    /**
     * Is the filter validated when a query will return zero-to-one resources?
     *
     * @return bool
     */
    public function isValidatedForOne(): bool;

    /**
     * Is the filter validated when a query will return zero-to-many resources?
     *
     * @return bool
     */
    public function isValidatedForMany(): bool;
}
