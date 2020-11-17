<?php
/*
 * Copyright 2020 Cloud Creativity Limited
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

use LaravelJsonApi\Contracts\Routing\Route;
use LaravelJsonApi\Contracts\Schema\Attribute;
use LaravelJsonApi\Contracts\Schema\Relation;
use LaravelJsonApi\Contracts\Schema\Schema;
use LaravelJsonApi\Validation\Rule;
use PHPUnit\Framework\MockObject\MockObject;

class ResourceValidationTest extends TestCase
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

        $this->schema = $this->createMock(Schema::class);

        $this->schema->method('field')->willReturnMap([
            ['title', $this->createAttribute('title')],
            ['content', $this->createAttribute('content')],
            ['publishedAt', $this->createAttribute('publishedAt')],
            ['author', $author = $this->createRelation('author', 'users')],
            ['tags', $tags = $this->createRelation('tags', 'tags')],
        ]);

        $this->schema->method('isAttribute')->willReturnCallback(
            fn($value) => in_array($value, ['title', 'content', 'publishedAt'])
        );

        $this->schema->method('isRelationship')->willReturnCallback(
            fn($value) => in_array($value, ['author', 'tags'])
        );

        $this->schema->method('relationship')->willReturnMap([
            ['author', $author],
            ['tags', $tags],
        ]);

        $this->app->instance(Route::class, $route = $this->createMock(Route::class));
        $route->method('schema')->willReturn($this->schema);
    }

    public function testValid(): void
    {
        $data = [
            'title' => 'Hello World!',
            'content' => '...',
            'publishedAt' => '2020-11-16T12:00:00.123456+06:00',
            'author' => [
                'type' => 'users',
                'id' => '123',
            ],
            'tags' => [
                [
                    'type' => 'tags',
                    'id' => '456',
                ],
            ],
        ];

        $validator = $this->validatorFactory->make($data, [
            'title' => ['required', 'string', 'min:3'],
            'content' => ['required', 'string'],
            'publishedAt' => ['required', Rule::dateTime()],
            'author' => ['required', Rule::toOne()],
            'tags' => Rule::toMany(),
        ]);

        $this->assertFalse($validator->fails());
    }

    /**
     * @return array
     */
    public function invalidProvider(): array
    {
        return [
            [
                'title',
                'HW',
                'The title must be at least 3 characters.',
                '/data/attributes/title',
                ['rule' => 'min', 'options' => ['3']],
            ],
            [
                'publishedAt',
                '2020-11-16',
                'The published at is not a valid ISO 8601 date and time.',
                '/data/attributes/publishedAt',
                ['rule' => 'date-time-iso8601'],
            ],
            [
                'author',
                ['type' => 'comments', 'id' => '123'],
                'The author field must be a to-one relationship containing users resources.',
                '/data/relationships/author',
                ['rule' => 'has-one'],
            ],
            [
                'tags',
                [
                    [
                        'type' => 'tags',
                        'id' => '123',
                    ],
                    [
                        'type' => 'comments',
                        'id' => '123',
                    ],
                ],
                'The tags field must be a to-many relationship containing tags resources.',
                '/data/relationships/tags',
                ['rule' => 'has-many'],
            ],
        ];
    }

    /**
     * @param string $key
     * @param $value
     * @param string $detail
     * @param string $pointer
     * @param array $failed
     * @dataProvider invalidProvider
     */
    public function testInvalid(string $key, $value, string $detail, string $pointer, array $failed): void
    {
        $data = [
            'title' => 'Hello World!',
            'content' => '...',
            'publishedAt' => '2020-11-16T12:00:00.123456+06:00',
            'author' => [
                'type' => 'users',
                'id' => '123',
            ],
            'tags' => [
                [
                    'type' => 'tags',
                    'id' => '456',
                ],
            ],
        ];

        $data[$key] = $value;

        $validator = $this->validatorFactory->make($data, [
            'title' => ['required', 'string', 'min:3'],
            'content' => ['required', 'string'],
            'publishedAt' => ['required', Rule::dateTime()],
            'author' => ['required', Rule::toOne()],
            'tags' => Rule::toMany(),
        ]);

        $this->assertTrue($validator->fails());

        $errors = $this->factory->createErrorsForResource($this->schema, $validator);

        $this->assertCount(1, $errors);
        $this->assertSame([
            'detail' => $detail,
            'source' => ['pointer' => $pointer],
            'status' => '422',
            'title' => 'Unprocessable Entity',
        ], $errors->first()->jsonSerialize());

        $errors->withFailureMeta();

        $this->assertSame([
            'detail' => $detail,
            'meta' => compact('failed'),
            'source' => ['pointer' => $pointer],
            'status' => '422',
            'title' => 'Unprocessable Entity',
        ], $errors->first()->jsonSerialize());
    }

    /**
     * @param string $fieldName
     * @return Attribute
     */
    private function createAttribute(string $fieldName): Attribute
    {
        $mock = $this->createMock(Attribute::class);
        $mock->method('name')->willReturn($fieldName);

        return $mock;
    }

    /**
     * @param string $fieldName
     * @param string $inverse
     * @return Relation
     */
    private function createRelation(string $fieldName, string $inverse): Relation
    {
        $mock = $this->createMock(Relation::class);
        $mock->method('name')->willReturn($fieldName);
        $mock->method('inverse')->willReturn($inverse);

        return $mock;
    }
}
