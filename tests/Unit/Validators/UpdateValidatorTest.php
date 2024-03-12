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
use LaravelJsonApi\Core\Extensions\Atomic\Operations\Update;
use LaravelJsonApi\Core\Values\ResourceId;
use LaravelJsonApi\Core\Values\ResourceType;
use LaravelJsonApi\Validation\Extractors\UpdateExtractor;
use LaravelJsonApi\Validation\Fields\UpdateRulesParser;
use LaravelJsonApi\Validation\ValidatedSchema;
use LaravelJsonApi\Validation\Validators\UpdateValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UpdateValidatorTest extends TestCase
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
     * @var MockObject&UpdateExtractor
     */
    private UpdateExtractor&MockObject $extractor;

    /**
     * @var MockObject&UpdateRulesParser
     */
    private UpdateRulesParser&MockObject $parser;

    /**
     * @var UpdateValidator
     */
    private UpdateValidator $validator;

    /**
     * @var Update
     */
    private Update $operation;

    /**
     * @var object
     */
    private object $model;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new UpdateValidator(
            $this->factory = $this->createMock(Factory::class),
            $this->schema = $this->createMock(ValidatedSchema::class),
            $this->extractor = $this->createMock(UpdateExtractor::class),
            $this->parser = $this->createMock(UpdateRulesParser::class),
        );

        $this->operation = new Update(
            null,
            new ResourceObject(
                type: new ResourceType('posts'),
                id: new ResourceId('123'),
            ),
        );

        $this->model = new \stdClass();
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
            ->with($this->identicalTo($this->operation), $this->identicalTo($this->model))
            ->willReturn($expected);

        $this->assertSame($expected, $this->validator->extract($this->operation, $this->model));
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
            ->with($this->identicalTo($this->operation), $this->identicalTo($this->model))
            ->willReturn($data = ['foo' => 'bar']);

        $this->schema
            ->expects($this->once())
            ->method('fields')
            ->willReturn($fields);

        $this->parser
            ->expects($this->once())
            ->method('with')
            ->with($this->identicalTo($this->model))
            ->willReturnSelf();

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

        $this->schema->expects($this->never())->method('withCreationValidator');
        $this->schema->expects($this->never())->method('afterCreationValidation');

        $this->schema
            ->expects($this->once())
            ->method('withValidator')
            ->with($this->identicalTo($validator), $this->identicalTo($this->operation))
            ->willReturnCallback(function () use (&$sequence): void {
                $sequence[] = 'withValidator';
            });

        $this->schema
            ->expects($this->once())
            ->method('withUpdateValidator')
            ->with(
                $this->identicalTo($validator),
                $this->identicalTo($this->operation),
                $this->identicalTo($this->model)
            )
            ->willReturnCallback(function () use (&$sequence): void {
                $sequence[] = 'withUpdateValidator';
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
            ->method('afterUpdateValidation')
            ->with(
                $this->identicalTo($validator),
                $this->identicalTo($this->operation),
                $this->identicalTo($this->model)
            )
            ->willReturnCallback(function () use (&$sequence): void {
                $sequence[] = 'afterUpdateValidation';
            });

        $validator
            ->expects($this->once())
            ->method('after')
            ->willReturnCallback(function (\Closure $callback) use (&$sequence, $validator): void {
                $sequence[] = 'after';
                $callback($validator);
            });

        $actual = $this->validator->make($this->operation, $this->model);

        $this->assertSame($validator, $actual);
        $this->assertSame([
            'withValidator',
            'withUpdateValidator',
            'after',
            'afterValidation',
            'afterUpdateValidation',
        ], $sequence);
    }
}
