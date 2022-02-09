<?php
/*
 * Copyright 2022 Cloud Creativity Limited
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

namespace LaravelJsonApi\Validation\Tests\Unit\Rules;

use LaravelJsonApi\Contracts\Pagination\Paginator;
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
        $schema->method('pagination')->willReturn($paginator);

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
