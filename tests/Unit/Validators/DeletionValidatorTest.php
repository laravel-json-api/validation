<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
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
use LaravelJsonApi\Validation\Extractors\DeletionExtractor;
use LaravelJsonApi\Validation\ValidatedSchema;
use LaravelJsonApi\Validation\Validators\DeletionValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DeletionValidatorTest extends TestCase
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
     * @var MockObject&DeletionExtractor
     */
    private DeletionExtractor&MockObject $extractor;

    /**
     * @var DeletionValidator
     */
    private DeletionValidator $validator;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new DeletionValidator(
            $this->factory = $this->createMock(Factory::class),
            $this->schema = $this->createMock(ValidatedSchema::class),
            $this->extractor = $this->createMock(DeletionExtractor::class),
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
            ->method('deletionRules')
            ->with($this->identicalTo($model))
            ->willReturn($rules = ['foo' => 'required']);

        $this->schema
            ->expects($this->once())
            ->method('deletionMessages')
            ->willReturn($messages = ['foo.required' => 'Foo is required.']);

        $this->schema
            ->expects($this->once())
            ->method('deletionAttributes')
            ->willReturn($attributes = ['foo']);

        $this->factory
            ->expects($this->once())
            ->method('make')
            ->with($data, $rules, $messages, $attributes)
            ->willReturn($validator = $this->createMock(Validator::class));

        $this->schema
            ->expects($this->once())
            ->method('withDeletionValidator')
            ->with($this->identicalTo($validator), $this->identicalTo($operation), $this->identicalTo($model))
            ->willReturnCallback(function () use (&$sequence): void {
                $sequence[] = 'withDeleteValidator';
            });

        $this->schema
            ->expects($this->once())
            ->method('afterDeletionValidation')
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
