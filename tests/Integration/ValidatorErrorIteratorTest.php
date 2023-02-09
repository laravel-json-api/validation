<?php
/*
 * Copyright 2023 Cloud Creativity Limited
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
    public function sourcePrefixProvider(): array
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
