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
use LaravelJsonApi\Contracts\Encoder\Encoder;
use LaravelJsonApi\Core\Document\ResourceObject;
use LaravelJsonApi\Core\Extensions\Atomic\Operations\Update;
use LaravelJsonApi\Validation\ValidatedSchema;

class UpdateExtractor
{
    /**
     * UpdateExtractor constructor
     *
     * @param ValidatedSchema $schema
     * @param Encoder $encoder
     * @param Request|null $request
     */
    public function __construct(
        private readonly ValidatedSchema $schema,
        private readonly Encoder $encoder,
        private readonly Request|null $request,
    ) {
    }

    /**
     * Extract data to validate.
     *
     * @param Update $operation
     * @param object $model
     * @return array
     */
    public function extract(Update $operation, object $model): array
    {
        $existing = $this->existing($model);
        $input = $operation->data->toArray();

        return ResourceObject::fromArray($existing)
            ->merge($input)
            ->all();
    }

    /**
     * @param object $model
     * @return array
     */
    public function existing(object $model): array
    {
        $values = $this->encoder
            ->withRequest($this->request)
            ->withIncludePaths($this->schema->includePaths())
            ->withResource($model)
            ->toArray()['data'];

        return $this->schema->withExisting($model, $values) ?? $values;
    }
}
