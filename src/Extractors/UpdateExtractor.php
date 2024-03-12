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
