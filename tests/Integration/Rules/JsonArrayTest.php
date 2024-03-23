<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Validation\Tests\Integration\Rules;

use LaravelJsonApi\Validation\Rules\JsonArray;
use LaravelJsonApi\Validation\Tests\Integration\TestCase;

class JsonArrayTest extends TestCase
{
    /**
     * @return array<string, array{0: array}>
     */
    public static function validProvider(): array
    {
        return [
            'empty list' => [[]],
            'list with one element' => [['foo']],
            'list with many elements' => [['foo', 'bar']],
        ];
    }

    /**
     * @return array<string, array{0: mixed}>
     */
    public static function invalidProvider(): array
    {
        return [
            'non-sequential keys' => [[0 => 'foo', 2 => 'bar']],
            'not zero-indexed' => [[1 => 'foo']],
            'associative array' => [['foo' => true, 'bar' => true]],
            'bool' => [true],
            'string' => ['blah!'],
            'int' => [1],
            'float' => [1.0],
            'object' => [(object) ['foo' => 'bar']],
        ];
    }

    /**
     * @param array $value
     * @return void
     * @dataProvider validProvider
     */
    public function testItIsValid(array $value): void
    {
        $validator = $this->validatorFactory->make(
            ['permissions' => $value],
            ['permissions' => new JsonArray()],
        );

        $this->assertFalse($validator->fails());
    }

    /**
     * @param mixed $value
     * @return void
     * @dataProvider invalidProvider
     */
    public function testItIsInvalid(mixed $value): void
    {
        $validator = $this->validatorFactory->make(
            ['permissions' => $value],
            ['permissions' => new JsonArray()],
        );

        $this->assertTrue($validator->fails());
        $this->assertSame([
            'permissions' => [
                'The permissions field must be an array.',
            ],
        ], $validator->errors()->getMessages());
    }
}