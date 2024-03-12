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

use LaravelJsonApi\Core\Document\ResourceObject;
use LaravelJsonApi\Core\Extensions\Atomic\Operations\Create;

class CreationExtractor
{
    /**
     * Extract data to validate.
     *
     * @param Create $operation
     * @return array
     */
    public function extract(Create $operation): array
    {
        $resource = ResourceObject::fromArray(
            $operation->data->toArray(),
        );

        return $resource->all();
    }
}
