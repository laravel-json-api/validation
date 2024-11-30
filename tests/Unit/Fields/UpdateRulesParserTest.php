<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Validation\Tests\Unit\Fields;

use Closure;
use Illuminate\Http\Request;
use LaravelJsonApi\Contracts\Schema\Field;
use LaravelJsonApi\Validation\Fields\UpdateRulesParser;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UpdateRulesParserTest extends TestCase
{
    /**
     * @var Request
     */
    private Request $request;

    /**
     * @var \stdClass
     */
    private \stdClass $model;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->request = $this->createMock(Request::class);
        $this->model = new \stdClass();
    }

    /**
     * @return void
     */
    public function test(): void
    {
        $expected = [
            'title' => ['required', 'string', 'between:5,255'],
            'slug' => ['required', 'string', 'between:5,255', 'unique:posts,slug'],
            'author' => ['array:name,nickname'],
            'author.name' => ['required', 'string', 'between:2,150'],
            'author.nickname' => ['required', 'string', 'between:2,50'],
            'tags' => ['array'],
            'tags.*' => ['string', 'min:1'],
        ];

        $values = [
            $this->createValidatedField('title', $expected['title']),
            $this->createValidatedField('slug', function (?Request $request) use ($expected): array {
                $this->assertSame($this->request, $request);
                return $expected['slug'];
            }),
            $this->createValidatedField('author', function (?Request $request) use ($expected): array {
                $this->assertSame($this->request, $request);
                return [
                    '.' => $expected['author'],
                    'name' => $expected['author.name'],
                    'nickname' => $this->createValidatedField('nickname', $expected['author.nickname']),
                    'blah!' => $this->createUnvalidatedField('blah!'),
                ];
            }),
            $this->createValidatedField('tags', fn() => [
                '.' => $expected['tags'],
                '*' => $expected['tags.*'],
            ]),
            $this->createUnvalidatedField('blah!'),
        ];

        $parser = new UpdateRulesParser($this->request);
        $actual = $parser->with($this->model)->parse($values);

        $this->assertSame($expected, $actual);
    }

    /**
     * @param string $name
     * @param Closure|array|null $rules
     * @return MockObject&TestField
     */
    private function createValidatedField(string $name, Closure|array|null $rules): TestField&MockObject
    {
        $field = $this->createMock(TestField::class);
        $field->method('name')->willReturn($name);
        $field->expects($this->never())->method('rulesForCreation');
        $field->method('rulesForUpdate')
            ->with($this->identicalTo($this->request), $this->identicalTo($this->model))
            ->willReturn($rules);

        return $field;
    }

    /**
     * @param string $name
     * @return MockObject&Field
     */
    private function createUnvalidatedField(string $name): Field&MockObject
    {
        $field = $this->createMock(Field::class);
        $field->method('name')->willReturn($name);

        return $field;
    }
}