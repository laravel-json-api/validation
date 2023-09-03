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

namespace LaravelJsonApi\Validation\Fields;

use Closure;

class UpdateRulesParser extends FieldRulesParser
{
    /**
     * @param object $model
     * @return $this
     */
    public function with(object $model): self
    {
        $this->model = $model;

        return $this;
    }

    /**
     * @param IsValidated $field
     * @return Closure|array|null
     */
    protected function extract(IsValidated $field): Closure|array|null
    {
        assert($this->model !== null, 'Expecting model to be injected.');

        return $field->rulesForUpdate($this->request, $this->model);
    }
}
