<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Validation\Tests\Unit;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Request;
use LaravelJsonApi\Contracts\Pagination\Paginator;
use LaravelJsonApi\Contracts\Schema\Container as SchemaContainer;
use LaravelJsonApi\Contracts\Schema\Query as QuerySchema;
use LaravelJsonApi\Contracts\Schema\Relation;
use LaravelJsonApi\Contracts\Schema\Schema;
use LaravelJsonApi\Core\Query\Input\Query;
use LaravelJsonApi\Core\Query\Input\QueryMany;
use LaravelJsonApi\Core\Query\Input\QueryOne;
use LaravelJsonApi\Core\Query\Input\QueryRelated;
use LaravelJsonApi\Core\Query\Input\QueryRelationship;
use LaravelJsonApi\Core\Query\Input\WillQueryOne;
use LaravelJsonApi\Core\Values\ResourceId;
use LaravelJsonApi\Core\Values\ResourceType;
use LaravelJsonApi\Validation\Pagination\ValidatedPaginator;
use LaravelJsonApi\Validation\ValidatedQuery;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ValidatedQueryTest extends TestCase
{
    /**
     * @var MockObject&SchemaContainer
     */
    private SchemaContainer&MockObject $schemas;

    /**
     * @var Request
     */
    private Request $request;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->schemas = $this->createMock(SchemaContainer::class);
        $this->request = $this->createMock(Request::class);
    }

    /**
     * @return void
     */
    public function testItHasFiltersWithoutRelationship(): void
    {
        $querySchema = $this->createMock(QuerySchema::class);
        $querySchema->expects($this->once())->method('filters')->willReturn(['foo', 'bar']);
        $validatedQuery = new ValidatedQuery($this->schemas, $querySchema, $this->request);

        $this->assertSame(['foo', 'bar'], iterator_to_array($validatedQuery->filters()));
    }

    /**
     * @return void
     */
    public function testItHasFiltersWithRelationship(): void
    {
        $this->schemas
            ->expects($this->once())
            ->method('schemaFor')
            ->with($this->identicalTo($type = new ResourceType('posts')))
            ->willReturn($postsSchema = $this->createMock(Schema::class));

        $postsSchema
            ->expects($this->once())
            ->method('relationship')
            ->with($this->identicalTo('tags'))
            ->willReturn($relation = $this->createMock(Relation::class));

        $relation->expects($this->once())->method('filters')->willReturn(['baz', 'bat']);

        $querySchema = $this->createMock(QuerySchema::class);
        $querySchema->expects($this->once())->method('filters')->willReturn(['foo', 'bar']);
        $validatedQuery = new ValidatedQuery($this->schemas, $querySchema, $this->request);
        $validatedQuery->withRelation($type, 'tags');

        $this->assertSame(['foo', 'bar', 'baz', 'bat'], iterator_to_array($validatedQuery->filters()));
    }

    /**
     * @return void
     */
    public function testItDoesNotHavePaginator(): void
    {
        $querySchema = $this->createMock(QuerySchema::class);
        $querySchema->expects($this->once())->method('pagination')->willReturn(null);
        $validatedQuery = new ValidatedQuery($this->schemas, $querySchema, $this->request);

        $this->assertNull($validatedQuery->pagination());
        $this->assertNull($validatedQuery->pagination()); // test memoization
    }

    /**
     * @return void
     */
    public function testItHasPaginator(): void
    {
        $querySchema = $this->createMock(QuerySchema::class);
        $querySchema
            ->expects($this->once())
            ->method('pagination')
            ->willReturn($pagination = $this->createMock(Paginator::class));

        $validatedQuery = new ValidatedQuery($this->schemas, $querySchema, $this->request);
        $expected = new ValidatedPaginator($pagination, $this->request);
        $actual = $validatedQuery->pagination();

        $this->assertEquals($expected, $actual);
        $this->assertSame($actual, $validatedQuery->pagination()); // test memoization
    }

    /**
     * @return void
     */
    public function testItReturnsMessages(): void
    {
        $querySchema = new class extends TestQuerySchema {
            public function validationMessages(): array
            {
                return ['foo' => 'bar'];
            }
        };

        $validatedQuery = new ValidatedQuery($this->schemas, $querySchema, $this->request);

        $this->assertSame(['foo' => 'bar'], $validatedQuery->messages());
    }

    /**
     * @return void
     */
    public function testItDoesNotReturnMessages(): void
    {
        $validatedQuery = new ValidatedQuery($this->schemas, new TestQuerySchema(), $this->request);

        $this->assertSame([], $validatedQuery->messages());
    }

    /**
     * @return void
     */
    public function testItReturnsAttributes(): void
    {
        $querySchema = new class extends TestQuerySchema {
            public function validationAttributes(): array
            {
                return ['foo' => 'bar'];
            }
        };

        $validatedQuery = new ValidatedQuery($this->schemas, $querySchema, $this->request);

        $this->assertSame(['foo' => 'bar'], $validatedQuery->attributes());
    }

    /**
     * @return void
     */
    public function testItDoesNotReturnAttributes(): void
    {
        $validatedQuery = new ValidatedQuery($this->schemas, new TestQuerySchema(), $this->request);

        $this->assertSame([], $validatedQuery->attributes());
    }

    /**
     * @return array
     */
    public static function queryProvider(): array
    {
        return [
            'query one' => [
                new QueryOne(new ResourceType('posts'), new ResourceId('123')),
            ],
            'will query one' => [
                new WillQueryOne(new ResourceType('posts')),
            ],
            'query many' => [
                new QueryMany(new ResourceType('posts')),
            ],
            'query related' => [
                new QueryRelated(new ResourceType('posts'), new ResourceId('123'), 'tags'),
            ],
            'query relationship' => [
                new QueryRelationship(new ResourceType('posts'), new ResourceId('123'), 'tags'),
            ],
        ];
    }

    /**
     * @return array
     */
    public static function toOneProvider(): array
    {
        return [
            'query one' => [
                new QueryOne(new ResourceType('posts'), new ResourceId('123')),
            ],
            'will query one' => [
                new WillQueryOne(new ResourceType('posts')),
            ],
            'query related' => [
                new QueryRelated(new ResourceType('posts'), new ResourceId('123'), 'author'),
            ],
            'query relationship' => [
                new QueryRelationship(new ResourceType('posts'), new ResourceId('123'), 'author'),
            ],
        ];
    }

    /**
     * @return array
     */
    public static function toManyProvider(): array
    {
        return [
            'query many' => [
                new QueryMany(new ResourceType('posts')),
            ],
            'query related' => [
                new QueryRelated(new ResourceType('posts'), new ResourceId('123'), 'tags'),
            ],
            'query relationship' => [
                new QueryRelationship(new ResourceType('posts'), new ResourceId('123'), 'tags'),
            ],
        ];
    }

    /**
     * @param Query $query
     * @return void
     * @dataProvider queryProvider
     */
    public function testItCallsWithValidator(Query $query): void
    {
        $querySchema = new class extends TestQuerySchema {
            public ?Validator $validator = null;
            public ?Request $request = null;
            public ?Query $query = null;

            public function withValidator($validator, $request, $query): void
            {
                $this->validator = $validator;
                $this->request = $request;
                $this->query = $query;
            }
        };

        $validator = $this->createMock(Validator::class);
        $validatedQuery = new ValidatedQuery($this->schemas, $querySchema, $this->request);
        $validatedQuery->withValidator($validator, $query);

        $this->assertSame($validator, $querySchema->validator);
        $this->assertSame($this->request, $querySchema->request);
        $this->assertSame($query, $querySchema->query);
    }

    /**
     * @param Query $query
     * @return void
     * @dataProvider queryProvider
     */
    public function testItDoesNotCallWithValidator(Query $query): void
    {
        $validator = $this->createMock(Validator::class);
        $validatedQuery = new ValidatedQuery($this->schemas, new TestQuerySchema(), $this->request);

        $validatedQuery->withValidator($validator, $query);
        $this->assertTrue(true);
    }

    /**
     * @param Query $query
     * @return void
     * @dataProvider toOneProvider
     */
    public function testItCallsWithToOneValidator(Query $query): void
    {
        $querySchema = new class extends TestQuerySchema {
            public ?Validator $validator = null;
            public ?Request $request = null;
            public ?Query $query = null;

            public function withToOneValidator($validator, $request, $query): void
            {
                $this->validator = $validator;
                $this->request = $request;
                $this->query = $query;
            }
        };

        $validator = $this->createMock(Validator::class);
        $validatedQuery = new ValidatedQuery($this->schemas, $querySchema, $this->request);
        $validatedQuery->withToOneValidator($validator, $query);

        $this->assertSame($validator, $querySchema->validator);
        $this->assertSame($this->request, $querySchema->request);
        $this->assertSame($query, $querySchema->query);
    }

    /**
     * @param Query $query
     * @return void
     * @dataProvider toOneProvider
     */
    public function testItDoesNotCallWithToOneValidator(Query $query): void
    {
        $validator = $this->createMock(Validator::class);
        $validatedQuery = new ValidatedQuery($this->schemas, new TestQuerySchema(), $this->request);

        $validatedQuery->withToOneValidator($validator, $query);
        $this->assertTrue(true);
    }

    /**
     * @param Query $query
     * @return void
     * @dataProvider toManyProvider
     */
    public function testItCallsWithToManyValidator(Query $query): void
    {
        $querySchema = new class extends TestQuerySchema {
            public ?Validator $validator = null;
            public ?Request $request = null;
            public ?Query $query = null;

            public function withToManyValidator($validator, $request, $query): void
            {
                $this->validator = $validator;
                $this->request = $request;
                $this->query = $query;
            }
        };

        $validator = $this->createMock(Validator::class);
        $validatedQuery = new ValidatedQuery($this->schemas, $querySchema, $this->request);
        $validatedQuery->withToManyValidator($validator, $query);

        $this->assertSame($validator, $querySchema->validator);
        $this->assertSame($this->request, $querySchema->request);
        $this->assertSame($query, $querySchema->query);
    }

    /**
     * @param Query $query
     * @return void
     * @dataProvider toManyProvider
     */
    public function testItDoesNotCallWithToManyValidator(Query $query): void
    {
        $validator = $this->createMock(Validator::class);
        $validatedQuery = new ValidatedQuery($this->schemas, new TestQuerySchema(), $this->request);

        $validatedQuery->withToManyValidator($validator, $query);
        $this->assertTrue(true);
    }

    /**
     * @param Query $query
     * @return void
     * @dataProvider queryProvider
     */
    public function testItCallsAfterValidation(Query $query): void
    {
        $querySchema = new class extends TestQuerySchema {
            public ?Validator $validator = null;
            public ?Request $request = null;
            public ?Query $query = null;

            public function afterValidation($validator, $request, $query): void
            {
                $this->validator = $validator;
                $this->request = $request;
                $this->query = $query;
            }
        };

        $validator = $this->createMock(Validator::class);
        $validatedQuery = new ValidatedQuery($this->schemas, $querySchema, $this->request);
        $validatedQuery->afterValidation($validator, $query);

        $this->assertSame($validator, $querySchema->validator);
        $this->assertSame($this->request, $querySchema->request);
        $this->assertSame($query, $querySchema->query);
    }

    /**
     * @param Query $query
     * @return void
     * @dataProvider queryProvider
     */
    public function testItDoesNotCallAfterValidation(Query $query): void
    {
        $validator = $this->createMock(Validator::class);
        $validatedQuery = new ValidatedQuery($this->schemas, new TestQuerySchema(), $this->request);

        $validatedQuery->afterValidation($validator, $query);
        $this->assertTrue(true);
    }

    /**
     * @param Query $query
     * @return void
     * @dataProvider toOneProvider
     */
    public function testItCallsAfterToOneValidation(Query $query): void
    {
        $querySchema = new class extends TestQuerySchema {
            public ?Validator $validator = null;
            public ?Request $request = null;
            public ?Query $query = null;

            public function afterToOneValidation($validator, $request, $query): void
            {
                $this->validator = $validator;
                $this->request = $request;
                $this->query = $query;
            }
        };

        $validator = $this->createMock(Validator::class);
        $validatedQuery = new ValidatedQuery($this->schemas, $querySchema, $this->request);
        $validatedQuery->afterToOneValidation($validator, $query);

        $this->assertSame($validator, $querySchema->validator);
        $this->assertSame($this->request, $querySchema->request);
        $this->assertSame($query, $querySchema->query);
    }

    /**
     * @param Query $query
     * @return void
     * @dataProvider toOneProvider
     */
    public function testItDoesNotCallAfterToOneValidation(Query $query): void
    {
        $validator = $this->createMock(Validator::class);
        $validatedQuery = new ValidatedQuery($this->schemas, new TestQuerySchema(), $this->request);

        $validatedQuery->afterToOneValidation($validator, $query);
        $this->assertTrue(true);
    }

    /**
     * @param Query $query
     * @return void
     * @dataProvider toManyProvider
     */
    public function testItCallsAfterToManyValidation(Query $query): void
    {
        $querySchema = new class extends TestQuerySchema {
            public ?Validator $validator = null;
            public ?Request $request = null;
            public ?Query $query = null;

            public function afterToManyValidation($validator, $request, $query): void
            {
                $this->validator = $validator;
                $this->request = $request;
                $this->query = $query;
            }
        };

        $validator = $this->createMock(Validator::class);
        $validatedQuery = new ValidatedQuery($this->schemas, $querySchema, $this->request);
        $validatedQuery->afterToManyValidation($validator, $query);

        $this->assertSame($validator, $querySchema->validator);
        $this->assertSame($this->request, $querySchema->request);
        $this->assertSame($query, $querySchema->query);
    }

    /**
     * @param Query $query
     * @return void
     * @dataProvider toManyProvider
     */
    public function testItDoesNotCallAfterToManyValidation(Query $query): void
    {
        $validator = $this->createMock(Validator::class);
        $validatedQuery = new ValidatedQuery($this->schemas, new TestQuerySchema(), $this->request);

        $validatedQuery->afterToManyValidation($validator, $query);
        $this->assertTrue(true);
    }
}
