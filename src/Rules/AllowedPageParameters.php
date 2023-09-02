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

namespace LaravelJsonApi\Validation\Rules;

use Illuminate\Support\Collection;
use LaravelJsonApi\Contracts\Schema\Schema;

class AllowedPageParameters extends AbstractAllowedRule
{

    /**
     * Create an allowed page parameters rule for the supplied schema.
     *
     * @param Schema $schema
     * @return AllowedPageParameters
     */
    public static function make(Schema $schema): self
    {
        if ($paginator = $schema->query()->pagination()) {
            return new self($paginator->keys());
        }

        return new self([]);
    }

    /**
     * @inheritDoc
     */
    protected function extract($value): Collection
    {
        $value = is_array($value) ? $value : [];

        return Collection::make($value)->keys();
    }
}
