<?php
/*
 * Copyright 2022 Cloud Creativity Limited
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

use LaravelJsonApi\Contracts\Schema\Schema;
use LaravelJsonApi\Validation\Rules\AllowedIncludePaths;
use PHPUnit\Framework\TestCase;

class AllowedIncludePathsTest extends TestCase
{

    public function test(): void
    {
        $rule = new AllowedIncludePaths(['foo', 'bar']);

        $this->assertTrue($rule->passes('include', 'foo,bar'));
        $this->assertFalse($rule->passes('include', 'foo,baz'));
    }

    public function testWithMethods(): void
    {
        $rule = (new AllowedIncludePaths())
            ->allow('foo', 'bar', 'id')
            ->forget('id');

        $this->assertTrue($rule->passes('include', 'foo,bar'));
        $this->assertFalse($rule->passes('include', 'foo,id'));
    }

    public function testSchema(): void
    {
        $schema = $this->createMock(Schema::class);
        $schema->method('includePaths')->willReturn(['author', 'comments']);

        $this->assertEquals(
            new AllowedIncludePaths(['author', 'comments']),
            AllowedIncludePaths::make($schema)
        );
    }

}
