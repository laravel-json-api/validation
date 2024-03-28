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

use Illuminate\Http\Request;
use LaravelJsonApi\Validation\Fields\IsValidated;
use LaravelJsonApi\Validation\Fields\ValidatedWithArrayKeys;
use PHPUnit\Framework\TestCase;

class ValidatedWithArrayKeysTest extends TestCase
{
    /**
     * @return void
     */
    public function testItGetsRulesForCreation(): void
    {
        $field = new class implements IsValidated {
            use ValidatedWithArrayKeys;

            protected function defaultRules(): array
            {
                return ['.' => 'array'];
            }
        };

        $request = $this->createMock(Request::class);

        $field
            ->rules(function (Request $r) use ($request): array {
                $this->assertSame($request, $r);
                return ['foo' => 'string', 'bar' => 'integer'];
            })
            ->creationRules(['foo' => 'unique:users,email']);

        $actual = $field->rulesForCreation($request);

        $this->assertSame([
            '.' => ['array'],
            'bar' => ['integer'],
            'foo' => ['string', 'unique:users,email'],
        ], $actual);
    }

    /**
     * @return void
     */
    public function testItGetsRuleForUpdate(): void
    {
        $field = new class implements IsValidated {
            use ValidatedWithArrayKeys;

            protected function defaultRules(): array
            {
                return ['.' => 'array'];
            }
        };

        $request = $this->createMock(Request::class);
        $model = new \stdClass();

        $field
            ->rules(function (Request $r, ?object $m) use ($request, $model): array {
                $this->assertSame($request, $r);
                $this->assertSame($model, $m);
                return ['foo' => 'string', 'bar' => 'integer'];
            })
            ->updateRules(['foo' => 'unique:users,email']);

        $actual = $field->rulesForUpdate($request, $model);

        $this->assertSame([
            '.' => ['array'],
            'bar' => ['integer'],
            'foo' => ['string', 'unique:users,email'],
        ], $actual);
    }
}
