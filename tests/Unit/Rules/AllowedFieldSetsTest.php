<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

namespace LaravelJsonApi\Validation\Tests\Unit\Rules;

use LaravelJsonApi\Contracts\Schema\Container;
use LaravelJsonApi\Contracts\Schema\Schema;
use LaravelJsonApi\Validation\Rules\AllowedFieldSets;
use PHPUnit\Framework\TestCase;

class AllowedFieldSetsTest extends TestCase
{

    /**
     * @return array
     */
    public static function allowedProvider(): array
    {
        return [
            'valid' => [
                ['posts' => 'title,author', 'users' => 'name', 'tags' => 'title'],
                true,
            ],
            'invalid resource type ' => [
                ['posts' => 'title', 'comments' => 'user'],
                false,
            ],
            'invalid resource field' => [
                ['posts' => 'title,foo,content'],
                false,
            ],
        ];
    }

    /**
     * @param array $fields
     * @param bool $expected
     * @dataProvider allowedProvider
     */
    public function test(array $fields, bool $expected): void
    {
        $rule = new AllowedFieldSets([
            'posts' => ['title', 'content', 'author'],
            'users' => 'name',
            'tags' => null, // any allowed
            'countries' => [], // none allowed
        ]);

        $this->assertSame($expected, $rule->passes('fields', $fields));
    }

    /**
     * @param array $fields
     * @param bool $expected
     * @dataProvider allowedProvider
     */
    public function testWithMethods(array $fields, bool $expected): void
    {
        $rule = (new AllowedFieldSets())
            ->allow('posts', ['title', 'content', 'author'])
            ->allow('users', ['name'])
            ->any('tags')
            ->none('countries');

        $this->assertSame($expected, $rule->passes('fields', $fields));
    }

    /**
     * @return array[]
     */
    public static function schemaProvider(): array
    {
        return [
            [
                ['posts' => 'title,author', 'users' => 'name'],
                true,
            ],
            [
                ['posts' => 'title,foo', 'users' => 'name'],
                false,
            ],
            [
                ['posts' => 'title,author', 'users' => 'foo'],
                false,
            ],
            [
                ['posts' => 'title,author', 'comments' => 'content'],
                false,
            ],
        ];
    }

    /**
     * @param array $fields
     * @param bool $expected
     * @dataProvider schemaProvider
     */
    public function testUsesSchemas(array $fields, bool $expected): void
    {
        $posts = $this->createMock(Schema::class);
        $posts->method('sparseFields')->willReturn(['title', 'content', 'author']);

        $users = $this->createMock(Schema::class);
        $users->method('sparseFields')->willReturn(['name', 'email']);

        $schemas = $this->createMock(Container::class);
        $schemas->method('exists')->willReturnCallback(fn ($value) => in_array($value, ['posts', 'users']));
        $schemas->method('schemaFor')->willReturnMap([
            ['posts', $posts],
            ['users', $users],
        ]);

        $rule = AllowedFieldSets::make($schemas);

        $this->assertSame($expected, $rule->passes('fields', $fields));
    }

}
