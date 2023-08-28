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

namespace LaravelJsonApi\Validation\Filters;

use Closure;
use Illuminate\Http\Request;
use LaravelJsonApi\Core\Query\Input\Query;

interface IsValidated
{
    /**
     * Get validation rules for the filter when the response will contain zero-to-one resources.
     *
     * If the method returns an empty array or null, this MUST be interpreted as
     * the filter not being validated.
     *
     * @param Request|null $request
     * @param Query $query
     * @return Closure|array|null
     */
    public function rulesForOne(?Request $request, Query $query): Closure|array|null;

    /**
     * Get validation rules for the filter when the response will contain zero-to-many resources.
     *
     *  If the method returns an empty array or null, this MUST be interpreted as
     *  the filter not being validated.
     *
     * @param Request|null $request
     * @param Query $query
     * @return Closure|array|null
     */
    public function rulesForMany(?Request $request, Query $query): Closure|array|null;
}
