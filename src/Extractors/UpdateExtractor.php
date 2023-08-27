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
use Illuminate\Support\Collection;
use LaravelJsonApi\Contracts\Encoder\Encoder;
use LaravelJsonApi\Contracts\Schema\Relation;
use LaravelJsonApi\Contracts\Schema\Schema;
use LaravelJsonApi\Core\Document\ResourceObject;
use LaravelJsonApi\Core\Extensions\Atomic\Operations\Update;
use LaravelJsonApi\Core\Query\IncludePaths;

class UpdateExtractor
{
    /**
     * UpdateExtractor constructor
     *
     * @param Schema $schema
     * @param Encoder $encoder
     */
    public function __construct(
        private readonly Schema $schema,
        private readonly Encoder $encoder,
    ) {
    }

    /**
     * Extract data to validate.
     *
     * @param Request|null $request
     * @param object $model
     * @param Update $operation
     * @return array
     */
    public function extract(?Request $request, object $model, Update $operation): array
    {
        $existing = $this->existing($request, $model);
        $input = $operation->data->toArray();

        return ResourceObject::fromArray($existing)
            ->merge($input)
            ->all();
    }

    /**
     * @param Request|null $request
     * @param object $model
     * @return array
     */
    public function existing(?Request $request, object $model): array
    {
        $values = $this->encoder
            ->withRequest($request)
            ->withIncludePaths($this->includePaths())
            ->withResource($model)
            ->toArray()['data'];

        if (method_exists($this->schema, 'withExisting')) {
            $values = $this->schema->withExisting($model, $values) ?? $values;
        }

        return $values;
    }

    /**
     * @return IncludePaths
     */
    private function includePaths(): IncludePaths
    {
        $paths = Collection::make($this->schema->relationships())
            ->filter(static fn (Relation $relation): bool => $relation->isValidated())
            ->map(static fn (Relation $relation): string => $relation->name())
            ->values();

        return IncludePaths::fromArray($paths);
    }
}
