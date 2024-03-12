<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Validation\Fields;

use Closure;

class CreationRulesParser extends FieldRulesParser
{
    /**
     * @param IsValidated $field
     * @return Closure|array|null
     */
    protected function extract(IsValidated $field): Closure|array|null
    {
        return $field->rulesForCreation($this->request);
    }
}
