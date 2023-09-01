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

use Illuminate\Http\Request;
use LaravelJsonApi\Contracts\Schema\Schema;
use LaravelJsonApi\Core\Document\ResourceObject;

class DeleteExtractor
{
    /**
     * DeleteExtractor constructor
     *
     * @param Schema $schema
     * @param UpdateExtractor $updateExtractor
     * @param Request|null $request
     */
    public function __construct(
        private readonly Schema $schema,
        private readonly UpdateExtractor $updateExtractor,
        private readonly Request|null $request,
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
        $meta = [];

        if (method_exists($this->schema, 'metaForDelete')) {
            $meta = (array) $this->schema->metaForDelete($this->request, $model);
        }

        $fields['meta'] = array_merge(
            $resource['meta'] ?? [],
            $fields['meta'] ?? [],
            $meta,
        );

        return $fields;
    }
}
