<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Validation\Tests\Unit;

use LaravelJsonApi\Contracts\Schema\Schema;
use LaravelJsonApi\Validation\JsonApiValidation;
use LaravelJsonApi\Validation\Rules\ClientId;
use LaravelJsonApi\Validation\Tests\Integration\TestCase;

class JsonApiValidationTest extends TestCase
{

    public function testTranslationKey(): void
    {
        $rule = new ClientId($this->createMock(Schema::class));

        $this->assertSame(
            'jsonapi-validation::validation.client_id',
            JsonApiValidation::translationKeyForRule($rule)
        );

        $this->assertSame(
            'jsonapi-validation::validation.client_id.foo',
            JsonApiValidation::translationKeyForRule($rule, 'foo')
        );
    }
}
