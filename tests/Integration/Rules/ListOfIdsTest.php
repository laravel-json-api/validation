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

use LaravelJsonApi\Contracts\Schema\ID;
use LaravelJsonApi\Validation\Rules\ListOfIds;
use PHPUnit\Framework\MockObject\MockObject;
use LaravelJsonApi\Validation\Tests\Integration\TestCase;

class ListOfIdsTest extends TestCase
{
    /**
     * @var ID&MockObject
     */
    private ID&MockObject $id;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->id = $this->createMock(ID::class);
    }

    /**
     * @return void
     */
    public function testItIsValidWithDelimiter(): void
    {
        $this->id
            ->expects($this->once())
            ->method('match')
            ->with($value = 'foo,bar', $delimiter = ',')
            ->willReturn(true);

        $rule = new ListOfIds($this->id, $delimiter);

        $validator = $this->validatorFactory->make(
            ['foo' => $value],
            ['foo' => $rule],
        );

        $this->assertFalse($validator->fails());
    }

    /**
     * @return void
     */
    public function testItIsInvalidWithDelimiter(): void
    {
        $this->id
            ->expects($this->once())
            ->method('match')
            ->with($value = 'foo,bar', $delimiter = ',')
            ->willReturn(false);

        $rule = new ListOfIds($this->id, $delimiter);

        $validator = $this->validatorFactory->make(
            ['foo' => $value],
            ['foo' => $rule],
        );

        $this->assertTrue($validator->fails());
        $this->assertSame([
            'foo' => [
                'The foo field must be a list of resource identifiers.',
            ],
        ], $validator->errors()->getMessages());
    }

    /**
     * @return void
     */
    public function testItIsValidWithArray(): void
    {
        $this->id
            ->expects($this->once())
            ->method('matchAll')
            ->with($value = ['foo', 'bar'])
            ->willReturn(true);

        $rule = new ListOfIds($this->id);

        $validator = $this->validatorFactory->make(
            ['foo' => $value],
            ['foo' => $rule],
        );

        $this->assertFalse($validator->fails());
    }

    /**
     * @return void
     */
    public function testItIsInvalidWithArray(): void
    {
        $this->id
            ->expects($this->once())
            ->method('matchAll')
            ->with($value = ['foo', 'bar'])
            ->willReturn(false);

        $rule = new ListOfIds($this->id);

        $validator = $this->validatorFactory->make(
            ['foo' => $value],
            ['foo' => $rule],
        );

        $this->assertTrue($validator->fails());
        $this->assertSame([
            'foo' => [
                'The foo field must be a list of resource identifiers.',
            ],
        ], $validator->errors()->getMessages());
    }

    /**
     * @return void
     */
    public function testItIsStringWithoutDelimiter(): void
    {
        $this->id
            ->expects($this->never())
            ->method($this->anything());

        $rule = new ListOfIds($this->id);

        $validator = $this->validatorFactory->make(
            ['foo' => 'foo,bar'],
            ['foo' => $rule],
        );

        $this->assertTrue($validator->fails());
        $this->assertSame([
            'foo' => [
                'The foo field must be a list of resource identifiers.',
            ],
        ], $validator->errors()->getMessages());
    }
}