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

use LaravelJsonApi\Contracts\Schema\Schema;
use LaravelJsonApi\Validation\Rules\AllowedIncludePaths;
use PHPUnit\Framework\TestCase;

class AllowedIncludePathsTest extends TestCase
{

    public function test(): void
    {
        $rule = new AllowedIncludePaths(['foo', 'bar']);

        $this->assertTrue($rule->passes('include', 'foo,bar'));
        $this->assertFalse($rule->passes('include', 'foo,baz'));
    }

    public function testWithMethods(): void
    {
        $rule = (new AllowedIncludePaths())
            ->allow('foo', 'bar', 'id')
            ->forget('id');

        $this->assertTrue($rule->passes('include', 'foo,bar'));
        $this->assertFalse($rule->passes('include', 'foo,id'));
    }

    public function testSchema(): void
    {
        $schema = $this->createMock(Schema::class);
        $schema->method('includePaths')->willReturn(['author', 'comments']);

        $this->assertEquals(
            new AllowedIncludePaths(['author', 'comments']),
            AllowedIncludePaths::make($schema)
        );
    }

}
