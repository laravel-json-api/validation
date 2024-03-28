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
use LaravelJsonApi\Core\Query\Input\QueryMany;
use LaravelJsonApi\Core\Values\ResourceType;
use LaravelJsonApi\Validation\Filters\FilterRuleMap;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FilterRuleMapTest extends TestCase
{
    /**
     * @var Request
     */
    private Request $request;

    /**
     * @var Query
     */
    private Query $query;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->request = $this->createMock(Request::class);
        $this->query = new QueryMany(new ResourceType('posts'));
    }

    /**
     * @return void
     */
    public function test(): void
    {
        $rule2 = function ($r, $q): array {
            $this->assertSame($this->request, $r);
            $this->assertSame($this->query, $q);
            return ['rule2'];
        };

        $map = new FilterRuleMap(
            $this->createFilter('foo'),
            $this->createValidatedFilter('bar', ['rule1']),
            $this->createValidatedFilter('baz', $rule2),
            $this->createValidatedFilter('bat', ['rule3']),
            $this->createFilter('foobar'),
            $this->createValidatedFilter('bazbat', fn() => null),
            $this->createValidatedFilter('blah!', []),
        );

        $expected = [
            '.' => ['array:bar,bat,baz'],
            'bar' => ['rule1'],
            'bat' => ['rule3'],
            'baz' => ['rule2'],
        ];

        $actual = $map->rules($this->request, $this->query);

        $this->assertSame($expected, $actual);
    }

    /**
     * @param string $name
     * @return MockObject&Filter
     */
    private function createFilter(string $name): Filter&MockObject
    {
        $mock = $this->createMock(Filter::class);
        $mock->method('key')->willReturn($name);

        return $mock;
    }

    /**
     * @param string $name
     * @param Closure|array|null $rules
     * @return MockObject&TestFilter
     */
    private function createValidatedFilter(string $name, Closure|array|null $rules): TestFilter&MockObject
    {
        $mock = $this->createMock(TestFilter::class);
        $mock->method('key')->willReturn($name);

        $mock
            ->expects($this->once())
            ->method('validationRules')
            ->with($this->identicalTo($this->request), $this->identicalTo($this->query))
            ->willReturn($rules);

        return $mock;
    }
}