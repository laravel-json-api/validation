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

namespace LaravelJsonApi\Validation\Tests\Unit\Pagination;

use Closure;
use Illuminate\Http\Request;
use LaravelJsonApi\Contracts\Pagination\Paginator;
use LaravelJsonApi\Core\Query\Input\Query;
use LaravelJsonApi\Core\Query\Input\QueryMany;
use LaravelJsonApi\Core\Values\ResourceType;
use LaravelJsonApi\Validation\Pagination\IsValidated;
use LaravelJsonApi\Validation\Pagination\ValidatedPaginator;
use PHPUnit\Framework\TestCase;

class ValidatedPaginatorTest extends TestCase
{
    /**
     * @var Request
     */
    private Request $request;

    /**
     * @var Query
     */
    private Query $query;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->request = $this->createMock(Request::class);
        $this->query = new QueryMany(new ResourceType('posts'));
    }

    /**
     * @return void
     */
    public function testItIsNotValidated(): void
    {
        $paginator = new class implements Paginator {
            public function keys(): array
            {
                return [];
            }
        };

        $validated = new ValidatedPaginator($paginator, $this->request);

        $this->assertSame([], $validated->rules($this->query));
    }

    /**
     * @return void
     */
    public function testItIsValidatedWithNull(): void
    {
        $paginator = new class implements Paginator, IsValidated {
            public function keys(): array
            {
                return [];
            }

            public function validationRules(?Request $request, Query $query): Closure|array|null
            {
                return null;
            }
        };

        $validated = new ValidatedPaginator($paginator, $this->request);

        $this->assertSame([], $validated->rules($this->query));
    }

    /**
     * @return void
     */
    public function testItIsValidatedWithArray(): void
    {
        $paginator = new class implements Paginator, IsValidated {
            public function keys(): array
            {
                return [];
            }

            public function validationRules(?Request $request, Query $query): array
            {
                return [
                    'number' => ['required', 'integer', 'min:1'],
                    'size' => ['required', 'integer', 'between:1,250'],
                ];
            }
        };

        $validated = new ValidatedPaginator($paginator, $this->request);

        $this->assertSame([
            'page.number' => ['required', 'integer', 'min:1'],
            'page.size' => ['required', 'integer', 'between:1,250'],
        ], $validated->rules($this->query));
    }

    /**
     * @return void
     */
    public function testItIsValidatedWithClosure(): void
    {
        $fn = function (Request $request, Query $query): array {
            $this->assertSame($this->request, $request);
            $this->assertSame($this->query, $query);
            return [
                'number' => ['required', 'integer', 'min:1'],
                'size' => ['required', 'integer', 'between:1,250'],
            ];
        };

        $paginator = new class($fn) implements Paginator, IsValidated {
            public function __construct(private readonly Closure $fn)
            {
            }

            public function keys(): array
            {
                return [];
            }

            public function validationRules(?Request $request, Query $query): Closure
            {
                return $this->fn;
            }
        };

        $validated = new ValidatedPaginator($paginator, $this->request);

        $this->assertSame([
            'page.number' => ['required', 'integer', 'min:1'],
            'page.size' => ['required', 'integer', 'between:1,250'],
        ], $validated->rules($this->query));
    }
}
