<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Validation\Pagination;

use Closure;
use Illuminate\Http\Request;
use LaravelJsonApi\Core\Query\Input\Query;

interface IsValidated
{
    /**
     * Get the validation rules for the paginator.
     *
     * @param Request|null $request
     * @param Query $query
     * @return Closure|array|null
     */
    public function validationRules(?Request $request, Query $query): Closure|array|null;
}
