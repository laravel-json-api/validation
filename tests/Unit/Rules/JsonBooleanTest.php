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

use LaravelJsonApi\Validation\Rules\JsonBoolean;
use PHPUnit\Framework\TestCase;

class JsonBooleanTest extends TestCase
{
    /**
     * @return array
     */
    public static function valueProvider(): array
    {
        return [
            'true' => [true],
            'false' => [false],
            'string' => ['true'],
            'int' => [1],
            'float' => [1.0],
            'object' => [(object) ['foo' => 'bar']],
            'array' => [['foo', 'bar']],
        ];
    }

    /**
     * @param mixed $value
     * @return void
     * @dataProvider valueProvider
     */
    public function test($value): void
    {
        $rule = new JsonBoolean();

        $this->assertSame(is_bool($value), $rule->passes('value', $value));
    }

    /**
     * @return array[]
     */
    public static function stringProvider(): array
    {
        return [
            'true' => ['true', true],
            'false' => ['false', true],
            'TRUE' => ['TRUE', true],
            'FALSE' => ['FALSE', true],
            '1' => ['1', true],
            '0' => ['0', true],
            'invalid' => ['foobar', false],
        ];
    }

    /**
     * @param string $value
     * @param bool $expected
     * @return void
     * @dataProvider stringProvider
     */
    public function testAsString(string $value, bool $expected): void
    {
        $rule = (new JsonBoolean())->asString();

        $this->assertSame($expected, $rule->passes('value', $value));
    }
}
