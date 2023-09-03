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
use LaravelJsonApi\Core\Query\Input\QueryMany;
use LaravelJsonApi\Core\Query\Input\QueryRelated;
use LaravelJsonApi\Core\Query\Input\QueryRelationship;
use LaravelJsonApi\Core\Values\ResourceId;
use LaravelJsonApi\Core\Values\ResourceType;
use LaravelJsonApi\Validation\Filters\QueryManyParser;
use LaravelJsonApi\Validation\Pagination\ValidatedPaginator;
use LaravelJsonApi\Validation\QueryRules;
use LaravelJsonApi\Validation\Rules\AllowedCountableFields;
use LaravelJsonApi\Validation\Rules\AllowedFieldSets;
use LaravelJsonApi\Validation\Rules\AllowedFilterParameters;
use LaravelJsonApi\Validation\Rules\AllowedIncludePaths;
use LaravelJsonApi\Validation\Rules\AllowedPageParameters;
use LaravelJsonApi\Validation\Rules\AllowedSortParameters;
use LaravelJsonApi\Validation\Rules\ParameterNotSupported;
use LaravelJsonApi\Validation\ValidatedQuery;
use LaravelJsonApi\Validation\Validators\QueryManyValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class QueryManyValidatorTest extends TestCase
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
     * @var MockObject&QueryManyParser
     */
    private QueryManyParser&MockObject $parser;

    /**
     * @var QueryRules&MockObject
     */
    private QueryRules&MockObject $rules;

    /**
     * @var QueryManyValidator
     */
    private QueryManyValidator $validator;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new QueryManyValidator(
            $this->factory = $this->createMock(Factory::class),
            $this->querySchema = $this->createMock(ValidatedQuery::class),
            $this->parser = $this->createMock(QueryManyParser::class),
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
                new QueryMany(
                    new ResourceType('posts'),
                    ['include' => 'author,tags'],
                ),
            ],
            [
                new QueryRelated(
                    new ResourceType('posts'),
                    new ResourceId('123'),
                    'tags',
                    ['include' => 'videos'],
                )],
            [
                new QueryRelationship(
                    new ResourceType('posts'),
                    new ResourceId('123'),
                    'tags',
                    ['include' => 'media'],
                ),
            ],
        ];
    }

    /**
     * @param QueryMany|QueryRelated|QueryRelationship $query
     * @return void
     * @dataProvider queryProvider
     */
    public function testItValidatesWithoutPagination(QueryMany|QueryRelated|QueryRelationship $query): void
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
            'page' => $this->withNotSupported(),
            'sort' => [
                'nullable',
                'string',
                $this->withAllowedSort(),
            ],
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
            'withToManyValidator',
            'after',
            'afterValidation',
            'afterToManyValidation',
        ], $sequence);
    }

    /**
     * @param QueryMany|QueryRelated|QueryRelationship $query
     * @return void
     * @dataProvider queryProvider
     */
    public function testItValidatesWithPagination(QueryMany|QueryRelated|QueryRelationship $query): void
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
            'page' => [
                'nullable',
                'array',
                $this->withAllowedPage(),
            ],
            'page.number' => ['required', 'integer', 'min:1'],
            'page.size' => ['integer', 'between:1,250'],
            'sort' => [
                'nullable',
                'string',
                $this->withAllowedSort(),
            ],
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
            ->willReturn($paginator = $this->createMock(ValidatedPaginator::class));

        $paginator
            ->expects($this->once())
            ->method('rules')
            ->with($this->identicalTo($query))
            ->willReturn(['page.number' => $rules['page.number'], 'page.size' => $rules['page.size']]);

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
            'withToManyValidator',
            'after',
            'afterValidation',
            'afterToManyValidation',
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
     * @param QueryMany|QueryRelated|QueryRelationship $query
     * @return AllowedFilterParameters
     */
    private function withAllowedFilters(QueryMany|QueryRelated|QueryRelationship $query): AllowedFilterParameters
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
            ->expects($this->once())
            ->method('notSupported')
            ->willReturn($rule = $this->createMock(ParameterNotSupported::class));

        return $rule;
    }

    /**
     * @return AllowedPageParameters
     */
    private function withAllowedPage(): AllowedPageParameters
    {
        $this->rules
            ->expects($this->once())
            ->method('page')
            ->willReturn($rule = $this->createMock(AllowedPageParameters::class));

        return $rule;
    }

    /**
     * @return AllowedSortParameters
     */
    private function withAllowedSort(): AllowedSortParameters
    {
        $this->rules
            ->expects($this->once())
            ->method('sort')
            ->willReturn($rule = $this->createMock(AllowedSortParameters::class));

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
     * @param QueryMany|QueryRelated|QueryRelationship $query
     * @param array $sequence
     * @return void
     */
    private function withHooks(
        Validator&MockObject $validator,
        QueryMany|QueryRelated|QueryRelationship $query,
        array &$sequence
    ): void
    {
        $this->querySchema
            ->expects($this->never())
            ->method('withToOneValidator');

        $this->querySchema
            ->expects($this->never())
            ->method('afterToOneValidation');

        $this->querySchema
            ->expects($this->once())
            ->method('withValidator')
            ->with($this->identicalTo($validator), $this->identicalTo($query))
            ->willReturnCallback(function () use (&$sequence): void {
                $sequence[] = 'withValidator';
            });

        $this->querySchema
            ->expects($this->once())
            ->method('withToManyValidator')
            ->with($this->identicalTo($validator), $this->identicalTo($query))
            ->willReturnCallback(function () use (&$sequence): void {
                $sequence[] = 'withToManyValidator';
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
            ->method('afterToManyValidation')
            ->with($this->identicalTo($validator), $this->identicalTo($query))
            ->willReturnCallback(function () use (&$sequence): void {
                $sequence[] = 'afterToManyValidation';
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
