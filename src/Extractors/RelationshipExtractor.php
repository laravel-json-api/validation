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

use LaravelJsonApi\Contracts\Resources\Container;
use LaravelJsonApi\Contracts\Schema\Schema;
use LaravelJsonApi\Core\Document\ResourceObject;
use LaravelJsonApi\Core\Extensions\Atomic\Operations\UpdateToMany;
use LaravelJsonApi\Core\Extensions\Atomic\Operations\UpdateToOne;
use LaravelJsonApi\Core\Values\ResourceType;

class RelationshipExtractor
{
    /**
     * @param Schema $schema
     * @param Container $resources
     */
    public function __construct(
        private readonly Schema $schema,
        private readonly Container $resources,
    ) {
    }

    /**
     * @param object $model
     * @param UpdateToOne|UpdateToMany $operation
     * @return array
     */
    public function extract(object $model, UpdateToOne|UpdateToMany $operation): array
    {
        $type = ResourceType::cast($this->schema->type());

        $input = [
            'type' => $type->value,
            'id' => $this->resources->idForType($type, $model),
            'relationships' => [
                $operation->getFieldName() => [
                    'data' => $operation->data?->toArray(),
                ],
            ],
        ];

        return ResourceObject::fromArray($input)->all();
    }
}
