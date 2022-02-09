<?php
/*
 * Copyright 2022 Cloud Creativity Limited
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

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Collection;
use LaravelJsonApi\Contracts\Schema\Container as SchemaContainer;
use LaravelJsonApi\Core\Support\Arr;
use LaravelJsonApi\Validation\JsonApiValidation;

class AllowedFieldSets implements Rule
{

    /**
     * @var Collection
     */
    private Collection $allowed;

    /**
     * @var SchemaContainer|null
     */
    private ?SchemaContainer $schemas = null;

    /**
     * The last value that was validated.
     *
     * @var array|null
     */
    private ?array $value = null;

    /**
     * Create an allowed field set rule for the supplied schemas.
     *
     * @param SchemaContainer $schemas
     * @return AllowedFieldSets
     */
    public static function make(SchemaContainer $schemas): self
    {
        $rule = new self();
        $rule->schemas = $schemas;

        return $rule;
    }

    /**
     * AllowedFieldSets constructor.
     *
     * @param iterable|null $allowed
     */
    public function __construct(iterable $allowed = [])
    {
        $this->allowed = collect($allowed);
    }

    /**
     * Allow fields for a resource type.
     *
     * @param string $resourceType
     * @param string[]|null $fields
     *      the allowed fields, empty array for none allowed, or null for all allowed.
     * @return $this
     */
    public function allow(string $resourceType, array $fields = null): self
    {
        $this->all = false;
        $this->allowed[$resourceType] = $fields;

        return $this;
    }

    /**
     * Allow any fields for the specified resource type.
     *
     * @param string ...$resourceTypes
     * @return $this
     */
    public function any(string ...$resourceTypes): self
    {
        foreach ($resourceTypes as $resourceType) {
            $this->allow($resourceType, null);
        }

        return $this;
    }

    /**
     * Allow no fields for the specified resource type.
     *
     * @param string ...$resourceTypes
     * @return $this
     */
    public function none(string ...$resourceTypes): self
    {
        foreach ($resourceTypes as $resourceType) {
            $this->allow($resourceType, []);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function passes($attribute, $value)
    {
        $this->value = $value;

        if (!is_array($value)) {
            return false;
        }

        return collect($value)->every(function ($value, $key) {
            return $this->allowed($key, (string) $value);
        });
    }

    /**
     * @inheritDoc
     */
    public function message()
    {
        if ($message = $this->messageForUnrecognised()) {
            return $message;
        }

        $invalid = $this->invalid();

        if ($invalid->isEmpty()) {
            $key = 'default';
        } else {
            $key = (1 === $invalid->count()) ? 'singular' : 'plural';
        }

        return trans(JsonApiValidation::translationKeyForRule($this, $key), [
            'values' => $invalid->sort()->implode(', '),
        ]);
    }

    /**
     * @return string|null
     */
    private function messageForUnrecognised(): ?string
    {
        $unrecognised = $this->unrecognised();

        if ($unrecognised->isEmpty()) {
            return null;
        }

        $key = (1 === $unrecognised->count()) ? 'singular' : 'plural';

        return trans(JsonApiValidation::translationKeyForRule($this, "unrecognised.{$key}"), [
            'types' => $unrecognised->sort()->implode(', '),
        ]);
    }

    /**
     * Are the fields allowed for the specified resource type?
     *
     * @param string $resourceType
     * @param string $fields
     * @return bool
     */
    protected function allowed(string $resourceType, string $fields): bool
    {
        return $this->notAllowed($resourceType, $fields)->isEmpty();
    }

    /**
     * Get the invalid fields for the resource type.
     *
     * @param string $resourceType
     * @param string $fields
     * @return Collection
     */
    protected function notAllowed(string $resourceType, string $fields): Collection
    {
        $fields = collect(explode(',', $fields));

        if (!$this->allowed->has($resourceType)) {
            $this->allowed[$resourceType] = $this->fieldsFor($resourceType);
        }

        $allowed = $this->allowed->get($resourceType);

        if (is_null($allowed)) {
            return collect();
        }

        $allowed = collect(Arr::wrap($allowed));

        return $fields->reject(fn($value) => $allowed->contains($value));
    }

    /**
     * Get the resource types that are not recognised.
     *
     * @return Collection
     */
    protected function unrecognised(): Collection
    {
        return collect($this->value ?? [])
            ->keys()
            ->reject(fn(string $resourceType) => $this->isResourceType($resourceType))
            ->values();
    }

    /**
     * Get the fields that are invalid.
     *
     * @return Collection
     */
    protected function invalid(): Collection
    {
        return collect($this->value ?? [])->map(function ($value, $key) {
            return $this->notAllowed($key, $value);
        })->flatMap(function (Collection $fields, $type) {
            return $fields->map(function ($field) use ($type) {
                return "{$type}.{$field}";
            });
        });
    }

    /**
     * @param string $resourceType
     * @return array
     */
    private function fieldsFor(string $resourceType): array
    {
        if ($this->isResourceType($resourceType)) {
            return collect($this->schemas
                ->schemaFor($resourceType)
                ->sparseFields()
            )->all();
        }

        return [];
    }

    /**
     * @param string $resourceType
     * @return bool
     */
    private function isResourceType(string $resourceType): bool
    {
        return $this->schemas && $this->schemas->exists($resourceType);
    }

}
