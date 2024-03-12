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

use LaravelJsonApi\Contracts\Schema\Attribute;
use LaravelJsonApi\Contracts\Schema\Relation;
use LaravelJsonApi\Contracts\Schema\Schema;
use LaravelJsonApi\Validation\SchemaSourcePointer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SchemaSourcePointerTest extends TestCase
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
            ['author', $this->createRelation('author')],
            ['tags', $this->createRelation('tags')],
            ['comments', $this->createRelation('comments')],
        ]);

        $this->schema->method('isAttribute')->willReturnCallback(
            fn($value) => in_array($value, ['title'])
        );

        $this->schema->method('isRelationship')->willReturnCallback(
            fn($value) => in_array($value, ['author', 'tags', 'comments'])
        );
    }

    /**
     * @return array
     */
    public static function keyProvider(): array
    {
        return [
            ['type', '/type'],
            ['id', '/id'],
            ['title', '/attributes/title'],
            ['title.foo.bar', '/attributes/title/foo/bar'],
            ['author', '/relationships/author'],
            ['author.type', '/relationships/author/data/type'],
            ['tags.0.id', '/relationships/tags/data/0/id'],
            ['comments', '/relationships/comments'],
            ['foo', '/'],
        ];
    }

    /**
     * @param string $key
     * @param string $expected
     * @dataProvider keyProvider
     */
    public function test(string $key, string $expected): void
    {
        $this->assertSame(
            ['pointer' => $expected],
            SchemaSourcePointer::make($this->schema, $key)->jsonSerialize()
        );
    }

    /**
     * @param string $key
     * @param string $expected
     * @dataProvider keyProvider
     */
    public function testPointerWithPrefix(string $key, string $expected): void
    {
        // @see https://github.com/cloudcreativity/laravel-json-api/issues/255
        $expected = rtrim("/data" . $expected, '/');

        $actual = SchemaSourcePointer::make($this->schema, $key)
            ->withPrefix('/data')
            ->toString();

        $this->assertSame($expected, $actual);
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
     * @return Relation
     */
    private function createRelation(string $fieldName): Relation
    {
        $mock = $this->createMock(Relation::class);
        $mock->method('name')->willReturn($fieldName);

        return $mock;
    }
}
