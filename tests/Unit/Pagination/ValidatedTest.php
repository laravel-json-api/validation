<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Validation\Tests\Unit\Pagination;

use Illuminate\Http\Request;
use LaravelJsonApi\Core\Query\Input\QueryMany;
use LaravelJsonApi\Core\Values\ResourceType;
use LaravelJsonApi\Validation\Pagination\IsValidated;
use LaravelJsonApi\Validation\Pagination\Validated;
use PHPUnit\Framework\TestCase;

class ValidatedTest extends TestCase
{
    /**
     * @return void
     */
    public function testItGetsRules(): void
    {
        $paginator = new class implements IsValidated {
            use Validated;

            protected function defaultRules(): array
            {
                return ['.' => 'array'];
            }
        };

        $request = $this->createMock(Request::class);
        $query = new QueryMany(new ResourceType('comments'));

        $paginator->rules(function ($r, $q) use ($request, $query): array {
            $this->assertSame($request, $r);
            $this->assertSame($query, $q);
            return ['foo' => 'string', 'bar' => 'integer'];
        });

        $actual = $paginator->validationRules($request, $query);

        $this->assertSame([
            '.' => 'array',
            'foo' => ['string'],
            'bar' => ['integer'],
        ], $actual);
    }
}
