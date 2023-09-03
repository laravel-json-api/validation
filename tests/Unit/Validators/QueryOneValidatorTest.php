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

use Closure;
use Generator;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Contracts\Validation\Validator;
use LaravelJsonApi\Core\Query\Input\QueryOne;
use LaravelJsonApi\Core\Query\Input\QueryRelated;
use LaravelJsonApi\Core\Query\Input\QueryRelationship;
use LaravelJsonApi\Core\Query\Input\WillQueryOne;
use LaravelJsonApi\Core\Values\ResourceId;
use LaravelJsonApi\Core\Values\ResourceType;
use LaravelJsonApi\Validation\Filters\QueryOneParser;
use LaravelJsonApi\Validation\QueryRules;
use LaravelJsonApi\Validation\Rules\AllowedCountableFields;
use LaravelJsonApi\Validation\Rules\AllowedFieldSets;
use LaravelJsonApi\Validation\Rules\AllowedFilterParameters;
use LaravelJsonApi\Validation\Rules\AllowedIncludePaths;
use LaravelJsonApi\Validation\Rules\ParameterNotSupported;
use LaravelJsonApi\Validation\ValidatedQuery;
use LaravelJsonApi\Validation\Validators\QueryOneValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class QueryOneValidatorTest extends TestCase
{
    /**
     * @var Factory&MockObject
     */
    private Factory&MockObject $factory;

    /**
     * @var ValidatedQuery&MockObject
     */
    private ValidatedQuery&MockObject $querySchema;

    /**
     * @var MockObject&QueryOneParser
     */
    private QueryOneParser&MockObject $parser;

    /**
     * @var QueryRules&MockObject
     */
    private QueryRules&MockObject $rules;

    /**
     * @var QueryOneValidator
     */
    private QueryOneValidator $validator;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new QueryOneValidator(
            $this->factory = $this->createMock(Factory::class),
            $this->querySchema = $this->createMock(ValidatedQuery::class),
            $this->parser = $this->createMock(QueryOneParser::class),
            $this->rules = $this->createMock(QueryRules::class),
        );
    }

    /**
     * @return array
     */
    public function queryProvider(): array
    {
        return [
            [
                new QueryOne(
                    new ResourceType('posts'),
                    new ResourceId('123'),
                    ['include' => 'author,tags'],
                ),
            ],
            [
                new WillQueryOne(
                    new ResourceType('posts'),
                    ['include' => 'author,tags'],
                ),
            ],
            [
                new QueryRelated(
                    new ResourceType('posts'),
                    new ResourceId('123'),
                    'author',
                    ['include' => 'profile'],
                )],
            [
                new QueryRelationship(
                    new ResourceType('posts'),
                    new ResourceId('123'),
                    'author',
                    ['include' => 'image'],
                ),
            ],
        ];
    }

    /**
     * @param QueryOne|WillQueryOne|QueryRelated|QueryRelationship $query
     * @return void
     * @dataProvider queryProvider
     */
    public function test(QueryOne|WillQueryOne|QueryRelated|QueryRelationship $query): void
    {
        $sequence = [];

        $rules = [
            'fields' => [
                'nullable',
                'array',
                $this->withAllowedFieldSets(),
            ],
            'filter' => [
                'nullable',
                'array',
                $this->withAllowedFilters($query),
            ],
            'filter.bar' => ['nullable', 'string', 'min:3'],
            'filter.foo' => ['nullable', 'string', 'min:5'],
            'include' => [
                'nullable',
                'string',
                $this->withAllowedIncludePaths(),
            ],
            'page' => $notSupported = $this->withNotSupported(),
            'sort' => $notSupported,
            'withCount' => [
                'nullable',
                'string',
                $this->withCountable(),
            ],
        ];

        if ($query->getFieldName()) {
            $this->querySchema
                ->expects($this->once())
                ->method('withRelation')
                ->with($this->identicalTo($query->type), $query->getFieldName());
        }

        $filters = (function (): Generator {
            yield 'filter1';
            yield 'filter2';
        })();

        $this->querySchema
            ->expects($this->once())
            ->method('filters')
            ->willReturn($filters);

        $this->querySchema
            ->method('pagination')
            ->willReturn(null);

        $this->querySchema
            ->expects($this->once())
            ->method('messages')
            ->willReturn($messages = ['foo' => 'blah!']);

        $this->querySchema
            ->expects($this->once())
            ->method('attributes')
            ->willReturn($attributes = ['foo' => 'blah!!']);

        $this->parser
            ->expects($this->once())
            ->method('with')
            ->with($this->identicalTo($query))
            ->willReturnSelf();

        $this->parser
            ->expects($this->once())
            ->method('parse')
            ->with($this->identicalTo($filters))
            ->willReturn(['filter.foo' => $rules['filter.foo'], 'filter.bar' => $rules['filter.bar']]);

        $this->factory
            ->expects($this->once())
            ->method('make')
            ->with(
                $this->identicalTo($query->parameters),
                $this->identicalTo($rules),
                $this->identicalTo($messages),
                $this->identicalTo($attributes),
            )
            ->willReturn($validator = $this->createMock(Validator::class));

        $this->withHooks($validator, $query, $sequence);

        $actual = $this->validator->make($query);

        $this->assertSame($validator, $actual);
        $this->assertSame([
            'withValidator',
            'withToOneValidator',
            'after',
            'afterValidation',
            'afterToOneValidation',
        ], $sequence);
    }

    /**
     * @return AllowedFieldSets
     */
    private function withAllowedFieldSets(): AllowedFieldSets
    {
        $this->rules
            ->expects($this->once())
            ->method('fieldSets')
            ->willReturn($rule = $this->createMock(AllowedFieldSets::class));

        return $rule;
    }

    /**
     * @param QueryOne|WillQueryOne|QueryRelated|QueryRelationship $query
     * @return AllowedFilterParameters
     */
    private function withAllowedFilters(
        QueryOne|WillQueryOne|QueryRelated|QueryRelationship $query,
    ): AllowedFilterParameters
    {
        $this->rules
            ->expects($this->once())
            ->method('filters')
            ->with($this->identicalTo($query))
            ->willReturn($rule = $this->createMock(AllowedFilterParameters::class));

        return $rule;
    }

    /**
     * @return AllowedIncludePaths
     */
    private function withAllowedIncludePaths(): AllowedIncludePaths
    {
        $this->rules
            ->expects($this->once())
            ->method('includePaths')
            ->willReturn($rule = $this->createMock(AllowedIncludePaths::class));

        return $rule;
    }

    /**
     * @return ParameterNotSupported
     */
    private function withNotSupported(): ParameterNotSupported
    {
        $this->rules
            ->method('notSupported')
            ->willReturn($rule = $this->createMock(ParameterNotSupported::class));

        return $rule;
    }

    /**
     * @return AllowedCountableFields
     */
    private function withCountable(): AllowedCountableFields
    {
        $this->rules
            ->expects($this->once())
            ->method('countable')
            ->willReturn($rule = $this->createMock(AllowedCountableFields::class));

        return $rule;
    }

    /**
     * @param Validator&MockObject $validator
     * @param QueryOne|WillQueryOne|QueryRelated|QueryRelationship $query
     * @param array $sequence
     * @return void
     */
    private function withHooks(
        Validator&MockObject $validator,
        QueryOne|WillQueryOne|QueryRelated|QueryRelationship $query,
        array &$sequence
    ): void
    {
        $this->querySchema
            ->expects($this->never())
            ->method('withToManyValidator');

        $this->querySchema
            ->expects($this->never())
            ->method('afterToManyValidation');

        $this->querySchema
            ->expects($this->once())
            ->method('withValidator')
            ->with($this->identicalTo($validator), $this->identicalTo($query))
            ->willReturnCallback(function () use (&$sequence): void {
                $sequence[] = 'withValidator';
            });

        $this->querySchema
            ->expects($this->once())
            ->method('withToOneValidator')
            ->with($this->identicalTo($validator), $this->identicalTo($query))
            ->willReturnCallback(function () use (&$sequence): void {
                $sequence[] = 'withToOneValidator';
            });

        $this->querySchema
            ->expects($this->once())
            ->method('afterValidation')
            ->with($this->identicalTo($validator), $this->identicalTo($query))
            ->willReturnCallback(function () use (&$sequence): void {
                $sequence[] = 'afterValidation';
            });

        $this->querySchema
            ->expects($this->once())
            ->method('afterToOneValidation')
            ->with($this->identicalTo($validator), $this->identicalTo($query))
            ->willReturnCallback(function () use (&$sequence): void {
                $sequence[] = 'afterToOneValidation';
            });

        $validator
            ->expects($this->once())
            ->method('after')
            ->willReturnCallback(function (Closure $callback) use (&$sequence, $validator): void {
                $sequence[] = 'after';
                $callback($validator);
            });
    }
}
