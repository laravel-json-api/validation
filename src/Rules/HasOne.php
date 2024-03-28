<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Validation\Rules;

use Illuminate\Contracts\Validation\Rule;
use LaravelJsonApi\Contracts\Schema\PolymorphicRelation;
use LaravelJsonApi\Contracts\Schema\Schema;
use LaravelJsonApi\Contracts\Schema\Relation;
use LaravelJsonApi\Validation\JsonApiValidation;
use LogicException;

class HasOne implements Rule
{
    /**
     * @var array|null
     */
    private ?array $types = null;

    /**
     * HasOne constructor.
     *
     * @param Schema|Relation $schemaOrRelation
     */
    public function __construct(private readonly Schema|Relation $schemaOrRelation)
    {
    }

    /**
     * @inheritDoc
     */
    public function passes($attribute, $value): bool
    {
        if (!is_null($value) && !is_array($value)) {
            return false;
        }

        $relation = $this->relation($attribute);

        if ($relation instanceof PolymorphicRelation) {
            $this->types = $relation->inverseTypes();
        } else {
            $this->types = [$relation->inverse()];
        }

        return $this->accept($value);
    }

    /**
     * @inheritDoc
     */
    public function message()
    {
        return trans(JsonApiValidation::translationKeyForRule($this), [
            'types' => collect($this->types)->implode(', '),
        ]);
    }

    /**
     * Accept the data value.
     *
     * @param array|null $data
     * @return bool
     */
    protected function accept(?array $data): bool
    {
        if (is_null($data)) {
            return true;
        }

        return $this->acceptType($data);
    }

    /**
     * @param $data
     * @return bool
     */
    protected function acceptType($data): bool
    {
        return is_array($data) && collect($this->types)->containsStrict(
            $data['type'] ?? null
        );
    }

    /**
     * @param string $name
     * @return Relation
     */
    private function relation(string $name): Relation
    {
        if ($this->schemaOrRelation instanceof Schema) {
            return $this->schemaOrRelation->relationship($name);
        }

        if ($this->schemaOrRelation->name() === $name) {
            return $this->schemaOrRelation;
        }

        throw new LogicException(sprintf(
            'Expecting to validate relation %s but received %s.',
            $this->schemaOrRelation->name(),
            $name,
        ));
    }
}
