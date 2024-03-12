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
use LaravelJsonApi\Contracts\Schema\Schema;
use LaravelJsonApi\Contracts\Validation\ResourceErrorFactory as ResourceErrorFactoryContract;
use LaravelJsonApi\Core\Document\ErrorList;

class ResourceErrorFactory implements ResourceErrorFactoryContract
{
    /**
     * ResourceErrorFactory constructor
     *
     * @param Translator $translator
     */
    public function __construct(private readonly Translator $translator)
    {
    }

    /**
     * @inheritDoc
     */
    public function make(Schema $schema, Validator $validator): ErrorList
    {
        $iterator = new SchemaErrorIterator(
            $this->translator,
            $schema,
            $validator,
        );

        return new ErrorList(...$iterator);
    }
}
