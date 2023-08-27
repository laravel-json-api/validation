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

namespace LaravelJsonApi\Validation\Tests\Unit\Fields;

use Illuminate\Http\Request;
use LaravelJsonApi\Contracts\Schema\Field;
use LaravelJsonApi\Validation\Fields\IsValidated;
use LaravelJsonApi\Validation\Fields\ListOfFields;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ListOfFieldsTest extends TestCase
{
    /**
     * @return void
     */
    public function testItReturnsRulesForCreate(): void
    {
        $request = $this->createMock(Request::class);

        $notValidated = $this->createField('foo');
        $title = $this->createValidatedField('title');
        $publishedAt = $this->createValidatedField('publishedAt');
        $author = $this->createValidatedField('author');
        $tags = $this->createValidatedField('tags');
        $noRules1 = $this->createValidatedField('bar');
        $noRules2 = $this->createValidatedField('baz');
        $noRules3 = $this->createValidatedField('bat');

        $expected = [
            'title' => ['required', 'string', 'min:3'],
            'publishedAt' => ['nullable', 'date'],
            'author' => 'array:name,username',
            'author.name' => ['required', 'string'],
            'author.username' => ['required', 'string', 'email'],
            'tags' => 'array',
            'tags.*' => ['string', 'min:1'],
        ];

        $title
            ->expects($this->once())
            ->method('rulesForCreate')
            ->with($this->identicalTo($request))
            ->willReturn($expected['title']);

        $publishedAt
            ->expects($this->once())
            ->method('rulesForCreate')
            ->with($this->identicalTo($request))
            ->willReturn(function ($r, $m) use ($request, $expected): array {
                $this->assertSame($request, $r);
                $this->assertNull($m);
                return $expected['publishedAt'];
            });

        $author
            ->expects($this->once())
            ->method('rulesForCreate')
            ->with($this->identicalTo($request))
            ->willReturn([
                'author' => $expected['author'],
                'author.name' => $expected['author.name'],
                'author.username' => $expected['author.username'],
            ]);

        $tags
            ->expects($this->once())
            ->method('rulesForCreate')
            ->with($this->identicalTo($request))
            ->willReturn(fn (): array => [
                'tags' => $expected['tags'],
                'tags.*' => $expected['tags.*'],
            ]);

        $noRules1->method('rulesForCreate')->willReturn(null);
        $noRules2->method('rulesForCreate')->willReturn([]);
        $noRules3->method('rulesForCreate')->willReturn(fn () => null);

        $fields = new ListOfFields(
            $notValidated,
            $title,
            $publishedAt,
            $author,
            $noRules1,
            $tags,
            $noRules2,
            $noRules3,
        );

        $this->assertSame($expected, $fields->forCreate($request));
    }

    /**
     * @return void
     */
    public function testItReturnsRulesForUpdate(): void
    {
        $request = $this->createMock(Request::class);
        $model = new \stdClass();

        $notValidated = $this->createField('foo');
        $title = $this->createValidatedField('title');
        $publishedAt = $this->createValidatedField('publishedAt');
        $author = $this->createValidatedField('author');
        $tags = $this->createValidatedField('tags');
        $noRules1 = $this->createValidatedField('bar');
        $noRules2 = $this->createValidatedField('baz');
        $noRules3 = $this->createValidatedField('bat');

        $expected = [
            'title' => ['required', 'string', 'min:3'],
            'publishedAt' => ['nullable', 'date'],
            'author' => 'array:name,username',
            'author.name' => ['required', 'string'],
            'author.username' => ['required', 'string', 'email'],
            'tags' => 'array',
            'tags.*' => ['string', 'min:1'],
        ];

        $title
            ->expects($this->once())
            ->method('rulesForUpdate')
            ->with($this->identicalTo($request), $this->identicalTo($model))
            ->willReturn($expected['title']);

        $publishedAt
            ->expects($this->once())
            ->method('rulesForUpdate')
            ->with($this->identicalTo($request), $this->identicalTo($model))
            ->willReturn(function ($r, $m) use ($request, $model, $expected): array {
                $this->assertSame($request, $r);
                $this->assertSame($model, $m);
                return $expected['publishedAt'];
            });

        $author
            ->expects($this->once())
            ->method('rulesForUpdate')
            ->with($this->identicalTo($request), $this->identicalTo($model))
            ->willReturn([
                'author' => $expected['author'],
                'author.name' => $expected['author.name'],
                'author.username' => $expected['author.username'],
            ]);

        $tags
            ->expects($this->once())
            ->method('rulesForUpdate')
            ->with($this->identicalTo($request), $this->identicalTo($model))
            ->willReturn(fn (): array => [
                'tags' => $expected['tags'],
                'tags.*' => $expected['tags.*'],
            ]);

        $noRules1->method('rulesForUpdate')->willReturn(null);
        $noRules2->method('rulesForUpdate')->willReturn([]);
        $noRules3->method('rulesForUpdate')->willReturn(fn () => []);

        $fields = new ListOfFields(
            $notValidated,
            $title,
            $publishedAt,
            $author,
            $noRules1,
            $tags,
            $noRules2,
            $noRules3,
        );

        $this->assertSame($expected, $fields->forUpdate($request, $model));
    }

    /**
     * @return void
     */
    public function testItReturnsRulesForRelation1(): void
    {
        $request = $this->createMock(Request::class);
        $model = new \stdClass();

        $title = $this->createValidatedField('title');
        $publishedAt = $this->createValidatedField('publishedAt');
        $author = $this->createValidatedField('author');
        $tags = $this->createValidatedField('tags');

        $expected = [
            'tags' => 'array',
            'tags.*' => ['string', 'min:1'],
        ];

        $title
            ->expects($this->never())
            ->method('rulesForUpdate');

        $publishedAt
            ->expects($this->never())
            ->method('rulesForUpdate');

        $author
            ->expects($this->never())
            ->method('rulesForUpdate');

        $tags
            ->expects($this->once())
            ->method('rulesForUpdate')
            ->with($this->identicalTo($request), $this->identicalTo($model))
            ->willReturn(fn () => $expected);

        $fields = new ListOfFields(
            $title,
            $publishedAt,
            $author,
            $tags,
        );

        $this->assertSame($expected, $fields->forRelation($request, $model, 'tags'));
    }

    /**
     * @return void
     */
    public function testItReturnsRulesForRelation2(): void
    {
        $request = $this->createMock(Request::class);
        $model = new \stdClass();
        $expected = ['tags' => ['required', 'to-many']];

        $title = $this->createValidatedField('title');
        $tags = $this->createValidatedField('tags');

        $title
            ->expects($this->never())
            ->method('rulesForUpdate');

        $tags
            ->expects($this->once())
            ->method('rulesForUpdate')
            ->with($this->identicalTo($request), $this->identicalTo($model))
            ->willReturn($expected['tags']);

        $fields = new ListOfFields($title, $tags);

        $this->assertSame($expected, $fields->forRelation($request, $model, 'tags'));
    }

    /**
     * @param string $name
     * @return MockObject&Field
     */
    private function createField(string $name): Field&MockObject
    {
        $mock = $this->createMock(Field::class);
        $mock->method('name')->willReturn($name);

        return $mock;
    }

    /**
     * @param string $name
     * @return MockObject&IsValidated&Field
     */
    private function createValidatedField(string $name): Field&IsValidated&MockObject
    {
        $mock = $this->createMock(TestField::class);
        $mock->method('name')->willReturn($name);

        return $mock;
    }
}
