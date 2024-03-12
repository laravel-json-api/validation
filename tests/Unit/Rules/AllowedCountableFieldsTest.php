<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Validation\Tests\Unit\Rules;

use LaravelJsonApi\Contracts\Schema\Container;
use LaravelJsonApi\Contracts\Schema\Relation;
use LaravelJsonApi\Contracts\Schema\Schema;
use LaravelJsonApi\Validation\Rules\AllowedCountableFields;
use LaravelJsonApi\Validation\Tests\Integration\TestSchema;
use PHPUnit\Framework\TestCase;

class AllowedCountableFieldsTest extends TestCase
{

    public function test(): void
    {
        $rule = new AllowedCountableFields(['comments', 'tags']);

        $this->assertTrue($rule->passes('withCount', 'comments,tags'));
        $this->assertFalse($rule->passes('withCount', 'comments,baz'));
    }

    public function testWithEmptyString(): void
    {
        $rule = new AllowedCountableFields(['comments', 'tags']);

        $this->assertTrue($rule->passes('withCount', ''));
    }

    public function testWithMethods(): void
    {
        $rule = (new AllowedCountableFields())
            ->allow('comments', 'tags', 'foobar')
            ->forget('foobar');

        $this->assertTrue($rule->passes('withCount', 'comments,tags'));
        $this->assertFalse($rule->passes('withCount', 'comments,foobar'));
    }

    public function testSchema(): void
    {
        $schema = $this->createMock(TestSchema::class);
        $schema->method('countable')->willReturn(['comments', 'tags']);

        $this->assertEquals(
            new AllowedCountableFields(['comments', 'tags']),
            AllowedCountableFields::make($schema)
        );
    }

    public function testMorphTo(): void
    {
        $relation = $this->createMock(Relation::class);
        $relation->method('allInverse')->willReturn(['comments', 'likes', 'tags']);

        $schemas = $this->createMock(Container::class);
        $schemas->method('schemaFor')->willReturnMap([
            ['comments', $comments = $this->createMock(TestSchema::class)],
            ['likes', $likes = $this->createMock(Schema::class)], // not countable
            ['tags', $tags = $this->createMock(TestSchema::class)],
        ]);

        $comments->method('countable')->willReturn(['foo', 'bar', 'baz']);
        $tags->method('countable')->willReturn(['baz', 'bat', 'foobar']);
        $likes->expects($this->never())->method($this->anything());

        $expected = new AllowedCountableFields(['foo', 'bar', 'baz', 'bat', 'foobar']);

        $this->assertEquals($expected, AllowedCountableFields::morphMany($schemas, $relation));
    }

}
