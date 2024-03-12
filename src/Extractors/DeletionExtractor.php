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
use LaravelJsonApi\Validation\ValidatedSchema;

class DeletionExtractor
{
    /**
     * DeleteExtractor constructor
     *
     * @param ValidatedSchema $schema
     * @param UpdateExtractor $updateExtractor
     */
    public function __construct(
        private readonly ValidatedSchema $schema,
        private readonly UpdateExtractor $updateExtractor,
    ) {
    }

    /**
     * @param object $model
     * @return array
     */
    public function extract(object $model): array
    {
        $resource = $this->updateExtractor->existing($model);
        $fields = ResourceObject::fromArray($resource)->all();
        $meta = $this->schema->metaForDeletion($model);

        $fields['meta'] = [
            ...$resource['meta'] ?? [],
            ...$meta,
        ];

        ksort($fields);
        ksort($fields['meta']);

        return $fields;
    }
}
