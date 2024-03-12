<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Validation\Tests\Integration;

use LaravelJsonApi\Contracts\Implementations\Countable\CountableSchema;
use LaravelJsonApi\Contracts\Schema\Schema;

interface TestSchema extends Schema, CountableSchema
{
}
