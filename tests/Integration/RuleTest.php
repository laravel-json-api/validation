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
use LaravelJsonApi\Contracts\Schema\Relation;
use LaravelJsonApi\Contracts\Schema\Schema;
use LaravelJsonApi\Contracts\Server\Server;
use LaravelJsonApi\Validation\Rule;
use LaravelJsonApi\Validation\Rules\AllowedFieldSets;
use LaravelJsonApi\Validation\Rules\AllowedFilterParameters;
use LaravelJsonApi\Validation\Rules\AllowedIncludePaths;
use LaravelJsonApi\Validation\Rules\AllowedPageParameters;
use LaravelJsonApi\Validation\Rules\AllowedSortParameters;
use LaravelJsonApi\Validation\Rules\ClientId;
use LaravelJsonApi\Validation\Rules\DateTimeIso8601;
use LaravelJsonApi\Validation\Rules\HasMany;
use LaravelJsonApi\Validation\Rules\HasOne;
use LaravelJsonApi\Validation\Rules\JsonBoolean;
use LaravelJsonApi\Validation\Rules\JsonNumber;
use LaravelJsonApi\Validation\Rules\ParameterNotSupported;
use PHPUnit\Framework\MockObject\MockObject;

class RuleTest extends TestCase
{

    /**
     * @var Route|MockObject
     */
    private Route $route;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->instance(Route::class, $this->route = $this->createMock(Route::class));
    }

    public function testDateTime(): void
    {
        $this->assertEquals(new DateTimeIso8601(), Rule::dateTime());
    }

    public function testFieldSets(): void
    {
        $this->willNotCallRoute();

        $this->assertEquals(
            new AllowedFieldSets($expected = ['posts' => ['title', 'author']]),
            Rule::fieldSets($expected)
        );
    }

    public function testFieldSetsWithSchemas(): void
    {
        $this->willNotCallRoute();

        $this->app->instance(Server::class, $server = $this->createMock(Server::class));
        $server->method('schemas')->willReturn($schemas = $this->createMock(Container::class));

        $this->assertEquals(
            AllowedFieldSets::make($schemas),
            Rule::fieldSets()
        );
    }

    /**
     * @return array
     */
    public static function filterProvider(): array
    {
        return [
            ['foo', ['foo']],
            [['foo', 'bar'], ['foo', 'bar']],
            [[], []],
        ];
    }

    /**
     * @param $filters
     * @param array $expected
     * @dataProvider filterProvider
     */
    public function testFilter($filters, array $expected): void
    {
        $this->willNotCallRoute();

        $this->assertEquals(
            new AllowedFilterParameters($expected),
            Rule::filter($filters)
        );
    }

    public function testFilterWithoutRelation(): void
    {
        $this->route->expects($this->once())->method('hasRelation')->willReturn(false);
        $this->route->expects($this->once())->method('schema')->willReturn(
            $schema = $this->createMock(Schema::class)
        );
        $this->route->expects($this->never())->method('inverse');
        $this->route->expects($this->never())->method('relation');

        $schema->method('filters')->willReturn([
            $a = $this->createMock(Filter::class),
            $b = $this->createMock(Filter::class),
        ]);

        $a->method('key')->willReturn('foo');
        $b->method('key')->willReturn('bar');

        $this->assertEquals(
            new AllowedFilterParameters(['foo', 'bar']),
            Rule::filter()
        );
    }

    public function testFilterWithRelation(): void
    {
        $this->route->expects($this->once())->method('hasRelation')->willReturn(true);
        $this->route->expects($this->never())->method('schema');
        $this->route->expects($this->once())->method('inverse')->willReturn(
            $schema = $this->createMock(Schema::class)
        );
        $this->route->expects($this->once())->method('relation')->willReturn(
            $relation = $this->createMock(Relation::class)
        );

        $schema->method('filters')->willReturn([
            $a = $this->createMock(Filter::class),
            $b = $this->createMock(Filter::class),
        ]);

        $relation->method('filters')->willReturn([
            $c = $this->createMock(Filter::class)
        ]);

        $a->method('key')->willReturn('foo');
        $b->method('key')->willReturn('bar');
        $c->method('key')->willReturn('baz');

        $this->assertEquals(
            new AllowedFilterParameters(['foo', 'bar', 'baz']),
            Rule::filter()
        );
    }

    /**
     * @return array
     */
    public static function includePathsProvider(): array
    {
        return [
            ['foo', ['foo']],
            [['foo', 'bar'], ['foo', 'bar']],
            [[], []]
        ];
    }

    /**
     * @param $paths
     * @param array $expected
     * @dataProvider includePathsProvider
     */
    public function testIncludePaths($paths, array $expected): void
    {
        $this->willNotCallRoute();

        $this->assertEquals(
            new AllowedIncludePaths($expected),
            Rule::includePaths($paths)
        );
    }

    public function testIncludePathsWithoutRelation(): void
    {
        $this->route->expects($this->once())->method('hasRelation')->willReturn(false);
        $this->route->expects($this->once())->method('schema')->willReturn(
            $schema = $this->createMock(Schema::class)
        );
        $this->route->expects($this->never())->method('inverse');

        $schema->method('includePaths')->willReturn(['foo', 'bar']);

        $this->assertEquals(
            new AllowedIncludePaths(['foo', 'bar']),
            Rule::includePaths()
        );
    }

    public function testIncludePathsWithRelation(): void
    {
        $this->route->expects($this->once())->method('hasRelation')->willReturn(true);
        $this->route->expects($this->never())->method('schema');
        $this->route->expects($this->once())->method('inverse')->willReturn(
            $schema = $this->createMock(Schema::class)
        );

        $schema->method('includePaths')->willReturn(['foo', 'bar']);

        $this->assertEquals(
            new AllowedIncludePaths(['foo', 'bar']),
            Rule::includePaths()
        );
    }

    public function testIncludePathsForPolymorph(): void
    {
        $this->route->method('hasRelation')->willReturn(true);
        $this->route->method('relation')->willReturn($relation = $this->createMock(Relation::class));

        $relation->method('allInverse')->willReturn(['foo', 'bar']);

        $this->app->instance(Server::class, $server = $this->createMock(Server::class));
        $server->method('schemas')->willReturn($schemas = $this->createMock(Container::class));

        $schemas->method('schemaFor')->willReturnMap([
            ['foo', $foo = $this->createMock(Schema::class)],
            ['bar', $bar = $this->createMock(Schema::class)],
        ]);

        $foo->method('includePaths')->willReturn((function () {
            yield from ['foo.bar', 'baz.bat', 'foobar.bazbat'];
        })());

        $bar->method('includePaths')->willReturn(['bar.baz', 'bar.bat', 'foobar.bazbat']);

        $this->assertEquals(
            new AllowedIncludePaths(['foo.bar', 'baz.bat', 'foobar.bazbat', 'bar.baz', 'bar.bat']),
            Rule::includePathsForPolymorph()
        );
    }

    /**
     * @return array
     */
    public static function notSupportedProvider(): array
    {
        return [
            [null],
            ['foo'],
        ];
    }

    /**
     * @param $value
     * @dataProvider notSupportedProvider
     */
    public function testNotSupported($value): void
    {
        $this->willNotCallRoute();

        $this->assertEquals(
            new ParameterNotSupported($value),
            Rule::notSupported($value)
        );
    }

    /**
     * @return array
     */
    public static function pageProvider(): array
    {
        return [
            ['number', ['number']],
            [['number', 'size'], ['number', 'size']],
            [[], []],
        ];
    }

    /**
     * @param $value
     * @param array $expected
     * @dataProvider pageProvider
     */
    public function testPage($value, array $expected): void
    {
        $this->willNotCallRoute();

        $this->assertEquals(
            new AllowedPageParameters($expected),
            Rule::page($value)
        );
    }

    public function testPageWithoutRelation(): void
    {
        $this->route->expects($this->once())->method('hasRelation')->willReturn(false);
        $this->route->expects($this->once())->method('schema')->willReturn(
            $schema = $this->createMock(Schema::class)
        );
        $this->route->expects($this->never())->method('inverse');

        $schema->method('pagination')->willReturn($paginator = $this->createMock(Paginator::class));
        $paginator->method('keys')->willReturn(['number', 'size']);

        $this->assertEquals(
            new AllowedPageParameters(['number', 'size']),
            Rule::page()
        );
    }

    public function testPageWithRelation(): void
    {
        $this->route->expects($this->once())->method('hasRelation')->willReturn(true);
        $this->route->expects($this->never())->method('schema');
        $this->route->expects($this->once())->method('inverse')->willReturn(
            $schema = $this->createMock(Schema::class)
        );

        $schema->method('pagination')->willReturn($paginator = $this->createMock(Paginator::class));
        $paginator->method('keys')->willReturn(['number', 'size']);

        $this->assertEquals(
            new AllowedPageParameters(['number', 'size']),
            Rule::page()
        );
    }

    /**
     * @return array
     */
    public static function sortProvider(): array
    {
        return [
            ['createdAt', ['createdAt']],
            [['createdAt', 'title'], ['createdAt', 'title']],
            [[], []],
        ];
    }

    /**
     * @param $value
     * @param array $expected
     * @dataProvider sortProvider
     */
    public function testSort($value, array $expected): void
    {
        $this->willNotCallRoute();

        $this->assertEquals(
            new AllowedSortParameters($expected),
            Rule::sort($value)
        );
    }

    public function testSortWithoutRelation(): void
    {
        $this->route->expects($this->once())->method('hasRelation')->willReturn(false);
        $this->route->expects($this->once())->method('schema')->willReturn(
            $schema = $this->createMock(Schema::class)
        );
        $this->route->expects($this->never())->method('inverse');

        $schema->method('sortFields')->willReturn([
            'createdAt',
            'title',
        ]);

        $this->assertEquals(
            new AllowedSortParameters(['createdAt', 'title']),
            Rule::sort()
        );
    }

    public function testSortWithRelation(): void
    {
        $this->route->expects($this->once())->method('hasRelation')->willReturn(true);
        $this->route->expects($this->never())->method('schema');
        $this->route->expects($this->once())->method('inverse')->willReturn(
            $schema = $this->createMock(Schema::class)
        );

        $schema->method('sortFields')->willReturn([
            'createdAt',
            'title',
        ]);

        $this->assertEquals(
            new AllowedSortParameters(['createdAt', 'title']),
            Rule::sort()
        );
    }

    public function testToMany(): void
    {
        $this->route->expects($this->once())->method('schema')->willReturn(
            $schema = $this->createMock(Schema::class)
        );

        $this->assertEquals(new HasMany($schema), Rule::toMany());
    }

    public function testToOne(): void
    {
        $this->route->expects($this->once())->method('schema')->willReturn(
            $schema = $this->createMock(Schema::class)
        );

        $this->assertEquals(new HasOne($schema), Rule::toOne());
    }

    public function testClientId(): void
    {
        $this->route->expects($this->once())->method('schema')->willReturn(
            $schema = $this->createMock(Schema::class)
        );

        $this->assertEquals(new ClientId($schema), Rule::clientId());
    }

    public function testBoolean(): void
    {
        $this->assertEquals(new JsonBoolean(), Rule::boolean());
    }

    public function testNumber(): void
    {
        $this->assertEquals(new JsonNumber(), Rule::number());
    }

    public function testInteger(): void
    {
        $expected = (new JsonNumber())->onlyIntegers();

        $this->assertEquals($expected, Rule::integer());
    }

    /**
     * @return void
     */
    private function willNotCallRoute(): void
    {
        $this->route->expects($this->never())->method($this->anything());
    }
}
