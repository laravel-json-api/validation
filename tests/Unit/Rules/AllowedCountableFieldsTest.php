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

        $this->assertTrue($rule->passes('include', 'comments,tags'));
        $this->assertFalse($rule->passes('include', 'comments,baz'));
    }

    public function testWithMethods(): void
    {
        $rule = (new AllowedCountableFields())
            ->allow('comments', 'tags', 'foobar')
            ->forget('foobar');

        $this->assertTrue($rule->passes('include', 'comments,tags'));
        $this->assertFalse($rule->passes('include', 'comments,foobar'));
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
