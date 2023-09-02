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

namespace LaravelJsonApi\Validation\Tests\Unit\Validators;

use Illuminate\Contracts\Validation\Factory;
use Illuminate\Contracts\Validation\Validator;
use LaravelJsonApi\Core\Extensions\Atomic\Operations\Delete;
use LaravelJsonApi\Core\Extensions\Atomic\Values\Href;
use LaravelJsonApi\Core\Extensions\Atomic\Values\ParsedHref;
use LaravelJsonApi\Core\Values\ResourceId;
use LaravelJsonApi\Core\Values\ResourceType;
use LaravelJsonApi\Validation\Extractors\DeleteExtractor;
use LaravelJsonApi\Validation\ValidatedSchema;
use LaravelJsonApi\Validation\Validators\DestroyValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DestroyValidatorTest extends TestCase
{
    /**
     * @var Factory&MockObject
     */
    private Factory&MockObject $factory;

    /**
     * @var MockObject&ValidatedSchema
     */
    private ValidatedSchema&MockObject $schema;

    /**
     * @var MockObject&DeleteExtractor
     */
    private DeleteExtractor&MockObject $extractor;

    /**
     * @var DestroyValidator
     */
    private DestroyValidator $validator;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new DestroyValidator(
            $this->factory = $this->createMock(Factory::class),
            $this->schema = $this->createMock(ValidatedSchema::class),
            $this->extractor = $this->createMock(DeleteExtractor::class),
        );
    }

    /**
     * @return void
     */
    public function testItExtractsData(): void
    {
        $model = new \stdClass();

        $operation = new Delete(
            new ParsedHref(
                new Href('/posts/123'),
                new ResourceType('posts'),
                new ResourceId('123'),
            ),
        );

        $expected = ['foo' => 'bar'];

        $this->factory->expects($this->never())->method($this->anything());
        $this->schema->expects($this->never())->method($this->anything());

        $this->extractor
            ->expects($this->once())
            ->method('extract')
            ->with($this->identicalTo($model))
            ->willReturn($expected);

        $this->assertSame($expected, $this->validator->extract($operation, $model));
    }

    /**
     * @return void
     */
    public function testItBuildsValidator(): void
    {
        $model = new \stdClass();

        $operation = new Delete(
            new ParsedHref(
                new Href('/posts/123'),
                new ResourceType('posts'),
                new ResourceId('123'),
            ),
        );

        $sequence = [];

        $this->extractor
            ->expects($this->once())
            ->method('extract')
            ->with($this->identicalTo($model))
            ->willReturn($data = ['foo' => 'bar']);

        $this->schema
            ->expects($this->once())
            ->method('deleteRules')
            ->with($this->identicalTo($model))
            ->willReturn($rules = ['foo' => 'required']);

        $this->schema
            ->expects($this->once())
            ->method('deleteMessages')
            ->willReturn($messages = ['foo.required' => 'Foo is required.']);

        $this->schema
            ->expects($this->once())
            ->method('deleteAttributes')
            ->willReturn($attributes = ['foo']);

        $this->factory
            ->expects($this->once())
            ->method('make')
            ->with($data, $rules, $messages, $attributes)
            ->willReturn($validator = $this->createMock(Validator::class));

        $this->schema
            ->expects($this->once())
            ->method('withDeleteValidator')
            ->with($this->identicalTo($validator), $this->identicalTo($operation), $this->identicalTo($model))
            ->willReturnCallback(function () use (&$sequence): void {
                $sequence[] = 'withDeleteValidator';
            });

        $this->schema
            ->expects($this->once())
            ->method('afterDeleteValidation')
            ->with($this->identicalTo($validator), $this->identicalTo($operation), $this->identicalTo($model))
            ->willReturnCallback(function (Validator $v) use (&$sequence): void {
                $sequence[] = 'afterDeleteValidation';
            });

        $validator
            ->expects($this->once())
            ->method('after')
            ->willReturnCallback(function (\Closure $callback) use (&$sequence, $validator): void {
                $sequence[] = 'after';
                $callback($validator);
            });

        $actual = $this->validator->make($operation, $model);

        $this->assertSame($validator, $actual);
        $this->assertSame(['withDeleteValidator', 'after', 'afterDeleteValidation'], $sequence);
    }
}
