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
use LaravelJsonApi\Validation\Rules\JsonBoolean;
use LaravelJsonApi\Validation\Utils\ListOfRules;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class ListOfRulesTest extends TestCase
{
    /**
     * @return array
     */
    public static function scenarioProvider(): array
    {
        $closure = static function (string $attribute, mixed $value, Closure $fail): bool {
            return true;
        };

        $rule = new JsonBoolean();

        return [
            'rules' => [
                function (): ListOfRules {
                    return ListOfRules::make()
                        ->defaults()
                        ->rules('required', 'email', 'max:255')
                        ->append();
                },
                ['required', 'email', 'max:255'],
            ],
            'rules array' => [
                function (): ListOfRules {
                    return ListOfRules::make()
                        ->rules(['required', 'email', 'max:255']);
                },
                ['required', 'email', 'max:255'],
            ],
            'rules closure' => [
                function (Request $request, object $model): ListOfRules {
                    return ListOfRules::make()
                        ->rules(static function ($r, $m) use ($request, $model): array {
                            Assert::assertSame($request, $r);
                            Assert::assertSame($model, $m);
                            return ['required', 'email', 'max:255'];
                        });
                },
                ['required', 'email', 'max:255'],
            ],
            'rules with objects' => [
                function () use ($closure, $rule): ListOfRules {
                    return ListOfRules::make()
                        ->rules($closure, $rule);
                },
                [$closure, $rule],
            ],
            'defaults without required and nullable' => [
                function (): ListOfRules {
                    return ListOfRules::make()
                        ->defaults('string')
                        ->rules('email', 'max:255')
                        ->append();
                },
                ['string', 'email', 'max:255'],
            ],
            'defaults with required' => [
                function (): ListOfRules {
                    return ListOfRules::make()
                        ->defaults('string')
                        ->rules('required', 'email', 'max:255');
                },
                ['required', 'string', 'email', 'max:255'],
            ],
            'defaults with nullable' => [
                function (): ListOfRules {
                    return ListOfRules::make()
                        ->defaults('string', 'blah!')
                        ->rules('nullable', 'email', 'max:255');
                },
                ['nullable', 'string', 'blah!', 'email', 'max:255'],
            ],
            'defaults with required not first' => [
                function (): ListOfRules {
                    return ListOfRules::make()
                        ->defaults('string')
                        ->rules('bail', 'required', 'email', 'max:255');
                },
                ['bail', 'required', 'string', 'email', 'max:255'],
            ],
            'defaults with nullable not first' => [
                function () use ($rule): ListOfRules {
                    return ListOfRules::make()
                        ->defaults($rule)
                        ->rules('bail', 'nullable', 'email', 'max:255');
                },
                ['bail', 'nullable', $rule, 'email', 'max:255'],
            ],
            'defaults closure' => [
                function (Request $request, object $model): ListOfRules {
                    return ListOfRules::make()
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
                function (): ListOfRules {
                    return ListOfRules::make()
                        ->defaults('string')
                        ->rules('required', 'email', 'max:255')
                        ->append('unique:users,email', 'blah!');
                },
                ['required', 'string', 'email', 'max:255', 'unique:users,email', 'blah!'],
            ],
            'append array' => [
                function (): ListOfRules {
                    return ListOfRules::make()
                        ->defaults('string')
                        ->rules('required', 'email', 'max:255')
                        ->append(['unique:users,email', 'blah!']);
                },
                ['required', 'string', 'email', 'max:255', 'unique:users,email', 'blah!'],
            ],
            'append closure' => [
                function (Request $request, object $model): ListOfRules {
                    return ListOfRules::make()
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
                function () use ($closure, $rule): ListOfRules {
                    return ListOfRules::make()
                        ->defaults('string')
                        ->rules('required', 'email', 'max:255')
                        ->append('unique:users,email', $closure, $rule);
                },
                ['required', 'string', 'email', 'max:255', 'unique:users,email', $closure, $rule],
            ],
        ];
    }

    /**
     * @param Closure(Request $request, object $model): ListOfRules $scenario
     * @param array $expected
     * @return void
     * @dataProvider scenarioProvider
     */
    public function test(Closure $scenario, array $expected): void
    {
        $rules = $scenario(
            $request = $this->createMock(Request::class),
            $model = new \stdClass(),
        );

        $this->assertSame($expected, $rules($request, $model));
    }
}
