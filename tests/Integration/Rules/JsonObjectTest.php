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

use LaravelJsonApi\Validation\Rules\JsonObject;
use LaravelJsonApi\Validation\Tests\Integration\TestCase;

class JsonObjectTest extends TestCase
{
    /**
     * @return array<string, array{0: array}>
     */
    public static function validProvider(): array
    {
        return [
            'associative array' => [['foo' => true, 'bar' => true]],
            'non-sequential keys' => [[0 => 'foo', 2 => 'bar']],
            'not zero-indexed' => [[1 => 'foo']],
        ];
    }

    /**
     * @return array<string, array{0: array}>
     */
    public static function validWithEmptyProvider(): array
    {
        $scenarios = self::validProvider();
        $scenarios['empty array'] = [[]];
        return $scenarios;
    }

    /**
     * @return array<string, array{0: mixed}>
     */
    public static function invalidProvider(): array
    {
        return [
            'empty array' => [[]],
            'list with one element' => [['foo']],
            'list with many elements' => [['foo', 'bar']],
            'bool' => [true],
            'string' => ['blah!'],
            'int' => [1],
            'float' => [1.0],
            'object' => [(object) ['foo' => 'bar']],
        ];
    }

    /**
     * @return array<string, array{0: mixed}>
     */
    public static function invalidWithoutEmptyProvider(): array
    {
        $scenarios = self::invalidProvider();
        unset($scenarios['empty array']);
        return $scenarios;
    }

    /**
     * @param array $value
     * @return void
     * @dataProvider validProvider
     */
    public function testItIsValid(array $value): void
    {
        $validator = $this->validatorFactory->make(
            ['options' => $value],
            ['options' => new JsonObject()],
        );

        $this->assertFalse($validator->fails());
    }

    /**
     * @param array $value
     * @return void
     * @dataProvider validWithEmptyProvider
     */
    public function testItIsValidWhenAllowingEmpty(array $value): void
    {
        $validator = $this->validatorFactory->make(
            ['options' => $value],
            ['options' => (new JsonObject())->allowEmpty()],
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
            ['options' => $value],
            ['options' => new JsonObject()],
        );

        $this->assertTrue($validator->fails());
        $this->assertSame([
            'options' => [
                'The options field must be an object.',
            ],
        ], $validator->errors()->getMessages());
    }

    /**
     * @param mixed $value
     * @return void
     * @dataProvider invalidWithoutEmptyProvider
     */
    public function testItIsInvalidWhenAllowingEmpty(mixed $value): void
    {
        $validator = $this->validatorFactory->make(
            ['options' => $value],
            ['options' => (new JsonObject())->allowEmpty()],
        );

        $this->assertTrue($validator->fails());
        $this->assertSame([
            'options' => [
                'The options field must be an object.',
            ],
        ], $validator->errors()->getMessages());
    }
}