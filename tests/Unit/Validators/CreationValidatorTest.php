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

use Generator;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Contracts\Validation\Validator;
use LaravelJsonApi\Core\Document\Input\Values\ResourceObject;
use LaravelJsonApi\Core\Extensions\Atomic\Operations\Create;
use LaravelJsonApi\Core\Values\ResourceId;
use LaravelJsonApi\Core\Values\ResourceType;
use LaravelJsonApi\Validation\Extractors\CreationExtractor;
use LaravelJsonApi\Validation\Fields\CreationRulesParser;
use LaravelJsonApi\Validation\ValidatedSchema;
use LaravelJsonApi\Validation\Validators\CreationValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CreationValidatorTest extends TestCase
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
     * @var MockObject&CreationExtractor
     */
    private CreationExtractor&MockObject $extractor;

    /**
     * @var MockObject&CreationRulesParser
     */
    private CreationRulesParser&MockObject $parser;

    /**
     * @var CreationValidator
     */
    private CreationValidator $validator;

    /**
     * @var Create
     */
    private Create $operation;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new CreationValidator(
            $this->factory = $this->createMock(Factory::class),
            $this->schema = $this->createMock(ValidatedSchema::class),
            $this->extractor = $this->createMock(CreationExtractor::class),
            $this->parser = $this->createMock(CreationRulesParser::class),
        );

        $this->operation = new Create(
            null,
            new ResourceObject(
                type: new ResourceType('posts'),
                id: new ResourceId('123'),
            ),
        );
    }

    /**
     * @return void
     */
    public function testItExtractsData(): void
    {
        $expected = ['foo' => 'bar'];

        $this->factory->expects($this->never())->method($this->anything());
        $this->schema->expects($this->never())->method($this->anything());
        $this->parser->expects($this->never())->method($this->anything());

        $this->extractor
            ->expects($this->once())
            ->method('extract')
            ->with($this->identicalTo($this->operation))
            ->willReturn($expected);

        $this->assertSame($expected, $this->validator->extract($this->operation));
    }

    /**
     * @return void
     */
    public function testItBuildsValidator(): void
    {
        $sequence = [];

        $fields = (function (): Generator {
            yield 'foo' => 'bar';
            yield 'baz' => 'bat';
        })();

        $this->extractor
            ->expects($this->once())
            ->method('extract')
            ->with($this->identicalTo($this->operation))
            ->willReturn($data = ['foo' => 'bar']);

        $this->schema
            ->expects($this->once())
            ->method('fields')
            ->willReturn($fields);

        $this->parser
            ->expects($this->once())
            ->method('parse')
            ->with($this->identicalTo($fields))
            ->willReturn($rules = ['foo' => 'required']);

        $this->schema
            ->expects($this->once())
            ->method('messages')
            ->willReturn($messages = ['foo.required' => 'Foo is required.']);

        $this->schema
            ->expects($this->once())
            ->method('attributes')
            ->willReturn($attributes = ['foo']);

        $this->factory
            ->expects($this->once())
            ->method('make')
            ->with($data, $rules, $messages, $attributes)
            ->willReturn($validator = $this->createMock(Validator::class));

        $this->schema->expects($this->never())->method('withUpdateValidator');
        $this->schema->expects($this->never())->method('afterUpdateValidation');

        $this->schema
            ->expects($this->once())
            ->method('withValidator')
            ->with($this->identicalTo($validator), $this->identicalTo($this->operation))
            ->willReturnCallback(function () use (&$sequence): void {
                $sequence[] = 'withValidator';
            });

        $this->schema
            ->expects($this->once())
            ->method('withCreationValidator')
            ->with($this->identicalTo($validator), $this->identicalTo($this->operation))
            ->willReturnCallback(function () use (&$sequence): void {
                $sequence[] = 'withCreationValidator';
            });

        $this->schema
            ->expects($this->once())
            ->method('afterValidation')
            ->with($this->identicalTo($validator), $this->identicalTo($this->operation))
            ->willReturnCallback(function () use (&$sequence): void {
                $sequence[] = 'afterValidation';
            });

        $this->schema
            ->expects($this->once())
            ->method('afterCreationValidation')
            ->with($this->identicalTo($validator), $this->identicalTo($this->operation))
            ->willReturnCallback(function () use (&$sequence): void {
                $sequence[] = 'afterCreationValidation';
            });

        $validator
            ->expects($this->once())
            ->method('after')
            ->willReturnCallback(function (\Closure $callback) use (&$sequence, $validator): void {
                $sequence[] = 'after';
                $callback($validator);
            });

        $actual = $this->validator->make($this->operation);

        $this->assertSame($validator, $actual);
        $this->assertSame([
            'withValidator',
            'withCreationValidator',
            'after',
            'afterValidation',
            'afterCreationValidation',
        ], $sequence);
    }
}
