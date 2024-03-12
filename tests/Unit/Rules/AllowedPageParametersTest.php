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

use LaravelJsonApi\Contracts\Pagination\Paginator;
use LaravelJsonApi\Contracts\Schema\Query;
use LaravelJsonApi\Contracts\Schema\Schema;
use LaravelJsonApi\Validation\Rules\AllowedPageParameters;
use PHPUnit\Framework\TestCase;

class AllowedPageParametersTest extends TestCase
{

    public function test(): void
    {
        $rule = new AllowedPageParameters(['foo', 'bar']);

        $this->assertTrue($rule->passes('page', ['foo' => 'foobar', 'bar' => 'bazbat']));
        $this->assertFalse($rule->passes('page', ['foo' => 'foobar', 'baz' => 'bazbat']));
    }

    public function testWithMethods(): void
    {
        $rule = (new AllowedPageParameters())
            ->allow('foo', 'bar', 'foobar')
            ->forget('foobar');

        $this->assertTrue($rule->passes('page', ['foo' => 'foobar', 'bar' => 'bazbat']));
        $this->assertFalse($rule->passes('page', ['foo' => 'foobar', 'baz' => 'bazbat']));
        $this->assertFalse($rule->passes('page', ['foobar' => '1']));
    }

    public function testSchema(): void
    {
        $paginator = $this->createMock(Paginator::class);
        $paginator->method('keys')->willReturn(['number', 'size']);

        $schema = $this->createMock(Schema::class);
        $schema->method('query')->willReturn($query = $this->createMock(Query::class));
        $query->method('pagination')->willReturn($paginator);

        $this->assertEquals(
            new AllowedPageParameters(['number', 'size']),
            $actual = AllowedPageParameters::make($schema)
        );

        $this->assertTrue($actual->passes('page', ['number' => 1, 'size' => 10]));
    }

    public function testSchemaWithoutPagination(): void
    {
        $schema = $this->createMock(Schema::class);
        $schema->method('pagination')->willReturn(null);

        $this->assertEquals(
            new AllowedPageParameters([]),
            $actual = AllowedPageParameters::make($schema)
        );

        $this->assertFalse($actual->passes('page', ['number' => 1, 'size' => 10]));
    }

}
