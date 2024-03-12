<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Validation\Pagination;

use Closure;
use Illuminate\Http\Request;
use LaravelJsonApi\Contracts\Pagination\Paginator;
use LaravelJsonApi\Core\Query\Input\Query;

class ValidatedPaginator
{
    /**
     * ValidatedPaginator constructor
     *
     * @param Paginator $paginator
     * @param Request|null $request
     */
    public function __construct(
        private readonly Paginator $paginator,
        private readonly ?Request $request,
    ) {
    }

    /**
     * Get validation rules for the paginator.
     *
     * @param Query $query
     * @return array
     */
    public function rules(Query $query): array
    {
        $rules = [];

        foreach ($this->validationRules($query) as $key => $value) {
            $path = 'page.' . $key;
            $rules[$path] = $value;
        }

        return $rules;
    }

    /**
     * @param Query $query
     * @return array
     */
    private function validationRules(Query $query): array
    {
        $rules = null;

        if ($this->paginator instanceof IsValidated) {
            $rules = $this->paginator->validationRules($this->request, $query) ?? [];
        }

        if ($rules instanceof Closure) {
            $rules = $rules($this->request, $query);
            assert($rules === null || is_array($rules), sprintf(
                'Validation rules closure for paginator %s must return an array or null.',
                $this->paginator::class,
            ));
        }

        return $rules ?? [];
    }
}
