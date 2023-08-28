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

namespace LaravelJsonApi\Validation\Pagination;

use Closure;
use Illuminate\Http\Request;
use LaravelJsonApi\Contracts\Pagination\Paginator;

class ValidatedPaginator
{
    /**
     * ValidatedPaginator constructor
     *
     * @param Paginator $paginator
     */
    public function __construct(private readonly Paginator $paginator)
    {
    }

    /**
     * Get validation rules for the paginator.
     *
     * @param Request|null $request
     * @return array
     */
    public function rules(?Request $request): array
    {
        $rules = [];

        foreach ($this->validationRules($request) as $key => $value) {
            $path = 'page.' . $key;
            $rules[$path] = $value;
        }

        return $rules;
    }

    /**
     * @param Request|null $request
     * @return array
     */
    private function validationRules(?Request $request): array
    {
        $rules = null;

        if ($this->paginator instanceof IsValidated) {
            $rules = $this->paginator->validationRules($request) ?? [];
        }

        if ($rules instanceof Closure) {
            $rules = $rules($request);
            assert($rules === null || is_array($rules), sprintf(
                'Validation rules closure for paginator %s must return an array or null.',
                $this->paginator::class,
            ));
        }

        return $rules ?? [];
    }
}
