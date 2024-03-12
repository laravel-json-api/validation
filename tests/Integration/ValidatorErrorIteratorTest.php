<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Validation\Tests\Integration;

class ValidatorErrorIteratorTest extends TestCase
{

    public function test(): void
    {
        $validator = $this->validatorFactory->make(
            ['foo' => 'bar', 'baz' => 'bat'],
            ['foo' => 'string|min:4', 'baz' => 'integer'],
        );

        $this->assertTrue($validator->fails());

        $errors = $this->factory
            ->createErrors($validator)
            ->toArray();

        $this->assertSame([
            [
                'detail' => 'The foo field must be at least 4 characters.',
                'source' => ['pointer' => '/foo'],
                'status' => '422',
                'title' => 'Unprocessable Entity',
            ],
            [
                'detail' => 'The baz field must be an integer.',
                'source' => ['pointer' => '/baz'],
                'status' => '422',
                'title' => 'Unprocessable Entity',
            ],
        ], $errors);
    }

    /**
     * @return array
     */
    public static function sourcePrefixProvider(): array
    {
        return [
            ['/data/attributes', '/data/attributes'],
            ['data/attributes', '/data/attributes'],
            ['/data/attributes/', '/data/attributes'],
        ];
    }

    /**
     * @param string $prefix
     * @param string $expected
     * @dataProvider sourcePrefixProvider
     */
    public function testSourcePrefix(string $prefix, string $expected): void
    {
        $validator = $this->validatorFactory->make(
            ['foo' => 'bar', 'baz' => 'bat'],
            ['foo' => 'string|min:4', 'baz' => 'integer'],
        );

        $this->assertTrue($validator->fails());

        $errors = $this->factory
            ->createErrors($validator)
            ->withSourcePrefix($prefix)
            ->toArray();

        $this->assertSame([
            [
                'detail' => 'The foo field must be at least 4 characters.',
                'source' => ['pointer' => "{$expected}/foo"],
                'status' => '422',
                'title' => 'Unprocessable Entity',
            ],
            [
                'detail' => 'The baz field must be an integer.',
                'source' => ['pointer' => "{$expected}/baz"],
                'status' => '422',
                'title' => 'Unprocessable Entity',
            ],
        ], $errors);
    }
}
