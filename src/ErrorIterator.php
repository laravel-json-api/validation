<?php
/*
 * Copyright 2020 Cloud Creativity Limited
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

namespace LaravelJsonApi\Validation;

use Countable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use IteratorAggregate;
use LaravelJsonApi\Contracts\Schema\Schema;
use LaravelJsonApi\Core\Document\Error;
use LaravelJsonApi\Core\Document\ErrorList;

class ErrorIterator implements IteratorAggregate, Countable, Arrayable, \JsonSerializable
{

    /**
     * @var Schema
     */
    private Schema $schema;

    /**
     * @var ValidatorContract
     */
    private ValidatorContract $validator;

    /**
     * @var Translator
     */
    private Translator $translator;

    /**
     * @var bool
     */
    private bool $includeFailed = false;

    /**
     * @param Schema $schema
     * @param ValidatorContract $validator
     * @return ErrorIterator
     */
    public static function make(Schema $schema, ValidatorContract $validator): self
    {
        return new self($schema, $validator, app(Translator::class));
    }

    /**
     * ErrorIterator constructor.
     *
     * @param Schema $schema
     * @param ValidatorContract $validator
     * @param Translator $translator
     */
    public function __construct(Schema $schema, ValidatorContract $validator, Translator $translator)
    {
        $this->schema = $schema;
        $this->validator = $validator;
        $this->translator = $translator;
    }

    /**
     * Include rule failure meta data.
     *
     * @param bool $failed
     * @return $this
     */
    public function withFailed(bool $failed = true): self
    {
        $this->includeFailed = $failed;

        return $this;
    }

    /**
     * @return array
     */
    public function failed(): array
    {
        if ($this->includeFailed) {
            return $this->validator->failed();
        }

        return [];
    }

    /**
     * @return Error|null
     */
    public function first(): ?Error
    {
        foreach ($this as $error) {
            return $error;
        }

        return null;
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return iterator_to_array($this);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->validator->errors());
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        $failed = $this->failed();

        foreach ($this->validator->errors()->messages() as $key => $messages) {
            $failures = $this->translator->validationFailures($failed[$key] ?? []);

            foreach ($messages as $message) {
                $currentFailure = $failures->shift() ?: [];

                yield $this->translator->invalidResource(
                    SourcePointer::make($this->schema, $key)->withPrefix('/data')->toString(),
                    $message,
                    $currentFailure
                );
            }
        }
    }

    /**
     * @return ErrorList
     */
    public function toErrors(): ErrorList
    {
        return new ErrorList(...$this->all());
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return collect($this)->toArray();
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return collect($this)->jsonSerialize();
    }

}
