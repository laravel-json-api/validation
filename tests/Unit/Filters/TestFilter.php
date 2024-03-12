<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Validation\Tests\Unit\Filters;

use Closure;
use Illuminate\Http\Request;
use LaravelJsonApi\Contracts\Schema\Filter;
use LaravelJsonApi\Core\Query\Input\Query;
use LaravelJsonApi\Validation\Filters\IsValidated;

class TestFilter implements Filter, IsValidated
{
    public function key(): string
    {
        // TODO: Implement key() method.
    }

    public function validationRules(?Request $request, Query $query): Closure|array|null
    {
        // TODO: Implement validationRules() method.
    }

    public function isValidatedForOne(): bool
    {
        // TODO: Implement isValidatedForOne() method.
    }

    public function isValidatedForMany(): bool
    {
        // TODO: Implement isValidatedForMany() method.
    }
}
