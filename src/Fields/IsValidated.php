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

namespace LaravelJsonApi\Validation\Fields;

use Closure;
use Illuminate\Http\Request;

interface IsValidated
{
    /**
     * Get validation rules for the field when the resource is being created.
     *
     * If the method returns an empty array or null, this MUST be interpreted as
     * the field not being validated.
     *
     * @param Request|null $request
     * @return Closure|array|null
     */
    public function rulesForCreate(?Request $request): Closure|array|null;

    /**
     * Get validation rules for the field when the resource is being updated.
     *
     * If the method returns an empty array or null, this MUST be interpreted as
     * the field not being validated.
     *
     * @param Request|null $request
     * @param object $model
     * @return Closure|array|null
     */
    public function rulesForUpdate(?Request $request, object $model): Closure|array|null;
}
