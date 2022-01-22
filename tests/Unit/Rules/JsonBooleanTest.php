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

use LaravelJsonApi\Validation\Rules\JsonBoolean;
use PHPUnit\Framework\TestCase;

class JsonBooleanTest extends TestCase
{
    /**
     * @return array
     */
    public function valueProvider(): array
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
    public function stringProvider(): array
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
