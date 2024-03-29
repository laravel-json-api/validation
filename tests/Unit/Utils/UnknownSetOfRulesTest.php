<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Validation\Tests\Unit\Utils;

use Closure;
use Illuminate\Http\Request;
use LaravelJsonApi\Validation\Rules\JsonArray;
use LaravelJsonApi\Validation\Rules\JsonBoolean;
use LaravelJsonApi\Validation\Tests\Integration\TestCase;
use LaravelJsonApi\Validation\Utils\UnknownSetOfRules;
use PHPUnit\Framework\Assert;

class UnknownSetOfRulesTest extends TestCase
{
    /**
     * @return array[]
     */
    public static function keyedSetScenarioProvider(): array
    {
        $list = new JsonArray();

        return [
            'prepend' => [
                static function (): UnknownSetOfRules {
                    return UnknownSetOfRules::make()
                        ->prepend(['foo' => 'string', 'bar' => 'integer'])
                        ->rules(null)
                        ->append(null);
                },
                [
                    '.' => ['array:foo,bar'],
                    'bar' => ['integer'],
                    'foo' => ['string'],
                ],
            ],
            'prepend closure' => [
                static function (Request $request, object $model): UnknownSetOfRules {
                    return UnknownSetOfRules::make()
                        ->prepend(function ($r, $m) use ($request, $model): array {
                            Assert::assertSame($request, $r);
                            Assert::assertSame($model, $m);
                            return [
                                '.' => ['array:foo,bar,baz'],
                                'foo' => ['string'],
                                'bar' => 'integer'
                            ];
                        });
                },
                [
                    '.' => ['array:foo,bar,baz'],
                    'bar' => ['integer'],
                    'foo' => ['string'],
                ],
            ],
            'rules' => [
                static function (): UnknownSetOfRules {
                    return UnknownSetOfRules::make()
                        ->prepend(null)
                        ->rules(['.' => 'required', 'foo' => 'string', 'bar' => 'integer'])
                        ->append(null);
                },
                [
                    '.' => ['required', 'array:foo,bar'],
                    'bar' => ['integer'],
                    'foo' => ['string'],
                ],
            ],
            'rules closure' => [
                static function (Request $request, object $model): UnknownSetOfRules {
                    return UnknownSetOfRules::make()
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
                    'bar' => ['integer'],
                    'foo' => ['string'],
                ],
            ],
            'append' => [
                static function (): UnknownSetOfRules {
                    return UnknownSetOfRules::make()
                        ->prepend(null)
                        ->rules(['.' => 'array:foo,bar,baz', 'foo' => 'string', 'bar' => 'integer'])
                        ->append(['.' => 'size:2', 'foo' => ['email', 'max:255'], 'bar' => 'min:10']);
                },
                [
                    '.' => ['array:foo,bar,baz', 'size:2'],
                    'bar' => ['integer', 'min:10'],
                    'foo' => ['string', 'email', 'max:255'],
                ],
            ],
            'append closure' => [
                static function (Request $request, object $model): UnknownSetOfRules {
                    return UnknownSetOfRules::make()
                        ->prepend(null)
                        ->rules(static fn() => ['foo' => 'string', 'bar' => 'integer'])
                        ->append(function ($r, $m) use ($request, $model): array {
                            Assert::assertSame($request, $r);
                            Assert::assertSame($model, $m);
                            return ['.' => 'size:2', 'foo' => ['email', 'max:255'], 'bar' => 'min:10'];
                        });
                },
                [
                    '.' => ['array:foo,bar', 'size:2'],
                    'bar' => ['integer', 'min:10'],
                    'foo' => ['string', 'email', 'max:255'],
                ],
            ],
            'all' => [
                static function (): UnknownSetOfRules {
                    return UnknownSetOfRules::make()
                        ->prepend(['.' => 'array:foo,bar', 'foo' => 'string', 'bar' => 'integer'])
                        ->rules(['.' => 'size:2', 'foo' => ['email', 'max:255'], 'bar' => 'min:10'])
                        ->append(['.' => 'required']);
                },
                [
                    '.' => ['array:foo,bar', 'size:2', 'required'],
                    'bar' => ['integer', 'min:10'],
                    'foo' => ['string', 'email', 'max:255'],
                ],
            ],
            'all closures' => [
                static function (): UnknownSetOfRules {
                    return UnknownSetOfRules::make()
                        ->prepend(static fn () => ['.' => 'array:foo,bar', 'foo' => 'string', 'bar' => 'integer'])
                        ->rules(static fn() => ['.' => 'size:2', 'foo' => ['email', 'max:255'], 'bar' => 'min:10'])
                        ->append(static fn() => ['.' => 'required']);
                },
                [
                    '.' => ['array:foo,bar', 'size:2', 'required'],
                    'bar' => ['integer', 'min:10'],
                    'foo' => ['string', 'email', 'max:255'],
                ],
            ],
            'array list' => [
                static function () use ($list): UnknownSetOfRules {
                    return UnknownSetOfRules::make()
                        ->prepend(['.' => $list])
                        ->rules(['*' => ['string', 'email']]);
                },
                [
                    '.' => [$list],
                    '*' => ['string', 'email'],
                ],
            ],
            'array list (elements not validated)' => [
                static function () use ($list): UnknownSetOfRules {
                    return UnknownSetOfRules::make()
                        ->rules(['.' => $list]);
                },
                [
                    '.' => [$list],
                ],
            ],
            'array list containing objects' => [
                static function () use ($list): UnknownSetOfRules {
                    return UnknownSetOfRules::make()
                        ->rules(['.' => $list])
                        ->append(['*.name' => 'string', '*.email' => 'email']);
                },
                [
                    '.' => [$list],
                    '*.email' => ['email'],
                    '*.name' => ['string'],
                ],
            ],
        ];
    }

    /**
     * @param Closure(Request $request, object $model): UnknownSetOfRules $scenario
     * @param array $expected
     * @return void
     * @dataProvider keyedSetScenarioProvider
     */
    public function testItIsKeyedSet(\Closure $scenario, array $expected): void
    {
        $rules = $scenario(
            $request = $this->createMock(Request::class),
            $model = new \stdClass(),
        );

        $this->assertSame($expected, $rules->all($request, $model));
    }

    /**
     * @return array
     */
    public static function listScenarioProvider(): array
    {
        $closure = static function (string $attribute, mixed $value, Closure $fail): bool {
            return true;
        };

        $rule = new JsonBoolean();

        return [
            'rules' => [
                static function (): UnknownSetOfRules {
                    return UnknownSetOfRules::make()
                        ->defaults()
                        ->rules('required', 'email', 'max:255')
                        ->append();
                },
                ['required', 'email', 'max:255'],
            ],
            'rules array' => [
                static function (): UnknownSetOfRules {
                    return UnknownSetOfRules::make()
                        ->rules(['required', 'email', 'max:255']);
                },
                ['required', 'email', 'max:255'],
            ],
            'rules closure' => [
                static function (Request $request, object $model): UnknownSetOfRules {
                    return UnknownSetOfRules::make()
                        ->rules(static function ($r, $m) use ($request, $model): array {
                            Assert::assertSame($request, $r);
                            Assert::assertSame($model, $m);
                            return ['required', 'email', 'max:255'];
                        });
                },
                ['required', 'email', 'max:255'],
            ],
            'rules with objects' => [
                static function () use ($closure, $rule): UnknownSetOfRules {
                    return UnknownSetOfRules::make()
                        ->rules($closure, $rule);
                },
                [$closure, $rule],
            ],
            'defaults without required and nullable' => [
                static function (): UnknownSetOfRules {
                    return UnknownSetOfRules::make()
                        ->defaults('string')
                        ->rules('email', 'max:255')
                        ->append();
                },
                ['string', 'email', 'max:255'],
            ],
            'defaults with required' => [
                static function (): UnknownSetOfRules {
                    return UnknownSetOfRules::make()
                        ->defaults('string')
                        ->rules('required', 'email', 'max:255');
                },
                ['required', 'string', 'email', 'max:255'],
            ],
            'defaults with nullable' => [
                static function (): UnknownSetOfRules {
                    return UnknownSetOfRules::make()
                        ->defaults('string', 'blah!')
                        ->rules('nullable', 'email', 'max:255');
                },
                ['nullable', 'string', 'blah!', 'email', 'max:255'],
            ],
            'defaults with required not first' => [
                static function (): UnknownSetOfRules {
                    return UnknownSetOfRules::make()
                        ->defaults('string')
                        ->rules('bail', 'required', 'email', 'max:255');
                },
                ['bail', 'required', 'string', 'email', 'max:255'],
            ],
            'defaults with nullable not first' => [
                static function () use ($rule): UnknownSetOfRules {
                    return UnknownSetOfRules::make()
                        ->defaults($rule)
                        ->rules('bail', 'nullable', 'email', 'max:255');
                },
                ['bail', 'nullable', $rule, 'email', 'max:255'],
            ],
            'defaults closure' => [
                static function (Request $request, object $model): UnknownSetOfRules {
                    return UnknownSetOfRules::make()
                        ->defaults(static function ($r, $m) use ($request, $model): array {
                            Assert::assertSame($request, $r);
                            Assert::assertSame($model, $m);
                            return ['string', 'blah!'];
                        })
                        ->rules('required', 'email', 'max:255');
                },
                ['required', 'string', 'blah!', 'email', 'max:255'],
            ],
            'append' => [
                static function (): UnknownSetOfRules {
                    return UnknownSetOfRules::make()
                        ->defaults('string')
                        ->rules('required', 'email', 'max:255')
                        ->append('unique:users,email', 'blah!');
                },
                ['required', 'string', 'email', 'max:255', 'unique:users,email', 'blah!'],
            ],
            'append array' => [
                static function (): UnknownSetOfRules {
                    return UnknownSetOfRules::make()
                        ->defaults('string')
                        ->rules('required', 'email', 'max:255')
                        ->append(['unique:users,email', 'blah!']);
                },
                ['required', 'string', 'email', 'max:255', 'unique:users,email', 'blah!'],
            ],
            'append closure' => [
                static function (Request $request, object $model): UnknownSetOfRules {
                    return UnknownSetOfRules::make()
                        ->defaults('string')
                        ->rules('required', 'email', 'max:255')
                        ->append(static function ($r, $m) use ($request, $model): array {
                            Assert::assertSame($request, $r);
                            Assert::assertSame($model, $m);
                            return ['unique:users,email', 'blah!'];
                        });
                },
                ['required', 'string', 'email', 'max:255', 'unique:users,email', 'blah!'],
            ],
            'append with rule objects' => [
                static function () use ($closure, $rule): UnknownSetOfRules {
                    return UnknownSetOfRules::make()
                        ->defaults('string')
                        ->rules('required', 'email', 'max:255')
                        ->append('unique:users,email', $closure, $rule);
                },
                ['required', 'string', 'email', 'max:255', 'unique:users,email', $closure, $rule],
            ],
        ];
    }

    /**
     * @param Closure(Request $request, object $model): UnknownSetOfRules $scenario
     * @param array $expected
     * @return void
     * @dataProvider listScenarioProvider
     */
    public function testItIsListOfRules(Closure $scenario, array $expected): void
    {
        $rules = $scenario(
            $request = $this->createMock(Request::class),
            $model = new \stdClass(),
        );

        $this->assertSame($expected, $rules->all($request, $model));
    }
}