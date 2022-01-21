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

namespace LaravelJsonApi\Validation\Tests\Integration;

use LaravelJsonApi\Validation\Rule;

class ValueTest extends TestCase
{
    /**
     * @var array
     */
    private array $data;

    /**
     * @var array
     */
    private array $rules;

    protected function setUp(): void
    {
        parent::setUp();

        $this->data = [
            'total' => 100,
        ];

        $this->rules = [
            'total' => [Rule::number()],
        ];
    }

    public function testValid(): void
    {
        $validator = $this->validatorFactory->make($this->data, $this->rules);

        $this->assertFalse($validator->fails());
    }

    /**
     * @return array
     */
    public function invalidProvider(): array
    {
        return [
            'number' => ['total', 'foo', 'The total field must be a number.'],
        ];
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param string $expected
     * @return void
     * @dataProvider invalidProvider
     */
    public function testInvalid(string $key, $value, string $expected): void
    {
        $data = $this->data;
        $data[$key] = $value;

        $validator = $this->validatorFactory->make($data, $this->rules);

        $this->assertTrue($validator->fails());
        $this->assertSame([$expected], $validator->errors()->all());
    }
}
