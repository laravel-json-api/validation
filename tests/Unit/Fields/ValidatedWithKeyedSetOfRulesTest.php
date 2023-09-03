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
use LaravelJsonApi\Validation\Fields\IsValidated;
use LaravelJsonApi\Validation\Fields\ValidatedWithKeyedSetOfRules;
use PHPUnit\Framework\TestCase;

class ValidatedWithKeyedSetOfRulesTest extends TestCase
{
    /**
     * @return void
     */
    public function testItGetsRulesForCreation(): void
    {
        $field = new class implements IsValidated {
            use ValidatedWithKeyedSetOfRules;

            protected function defaultRules(): array
            {
                return ['.' => 'array'];
            }
        };

        $request = $this->createMock(Request::class);

        $field
            ->rules(function (Request $r, ?object $m) use ($request): array {
                $this->assertSame($request, $r);
                $this->assertNull($m);
                return ['foo' => 'string', 'bar' => 'integer'];
            })
            ->creationRules(['foo' => 'unique:users,email']);

        $actual = $field->rulesForCreation($request);

        $this->assertSame([
            '.' => 'array',
            'foo' => ['string', 'unique:users,email'],
            'bar' => ['integer'],
        ], $actual);
    }

    /**
     * @return void
     */
    public function testItGetsRuleForUpdate(): void
    {
        $field = new class implements IsValidated {
            use ValidatedWithKeyedSetOfRules;

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
            '.' => 'array',
            'foo' => ['string', 'unique:users,email'],
            'bar' => ['integer'],
        ], $actual);
    }
}
