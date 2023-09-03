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
