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
use LaravelJsonApi\Validation\Fields\Validated;
use PHPUnit\Framework\TestCase;

class ValidatedTest extends TestCase
{
    /**
     * @return void
     */
    public function testItGetsRulesForCreation(): void
    {
        $field = new class implements IsValidated {
            use Validated;

            protected function defaultRules(): array
            {
                return ['string'];
            }
        };

        $request = $this->createMock(Request::class);

        $field
            ->rules(function (Request $r, ?object $m) use ($request): array {
                $this->assertSame($request, $r);
                $this->assertNull($m);
                return ['required', 'email', 'max:255'];
            })
            ->creationRules('unique:users,email');

        $actual = $field->rulesForCreation($request);

        $this->assertSame(['required', 'string', 'email', 'max:255', 'unique:users,email'], $actual);
    }

    /**
     * @return void
     */
    public function testItGetsRuleForUpdate(): void
    {
        $field = new class implements IsValidated {
            use Validated;

            protected function defaultRules(): array
            {
                return ['string'];
            }
        };

        $request = $this->createMock(Request::class);
        $model = new \stdClass();

        $field
            ->rules(function (Request $r, ?object $m) use ($request, $model): array {
                $this->assertSame($request, $r);
                $this->assertSame($model, $m);
                return ['required', 'email', 'max:255'];
            })
            ->updateRules('unique:users,email');

        $actual = $field->rulesForUpdate($request, $model);

        $this->assertSame(['required', 'string', 'email', 'max:255', 'unique:users,email'], $actual);
    }
}
