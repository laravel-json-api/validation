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

use LaravelJsonApi\Contracts\Schema\Query;
use LaravelJsonApi\Contracts\Schema\Schema;
use LaravelJsonApi\Validation\Rules\AllowedSortParameters;
use PHPUnit\Framework\TestCase;

class AllowedSortParametersTest extends TestCase
{

    public function test(): void
    {
        $rule = new AllowedSortParameters(['foo', 'bar']);

        $this->assertTrue($rule->passes('sort', 'foo,-bar'));
        $this->assertTrue($rule->passes('sort', '+foo,bar'));
        $this->assertFalse($rule->passes('sort', 'foo,baz'));
        $this->assertFalse($rule->passes('sort', 'foobar'));
    }

    public function testWithMethods(): void
    {
        $rule = (new AllowedSortParameters())
            ->allow('foo', 'bar', 'foobar')
            ->forget('foobar');

        $this->assertTrue($rule->passes('sort', 'foo,-bar'));
        $this->assertFalse($rule->passes('sort', 'foo,baz'));
        $this->assertFalse($rule->passes('sort', 'foobar'));
    }

    public function testSchema(): void
    {
        $schema = $this->createMock(Schema::class);
        $schema->method('query')->willReturn($query = $this->createMock(Query::class));
        $query->method('sortFields')->willReturn(['title', 'createdAt']);

        $this->assertEquals(
            new AllowedSortParameters(['title', 'createdAt']),
            $actual = AllowedSortParameters::make($schema)
        );

        $this->assertTrue($actual->passes('sort', 'createdAt,title'));
        $this->assertFalse($actual->passes('sort', 'title,foo'));
    }

}
