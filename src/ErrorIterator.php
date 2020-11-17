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
use Illuminate\Contracts\Validation\Validator;
use IteratorAggregate;
use JsonSerializable;
use LaravelJsonApi\Contracts\ErrorProvider;
use LaravelJsonApi\Core\Document\Error;
use LaravelJsonApi\Core\Document\ErrorList;

abstract class ErrorIterator implements IteratorAggregate, Countable, Arrayable, JsonSerializable, ErrorProvider
{

    /**
     * @var Translator
     */
    protected Translator $translator;

    /**
     * @var Validator
     */
    protected Validator $validator;

    /**
     * @var bool
     */
    private bool $includeFailed;

    /**
     * Create a JSON API error.
     *
     * @param string $key
     * @param string $message
     * @param array $failed
     * @return Error
     */
    abstract protected function createError(string $key, string $message, array $failed): Error;

    /**
     * ErrorIterator constructor.
     *
     * @param Translator $translator
     * @param Validator $validator
     */
    public function __construct(Translator $translator, Validator $validator)
    {
        $this->translator = $translator;
        $this->validator = $validator;
        $this->includeFailed = JsonApiValidation::$validationFailures;
    }

    /**
     * Include rule failure meta data.
     *
     * @param bool $failed
     * @return $this
     */
    public function withFailureMeta(bool $failed = true): self
    {
        $this->includeFailed = $failed;

        return $this;
    }

    /**
     * Do not include rule failure meta data.
     *
     * @return $this
     */
    public function withoutFailureMeta(): self
    {
        return $this->withFailureMeta(false);
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
     * @inheritDoc
     */
    public function getIterator()
    {
        $failed = $this->failed();

        foreach ($this->validator->errors()->messages() as $key => $messages) {
            $failures = $this->translator->validationFailures($failed[$key] ?? []);

            foreach ($messages as $message) {
                yield $this->createError(
                    $key,
                    $message,
                    $failures->shift() ?: []
                );
            }
        }
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
        return count($this->all());
    }

    /**
     * @inheritDoc
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
