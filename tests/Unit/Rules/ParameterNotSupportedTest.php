<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Validation\Tests\Unit\Rules;

use LaravelJsonApi\Validation\Rules\ParameterNotSupported;
use PHPUnit\Framework\TestCase;

class ParameterNotSupportedTest extends TestCase
{

    public function test(): void
    {
        $rule = new ParameterNotSupported('include');

        $this->assertFalse($rule->passes('include', 'foo,bar'));
    }
}
