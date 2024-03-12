<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Validation;

use Illuminate\Contracts\Validation\Validator;
use LaravelJsonApi\Contracts\Validation\DestroyErrorFactory as DestroyErrorFactoryContract;
use LaravelJsonApi\Core\Document\ErrorList;

class DestroyErrorFactory implements DestroyErrorFactoryContract
{
    /**
     * DestroyErrorFactory constructor
     *
     * @param Translator $translator
     */
    public function __construct(private readonly Translator $translator)
    {
    }

    /**
     * @inheritDoc
     */
    public function make(Validator $validator): ErrorList
    {
        $iterator = new DeleteErrorIterator(
            $this->translator,
            $validator,
        );

        return new ErrorList(...$iterator);
    }
}
