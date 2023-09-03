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