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

use LaravelJsonApi\Validation\Rules\JsonNumber;
use PHPUnit\Framework\TestCase;

class JsonNumberTest extends TestCase
{

    /**
     * @return array[]
     */
    public static function valueProvider(): array
    {
        return [
            'int1' => [0, true],
            'int2' => [99, true],
            'int3' => [-1, true],
            'int4' => [-0, true],
            'float1' => [0.0, true],
            'float2' => [9.9, true],
            'float3' => [-9.9, true],
            'float4' => [-0.0, true],
            'string int1' => ['0', false],
            'string int2' => ['1', false],
            'string float1' => ['0.0', false],
            'string float2' => ['9.9', false],
            'boolean1' => [true, false],
            'boolean2' => [false, false],
            'object' => [(object) ['foo' => 'bar'], false],
            'array' => [['foo', 'bar'], false],
            'null' => [null, false],
        ];
    }

    /**
     * @param $value
     * @param bool $expected
     * @return void
     * @dataProvider valueProvider
     */
    public function test($value, bool $expected): void
    {
        $rule = new JsonNumber();
        $this->assertSame($expected, $rule->passes('value', $value));
    }

    /**
     * @return array
     */
    public static function integerProvider(): array
    {
        return [
            'integer1' => [0],
            'integer2' => [1],
            'integer3' => [-1],
            'integer4' => [-0],
            'float' => [1.0],
            'string' => ['1'],
            'boolean' => [true],
            'array' => [['foo', 'bar']],
            'object' => [(object) ['foo' => 'bar']],
        ];
    }

    /**
     * @param mixed $value
     * @return void
     * @dataProvider integerProvider
     */
    public function testInteger($value): void
    {
        $rule = (new JsonNumber())->onlyIntegers();

        $this->assertSame(is_int($value), $rule->passes('value', $value));
    }
}
