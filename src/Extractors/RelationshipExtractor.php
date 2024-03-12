<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
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
