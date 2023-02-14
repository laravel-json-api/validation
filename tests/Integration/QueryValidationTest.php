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

namespace LaravelJsonApi\Validation\Tests\Integration;

use LaravelJsonApi\Contracts\Pagination\Paginator;
use LaravelJsonApi\Contracts\Routing\Route;
use LaravelJsonApi\Contracts\Schema\Container;
use LaravelJsonApi\Contracts\Schema\Filter;
use LaravelJsonApi\Contracts\Schema\Schema;
use LaravelJsonApi\Contracts\Server\Server;
use LaravelJsonApi\Validation\Rule as JsonApiRule;
use PHPUnit\Framework\MockObject\MockObject;

class QueryValidationTest extends TestCase
{

    /**
     * @var Schema|MockObject
     */
    private Schema $schema;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->schema = $this->createMock(TestSchema::class);
        $this->schema->method('sparseFields')->willReturn(['author', 'createdAt', 'title', 'updatedAt']);
        $this->schema->method('filters')->willReturn([
            $filter = $this->createMock(Filter::class),
        ]);
        $this->schema->method('includePaths')->willReturn(['author']);
        $this->schema->method('pagination')->willReturn(
            $paginator = $this->createMock(Paginator::class)
        );
        $this->schema->method('sortFields')->willReturn(['createdAt', 'title', 'updatedAt']);
        $this->schema->method('countable')->willReturn(['comments', 'likes', 'tags']);

        $filter->method('key')->willReturn('title');
        $paginator->method('keys')->willReturn(['number', 'size']);

        $this->app->instance(Route::class, $route = $this->createMock(Route::class));
        $route->method('schema')->willReturn($this->schema);

        $this->app->instance(Server::class, $server = $this->createMock(Server::class));
        $server->method('schemas')->willReturn($schemas = $this->createMock(Container::class));
        $schemas->method('schemaFor')->with('posts')->willReturn($this->schema);
        $schemas->method('exists')->willReturnCallback(fn($value) => 'posts' === $value);
    }

    /**
     * @return array
     */
    public function validProvider(): array
    {
        return [
            'all' => [
                [
                    'fields' => [
                        'posts' => 'title,author',
                    ],
                    'filter' => [
                        'title' => 'Hello*',
                    ],
                    'include' => 'author',
                    'page' => [
                        'number' => '1',
                        'size' => '25',
                    ],
                    'sort' => 'title,createdAt',
                    'withCount' => 'comments,tags',
                ],
            ],
            'fields:null' => [
                [
                    'fields' => [
                        'posts' => null,
                    ],
                ],
            ],
            'fields:empty' => [
                [
                    'fields' => [
                        'posts' => '',
                    ],
                ],
            ],
            'include:empty' => [
                [
                    'include' => '',
                ],
            ],
            'sort:empty' => [
                [
                    'sort' => '',
                ],
            ],
            'withCount:empty' => [
                [
                    'withCount' => '',
                ],
            ],
        ];
    }

    /**
     * @param array $data
     * @return void
     * @dataProvider validProvider
     */
    public function testValid(array $data): void
    {
        $validator = $this->validatorFactory->make($data, [
            'fields' => [
                'nullable',
                'array',
                JsonApiRule::fieldSets(),
            ],
            'filter' => [
                'nullable',
                'array',
                JsonApiRule::filter(),
            ],
            'include' => [
                'nullable',
                'string',
                JsonApiRule::includePaths(),
            ],
            'page' => [
                'nullable',
                'array',
                JsonApiRule::page(),
            ],
            'sort' => [
                'nullable',
                'string',
                JsonApiRule::sort(),
            ],
            'withCount' => [
                'nullable',
                'string',
                JsonApiRule::countable(),
            ],
        ]);

        $this->assertFalse($validator->fails());
    }

    /**
     * @return array
     */
    public function invalidProvider(): array
    {
        return [
            'fields:not array' => [
                'fields',
                'blah',
                'The fields field must be an array.',
                'fields',
                ['rule' => 'array'],
            ],
            'fields:singular' => [
                'fields',
                ['posts' => 'title,content'],
                'Sparse field set posts.content is not allowed.',
                'fields',
                ['rule' => 'allowed-field-sets'],
            ],
            'fields:plural' => [
                'fields',
                ['posts' => 'foo,content,title'],
                'Sparse field sets posts.content, posts.foo are not allowed.',
                'fields',
                ['rule' => 'allowed-field-sets'],
            ],
            'fields:unrecognised.singular' => [
                'fields',
                ['foo' => 'bar', 'posts' => 'title'],
                'Resource type foo is not recognised.',
                'fields',
                ['rule' => 'allowed-field-sets'],
            ],
            'fields:unrecognised.plural' => [
                'fields',
                ['foo' => 'bar', 'baz' => 'bat', 'posts' => 'title'],
                'Resource types baz, foo are not recognised.',
                'fields',
                ['rule' => 'allowed-field-sets'],
            ],
            'filter:not array' => [
                'filter',
                'blah',
                'The filter field must be an array.',
                'filter',
                ['rule' => 'array'],
            ],
            'filter:singular' => [
                'filter',
                ['foo' => 'bar'],
                'Filter parameter foo is not allowed.',
                'filter',
                ['rule' => 'allowed-filter-parameters'],
            ],
            'filter:plural' => [
                'filter',
                ['foo' => 'bar', 'baz' => 'bat'],
                'Filter parameters baz, foo are not allowed.',
                'filter',
                ['rule' => 'allowed-filter-parameters'],
            ],
            'include:not string' => [
                'include',
                ['foo' => 'bar'],
                'The include field must be a string.',
                'include',
                ['rule' => 'string'],
            ],
            'include:singular' => [
                'include',
                'author,foo',
                'Include path foo is not allowed.',
                'include',
                ['rule' => 'allowed-include-paths'],
            ],
            'include:plural' => [
                'include',
                'author,foo,bar',
                'Include paths bar, foo are not allowed.',
                'include',
                ['rule' => 'allowed-include-paths'],
            ],
            'page:not array' => [
                'page',
                'blah',
                'The page field must be an array.',
                'page',
                ['rule' => 'array'],
            ],
            'page:singular' => [
                'page',
                ['number' => '1', 'size' => '25', 'foo' => 'bar'],
                'Page parameter foo is not allowed.',
                'page',
                ['rule' => 'allowed-page-parameters'],
            ],
            'page:plural' => [
                'page',
                ['number' => '1', 'size' => '25', 'foo' => 'bar', 'baz' => 'bat'],
                'Page parameters baz, foo are not allowed.',
                'page',
                ['rule' => 'allowed-page-parameters'],
            ],
            'sort:not string' => [
                'sort',
                ['foo' => 'bar'],
                'The sort field must be a string.',
                'sort',
                ['rule' => 'string'],
            ],
            'sort:singular' => [
                'sort',
                'title,createdAt,foo',
                'Sort parameter foo is not allowed.',
                'sort',
                ['rule' => 'allowed-sort-parameters'],
            ],
            'sort:plural' => [
                'sort',
                'title,foo,createdAt,bar',
                'Sort parameters bar, foo are not allowed.',
                'sort',
                ['rule' => 'allowed-sort-parameters'],
            ],
            'withCount:not string' => [
                'withCount',
                ['foo' => 'bar'],
                'The with count field must be a string.',
                'withCount',
                ['rule' => 'string'],
            ],
            'withCount:singular' => [
                'withCount',
                'comments,foo,tags',
                'Field foo is not countable.',
                'withCount',
                ['rule' => 'allowed-countable-fields'],
            ],
            'withCount:plural' => [
                'withCount',
                'comments,foo,tags,bar',
                'Fields bar, foo are not countable.',
                'withCount',
                ['rule' => 'allowed-countable-fields'],
            ],
        ];
    }

    /**
     * @param string $key
     * @param $value
     * @param string $detail
     * @param string $parameter
     * @param array $failed
     * @dataProvider invalidProvider
     */
    public function testInvalid(string $key, $value, string $detail, string $parameter, array $failed): void
    {
        $data = [
            'fields' => [
                'posts' => 'title,author',
            ],
            'filter' => [
                'title' => 'Hello*',
            ],
            'include' => 'author',
            'page' => [
                'number' => '1',
                'size' => '25',
            ],
            'sort' => 'title,createdAt',
        ];

        $data[$key] = $value;

        $validator = $this->validatorFactory->make($data, [
            'fields' => [
                'nullable',
                'array',
                JsonApiRule::fieldSets(),
            ],
            'filter' => [
                'nullable',
                'array',
                JsonApiRule::filter(),
            ],
            'include' => [
                'nullable',
                'string',
                JsonApiRule::includePaths(),
            ],
            'page' => [
                'nullable',
                'array',
                JsonApiRule::page(),
            ],
            'sort' => [
                'nullable',
                'string',
                JsonApiRule::sort(),
            ],
            'withCount' => [
                'nullable',
                'string',
                JsonApiRule::countable(),
            ],
        ]);

        $this->assertTrue($validator->fails());

        $errors = $this->factory->createErrorsForQuery($validator);

        $this->assertCount(1, $errors);
        $this->assertSame([
            'detail' => $detail,
            'source' => ['parameter' => $parameter],
            'status' => '400',
            'title' => 'Invalid Query Parameter',
        ], $errors->first()->jsonSerialize());

        $errors->withFailureMeta();

        $this->assertSame([
            'detail' => $detail,
            'meta' => compact('failed'),
            'source' => ['parameter' => $parameter],
            'status' => '400',
            'title' => 'Invalid Query Parameter',
        ], $errors->first()->jsonSerialize());
    }

    public function testNotSupported(): void
    {
        $data = ['filter' => ['id' => '1,2,3']];

        $validator = $this->validatorFactory->make($data, [
            'filter' => JsonApiRule::notSupported(),
        ]);

        $this->assertTrue($validator->fails());

        $errors = $this->factory->createErrorsForQuery($validator);

        $this->assertCount(1, $errors);

        $this->assertSame([
            'detail' => 'Parameter filter is not allowed.',
            'source' => ['parameter' => 'filter'],
            'status' => '400',
            'title' => 'Invalid Query Parameter',
        ], $errors->first()->jsonSerialize());

        $errors->withFailureMeta();

        $this->assertSame([
            'detail' => 'Parameter filter is not allowed.',
            'meta' => [
                'failed' => [
                    'rule' => 'parameter-not-supported',
                ],
            ],
            'source' => ['parameter' => 'filter'],
            'status' => '400',
            'title' => 'Invalid Query Parameter',
        ], $errors->first()->jsonSerialize());
    }

}
