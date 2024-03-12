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

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->data = [
            'total' => 100,
            'weight' => 66.5,
            'boolean' => true,
            'boolean_string' => 'true',
        ];

        $this->rules = [
            'total' => [Rule::integer()],
            'weight' => [Rule::number()],
            'boolean' => [Rule::boolean()],
            'boolean_string' => [Rule::boolean()->asString()],
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
    public static function invalidProvider(): array
    {
        return [
            'integer' => ['total', 5.5, 'The total field must be an integer.'],
            'number' => ['weight', 'foo', 'The weight field must be a number.'],
            'boolean' => ['boolean', 'true', 'The boolean field must be a boolean.'],
            'boolean_string' => ['boolean_string', 'blah', 'The boolean string field must be true or false.'],
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
