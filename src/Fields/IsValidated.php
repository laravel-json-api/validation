<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
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
    public function rulesForCreation(?Request $request): Closure|array|null;

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
