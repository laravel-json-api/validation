<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Validation\Tests\Unit\Filters;

use Illuminate\Http\Request;
use LaravelJsonApi\Core\Query\Input\QueryMany;
use LaravelJsonApi\Core\Values\ResourceType;
use LaravelJsonApi\Validation\Filters\IsValidated;
use LaravelJsonApi\Validation\Filters\ValidatedWithListOfRules;
use PHPUnit\Framework\TestCase;

class ValidatedWithListOfRulesTest extends TestCase
{
    /**
     * @return void
     */
    public function testItGetsRules(): void
    {
        $filter = new class implements IsValidated {
            use ValidatedWithListOfRules;

            protected function defaultRules(): array
            {
                return ['string'];
            }
        };

        $request = $this->createMock(Request::class);
        $query = new QueryMany(new ResourceType('posts'));

        $filter->rules(function ($r, $q) use ($request, $query): array {
            $this->assertSame($request, $r);
            $this->assertSame($query, $q);
            return ['required', 'email', 'max:255'];
        });

        $actual = $filter->validationRules($request, $query);

        $this->assertTrue($filter->isValidatedForOne());
        $this->assertTrue($filter->isValidatedForMany());
        $this->assertSame(['required', 'string', 'email', 'max:255'], $actual);
    }

    /**
     * @return void
     */
    public function testItIsValidatedForOne(): void
    {
        $filter = new class implements IsValidated {
            use ValidatedWithListOfRules;
        };

        $this->assertSame($filter, $filter->onlyToOne());
        $this->assertTrue($filter->isValidatedForOne());
        $this->assertFalse($filter->isValidatedForMany());
    }

    /**
     * @return void
     */
    public function testItIsValidatedForMany(): void
    {
        $filter = new class implements IsValidated {
            use ValidatedWithListOfRules;
        };

        $this->assertSame($filter, $filter->onlyToMany());
        $this->assertFalse($filter->isValidatedForOne());
        $this->assertTrue($filter->isValidatedForMany());
    }
}
