<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Validation\Tests\Unit\Fields;

use Closure;
use Illuminate\Http\Request;
use LaravelJsonApi\Contracts\Schema\Field;
use LaravelJsonApi\Validation\Fields\IsValidated;

class TestField implements Field, IsValidated
{
    public function name(): string
    {
        // TODO: Implement name() method.
    }

    public function isSparseField(): bool
    {
        // TODO: Implement isSparseField() method.
    }

    public function rulesForCreation(?Request $request): Closure|array|null
    {
        // TODO: Implement rulesForCreate() method.
    }

    public function rulesForUpdate(?Request $request, object $model): Closure|array|null
    {
        // TODO: Implement rulesForUpdate() method.
    }
}
