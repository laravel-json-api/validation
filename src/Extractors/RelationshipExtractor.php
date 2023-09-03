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

namespace LaravelJsonApi\Validation\Extractors;

use LaravelJsonApi\Core\Extensions\Atomic\Operations\UpdateToMany;
use LaravelJsonApi\Core\Extensions\Atomic\Operations\UpdateToOne;

class RelationshipExtractor
{
    /**
     * @param UpdateToOne|UpdateToMany $operation
     * @return array
     */
    public function extract(UpdateToOne|UpdateToMany $operation): array
    {
        $ref = $operation->ref();

        assert($ref->id !== null, 'Expecting a resource id.');

        $input = [
            'type' => $ref->type->value,
            'id' => $ref->id->value,
            $operation->getFieldName() => $operation->data?->toArray(),
        ];

        ksort($input);

        return $input;
    }
}
