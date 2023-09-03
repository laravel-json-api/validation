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

namespace LaravelJsonApi\Validation\Tests\Unit\Utils;

use Illuminate\Http\Request;
use LaravelJsonApi\Validation\Utils\KeyedSetOfRules;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class KeyedSetOfRulesTest extends TestCase
{
    /**
     * @return array[]
     */
    public function scenarioProvider(): array
    {
        return [
            'prepend' => [
                function (): KeyedSetOfRules {
                    return KeyedSetOfRules::make()
                        ->prepend(['.' => 'array:foo,bar', 'foo' => 'string', 'bar' => 'integer'])
                        ->rules(null)
                        ->append(null);
                },
                [
                    '.' => 'array:foo,bar',
                    'foo' => 'string',
                    'bar' => 'integer',
                ],
            ],
            'prepend closure' => [
                function (Request $request, object $model): KeyedSetOfRules {
                    return KeyedSetOfRules::make()
                        ->prepend(function ($r, $m) use ($request, $model): array {
                            Assert::assertSame($request, $r);
                            Assert::assertSame($model, $m);
                            return [
                                '.' => ['array:foo,bar'],
                                'foo' => ['string'],
                                'bar' => 'integer'
                            ];
                        });
                },
                [
                    '.' => ['array:foo,bar'],
                    'foo' => ['string'],
                    'bar' => 'integer',
                ],
            ],
            'rules' => [
                function (): KeyedSetOfRules {
                    return KeyedSetOfRules::make()
                        ->prepend(null)
                        ->rules(['.' => 'array:foo,bar', 'foo' => 'string', 'bar' => 'integer'])
                        ->append(null);
                },
                [
                    '.' => ['array:foo,bar'],
                    'foo' => ['string'],
                    'bar' => ['integer'],
                ],
            ],
            'rules closure' => [
                function (Request $request, object $model): KeyedSetOfRules {
                    return KeyedSetOfRules::make()
                        ->rules(function ($r, $m) use ($request, $model): array {
                            Assert::assertSame($request, $r);
                            Assert::assertSame($model, $m);
                            return [
                                '.' => ['array:foo,bar'],
                                'foo' => ['string'],
                                'bar' => 'integer'
                            ];
                        });
                },
                [
                    '.' => ['array:foo,bar'],
                    'foo' => ['string'],
                    'bar' => ['integer'],
                ],
            ],
            'append' => [
                function (): KeyedSetOfRules {
                    return KeyedSetOfRules::make()
                        ->prepend(null)
                        ->rules(['.' => 'array:foo,bar', 'foo' => 'string', 'bar' => 'integer'])
                        ->append(['.' => 'size:2', 'foo' => ['email', 'max:255'], 'bar' => 'min:10']);
                },
                [
                    '.' => ['array:foo,bar', 'size:2'],
                    'foo' => ['string', 'email', 'max:255'],
                    'bar' => ['integer', 'min:10'],
                ],
            ],
            'append closure' => [
                function (Request $request, object $model): KeyedSetOfRules {
                    return KeyedSetOfRules::make()
                        ->prepend(null)
                        ->rules(static fn() => ['.' => 'array:foo,bar', 'foo' => 'string', 'bar' => 'integer'])
                        ->append(function ($r, $m) use ($request, $model): array {
                            Assert::assertSame($request, $r);
                            Assert::assertSame($model, $m);
                            return ['.' => 'size:2', 'foo' => ['email', 'max:255'], 'bar' => 'min:10'];
                        });
                },
                [
                    '.' => ['array:foo,bar', 'size:2'],
                    'foo' => ['string', 'email', 'max:255'],
                    'bar' => ['integer', 'min:10'],
                ],
            ],
            'all' => [
                function (): KeyedSetOfRules {
                    return KeyedSetOfRules::make()
                        ->prepend(['.' => 'array:foo,bar', 'foo' => 'string', 'bar' => 'integer'])
                        ->rules(['.' => 'size:2', 'foo' => ['email', 'max:255'], 'bar' => 'min:10'])
                        ->append(['.' => 'required']);
                },
                [
                    '.' => ['array:foo,bar', 'size:2', 'required'],
                    'foo' => ['string', 'email', 'max:255'],
                    'bar' => ['integer', 'min:10'],
                ],
            ],
            'all closures' => [
                function (): KeyedSetOfRules {
                    return KeyedSetOfRules::make()
                        ->prepend(static fn () => ['.' => 'array:foo,bar', 'foo' => 'string', 'bar' => 'integer'])
                        ->rules(static fn() => ['.' => 'size:2', 'foo' => ['email', 'max:255'], 'bar' => 'min:10'])
                        ->append(static fn() => ['.' => 'required']);
                },
                [
                    '.' => ['array:foo,bar', 'size:2', 'required'],
                    'foo' => ['string', 'email', 'max:255'],
                    'bar' => ['integer', 'min:10'],
                ],
            ],
        ];
    }

    /**
     * @param Closure(Request $request, object $model): KeyedSetOfRules $scenario
     * @param array $expected
     * @return void
     * @dataProvider scenarioProvider
     */
    public function test(\Closure $scenario, array $expected): void
    {
        $rules = $scenario(
            $request = $this->createMock(Request::class),
            $model = new \stdClass(),
        );

        $this->assertSame($expected, $rules($request, $model));
    }
}
