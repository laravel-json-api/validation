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

namespace LaravelJsonApi\Validation\Tests\Unit\Filters;

use Closure;
use Illuminate\Http\Request;
use LaravelJsonApi\Contracts\Schema\Filter;
use LaravelJsonApi\Core\Query\Input\QueryMany;
use LaravelJsonApi\Core\Values\ResourceType;
use LaravelJsonApi\Validation\Filters\QueryManyParser;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class QueryManyParserTest extends TestCase
{
    /**
     * @var Request|null
     */
    private ?Request $request = null;

    /**
     * @var QueryMany
     */
    private QueryMany $query;

    /**
     * @return void
     */
    public function test(): void
    {
        $this->query = new QueryMany(new ResourceType('posts'));
        $this->request = $this->createMock(Request::class);

        $expected = [
            'filter.title' => ['string'],
            'filter.slug' => ['string', 'regex:foo'],
            'filter.author' => ['array:name,nickname'],
            'filter.author.name' => ['required', 'string', 'between:2,150'],
            'filter.author.nickname' => ['required', 'string', 'between:2,50'],
            'filter.tags' => ['array'],
            'filter.tags.*' => ['string', 'min:1'],
        ];

        $values = [
            $this->createValidatedFilter('title', $expected['filter.title']),
            $this->createValidatedFilter('slug', function (?Request $request) use ($expected): array {
                $this->assertSame($this->request, $request);
                return $expected['filter.slug'];
            }),
            $this->createUnvalidatedField('blah!!'),
            $this->createSkippedFilter('foobar'),
            $this->createValidatedFilter('author', function (?Request $request) use ($expected): array {
                $this->assertSame($this->request, $request);
                return [
                    '.' => $expected['filter.author'],
                    'name' => $expected['filter.author.name'],
                    'nickname' => $this->createValidatedFilter('nickname', $expected['filter.author.nickname']),
                    'blah!' => $this->createUnvalidatedField('blah!'),
                    'blah!!' => $this->createSkippedFilter('blah!!'),
                ];
            }),
            $this->createValidatedFilter('tags', fn() => [
                '.' => $expected['filter.tags'],
                '*' => $expected['filter.tags.*'],
            ]),
            $this->createUnvalidatedField('blah!'),
            $this->createSkippedFilter('bazbat'),
        ];

        $parser = new QueryManyParser($this->request);
        $actual = $parser->with($this->query)->parse($values);

        $this->assertSame($expected, $actual);
    }

    /**
     * @param string $name
     * @param Closure|array|null $rules
     * @return MockObject&TestFilter
     */
    private function createValidatedFilter(string $name, Closure|array|null $rules): TestFilter&MockObject
    {
        $field = $this->createMock(TestFilter::class);
        $field->method('key')->willReturn($name);
        $field->method('isValidatedForOne')->willReturn(false);
        $field->method('isValidatedForMany')->willReturn(true);
        $field->method('validationRules')
            ->with($this->identicalTo($this->request), $this->identicalTo($this->query))
            ->willReturn($rules);

        return $field;
    }

    /**
     * @param string $name
     * @return MockObject&TestFilter
     */
    private function createSkippedFilter(string $name): TestFilter&MockObject
    {
        $field = $this->createMock(TestFilter::class);
        $field->method('key')->willReturn($name);
        $field->method('isValidatedForOne')->willReturn(true);
        $field->method('isValidatedForMany')->willReturn(false);
        $field->expects($this->never())->method('validationRules');

        return $field;
    }

    /**
     * @param string $name
     * @return MockObject&Filter
     */
    private function createUnvalidatedField(string $name): Filter&MockObject
    {
        $field = $this->createMock(Filter::class);
        $field->method('key')->willReturn($name);

        return $field;
    }
}
