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
use LaravelJsonApi\Validation\Fields\FieldRuleMap;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FieldRuleMapTest extends TestCase
{
    /**
     * @var Request
     */
    private Request $request;

    /**
     * @var object
     */
    private object $model;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->request = $this->createMock(Request::class);
        $this->model = new \stdClass();
    }

    /**
     * @return void
     */
    public function testCreationRules(): void
    {
        $rule2 = function ($r): array {
            $this->assertSame($this->request, $r);
            return ['rule2'];
        };

        $map = new FieldRuleMap(
            $this->createField('foo'),
            $this->createCreationField('bar', ['rule1']),
            $this->createCreationField('baz', $rule2),
            $this->createCreationField('bat', ['rule3']),
            $this->createField('foobar'),
            $this->createCreationField('bazbat', fn() => null),
            $this->createCreationField('blah!', []),
        );

        $expected = [
            '.' => ['array:bar,bat,baz'],
            'bar' => ['rule1'],
            'bat' => ['rule3'],
            'baz' => ['rule2'],
        ];

        $actual = $map->creation($this->request);

        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     */
    public function testUpdateRules(): void
    {
        $rule3 = function ($r, $m): array {
            $this->assertSame($this->request, $r);
            $this->assertSame($this->model, $m);
            return ['rule3'];
        };

        $map = new FieldRuleMap(
            $this->createField('foo'),
            $this->createUpdateField('bar', ['rule1']),
            $this->createUpdateField('baz', ['rule2']),
            $this->createUpdateField('bat', $rule3),
            $this->createField('foobar'),
            $this->createUpdateField('bazbat', null),
            $this->createUpdateField('blah!', fn () => []),
        );

        $expected = [
            '.' => ['array:bar,bat,baz'],
            'bar' => ['rule1'],
            'bat' => ['rule3'],
            'baz' => ['rule2'],
        ];

        $actual = $map->update($this->request, $this->model);

        $this->assertSame($expected, $actual);
    }

    /**
     * @param string $name
     * @return MockObject&Field
     */
    private function createField(string $name): Field&MockObject
    {
        $mock = $this->createMock(Field::class);
        $mock->method('name')->willReturn($name);

        return $mock;
    }

    /**
     * @param string $name
     * @param Closure|array|null $rules
     * @return MockObject&TestField
     */
    private function createCreationField(string $name, Closure|array|null $rules): TestField&MockObject
    {
        $mock = $this->createMock(TestField::class);
        $mock->method('name')->willReturn($name);

        $mock
            ->expects($this->once())
            ->method('rulesForCreation')
            ->with($this->identicalTo($this->request))
            ->willReturn($rules);

        $mock
            ->expects($this->never())
            ->method('rulesForUpdate');

        return $mock;
    }

    /**
     * @param string $name
     * @param Closure|array|null $rules
     * @return MockObject&TestField
     */
    private function createUpdateField(string $name, Closure|array|null $rules): TestField&MockObject
    {
        $mock = $this->createMock(TestField::class);
        $mock->method('name')->willReturn($name);

        $mock
            ->expects($this->once())
            ->method('rulesForUpdate')
            ->with($this->identicalTo($this->request), $this->identicalTo($this->model))
            ->willReturn($rules);

        $mock
            ->expects($this->never())
            ->method('rulesForCreation');

        return $mock;
    }
}