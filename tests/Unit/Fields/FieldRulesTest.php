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
use LaravelJsonApi\Validation\Fields\FieldRules;
use LaravelJsonApi\Validation\Rules\JsonBoolean;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class FieldRulesTest extends TestCase
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
                function (): FieldRules {
                    return FieldRules::make()
                        ->always()
                        ->rules('required', 'email', 'max:255')
                        ->append();
                },
                ['required', 'email', 'max:255'],
            ],
            'rules array' => [
                function (): FieldRules {
                    return FieldRules::make()
                        ->rules(['required', 'email', 'max:255']);
                },
                ['required', 'email', 'max:255'],
            ],
            'rules closure' => [
                function (Request $request, object $model): FieldRules {
                    return FieldRules::make()
                        ->rules(static function ($r, $m) use ($request, $model): array {
                            Assert::assertSame($request, $r);
                            Assert::assertSame($model, $m);
                            return ['required', 'email', 'max:255'];
                        });
                },
                ['required', 'email', 'max:255'],
            ],
            'rules with objects' => [
                function () use ($closure, $rule): FieldRules {
                    return FieldRules::make()
                        ->rules($closure, $rule);
                },
                [$closure, $rule],
            ],
            'always without required and nullable' => [
                function (): FieldRules {
                    return FieldRules::make()
                        ->always('string')
                        ->rules('email', 'max:255')
                        ->append();
                },
                ['string', 'email', 'max:255'],
            ],
            'always with required' => [
                function (): FieldRules {
                    return FieldRules::make()
                        ->always('string')
                        ->rules('required', 'email', 'max:255');
                },
                ['required', 'string', 'email', 'max:255'],
            ],
            'always with nullable' => [
                function (): FieldRules {
                    return FieldRules::make()
                        ->always('string', 'blah!')
                        ->rules('nullable', 'email', 'max:255');
                },
                ['nullable', 'string', 'blah!', 'email', 'max:255'],
            ],
            'always with required not first' => [
                function (): FieldRules {
                    return FieldRules::make()
                        ->always('string')
                        ->rules('bail', 'required', 'email', 'max:255');
                },
                ['bail', 'required', 'string', 'email', 'max:255'],
            ],
            'always with nullable not first' => [
                function () use ($rule): FieldRules {
                    return FieldRules::make()
                        ->always($rule)
                        ->rules('bail', 'nullable', 'email', 'max:255');
                },
                ['bail', 'nullable', $rule, 'email', 'max:255'],
            ],
            'always closure' => [
                function (Request $request, object $model): FieldRules {
                    return FieldRules::make()
                        ->always(static function ($r, $m) use ($request, $model): array {
                            Assert::assertSame($request, $r);
                            Assert::assertSame($model, $m);
                            return ['string', 'blah!'];
                        })
                        ->rules('required', 'email', 'max:255');
                },
                ['required', 'string', 'blah!', 'email', 'max:255'],
            ],
            'append' => [
                function (): FieldRules {
                    return FieldRules::make()
                        ->always('string')
                        ->rules('required', 'email', 'max:255')
                        ->append('unique:users,email', 'blah!');
                },
                ['required', 'string', 'email', 'max:255', 'unique:users,email', 'blah!'],
            ],
            'append array' => [
                function (): FieldRules {
                    return FieldRules::make()
                        ->always('string')
                        ->rules('required', 'email', 'max:255')
                        ->append(['unique:users,email', 'blah!']);
                },
                ['required', 'string', 'email', 'max:255', 'unique:users,email', 'blah!'],
            ],
            'append closure' => [
                function (Request $request, object $model): FieldRules {
                    return FieldRules::make()
                        ->always('string')
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
                function () use ($closure, $rule): FieldRules {
                    return FieldRules::make()
                        ->always('string')
                        ->rules('required', 'email', 'max:255')
                        ->append('unique:users,email', $closure, $rule);
                },
                ['required', 'string', 'email', 'max:255', 'unique:users,email', $closure, $rule],
            ],
        ];
    }

    /**
     * @param Closure(Request $request, object $model): FieldRules $scenario
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
