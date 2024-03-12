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
use LaravelJsonApi\Contracts\Schema\Relation;
use LaravelJsonApi\Core\Document\Input\Values\ResourceIdentifier;
use LaravelJsonApi\Core\Extensions\Atomic\Operations\UpdateToOne;
use LaravelJsonApi\Core\Extensions\Atomic\Values\Ref;
use LaravelJsonApi\Core\Values\ResourceId;
use LaravelJsonApi\Core\Values\ResourceType;
use LaravelJsonApi\Validation\Extractors\RelationshipExtractor;
use LaravelJsonApi\Validation\Fields\UpdateRulesParser;
use LaravelJsonApi\Validation\ValidatedSchema;
use LaravelJsonApi\Validation\Validators\RelationshipValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RelationshipValidatorTest extends TestCase
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
     * @var MockObject&RelationshipExtractor
     */
    private RelationshipExtractor&MockObject $extractor;

    /**
     * @var MockObject&UpdateRulesParser
     */
    private UpdateRulesParser&MockObject $parser;

    /**
     * @var RelationshipValidator
     */
    private RelationshipValidator $validator;

    /**
     * @var UpdateToOne
     */
    private UpdateToOne $operation;

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

        $this->validator = new RelationshipValidator(
            $this->factory = $this->createMock(Factory::class),
            $this->schema = $this->createMock(ValidatedSchema::class),
            $this->extractor = $this->createMock(RelationshipExtractor::class),
            $this->parser = $this->createMock(UpdateRulesParser::class),
        );

        $this->operation = new UpdateToOne(
            new Ref(type: new ResourceType('posts'), id: new ResourceId('123'), relationship: 'author'),
            new ResourceIdentifier(
                type: new ResourceType('users'),
                id: new ResourceId('456'),
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
            ->with($this->identicalTo($this->operation))
            ->willReturn($expected);

        $this->assertSame($expected, $this->validator->extract($this->operation, $this->model));
    }

    /**
     * @return void
     */
    public function testItBuildsValidator(): void
    {
        $sequence = [];

        $this->extractor
            ->expects($this->once())
            ->method('extract')
            ->with($this->identicalTo($this->operation))
            ->willReturn($data = ['foo' => 'bar']);

        $this->schema
            ->expects($this->once())
            ->method('relationship')
            ->with('author')
            ->willReturn($relation = $this->createMock(Relation::class));

        $this->parser
            ->expects($this->once())
            ->method('with')
            ->with($this->identicalTo($this->model))
            ->willReturnSelf();

        $this->parser
            ->expects($this->once())
            ->method('parse')
            ->with($this->identicalTo([$relation]))
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

        $this->schema
            ->expects($this->once())
            ->method('withRelationshipValidator')
            ->with(
                $this->identicalTo($validator),
                $this->identicalTo($this->operation),
                $this->identicalTo($this->model)
            )
            ->willReturnCallback(function () use (&$sequence): void {
                $sequence[] = 'withRelationshipValidator';
            });

        $this->schema
            ->expects($this->once())
            ->method('afterRelationshipValidation')
            ->with(
                $this->identicalTo($validator),
                $this->identicalTo($this->operation),
                $this->identicalTo($this->model)
            )
            ->willReturnCallback(function () use (&$sequence): void {
                $sequence[] = 'afterRelationshipValidation';
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
            'withRelationshipValidator',
            'after',
            'afterRelationshipValidation',
        ], $sequence);
    }
}
