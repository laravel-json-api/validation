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

use Illuminate\Contracts\Translation\Translator as TranslatorContract;
use Illuminate\Support\Collection;
use LaravelJsonApi\Core\Document\Error;
use LaravelJsonApi\Core\Support\Str;
use function in_array;
use function strtolower;

class Translator
{

    /**
     * @var TranslatorContract
     */
    private TranslatorContract $translator;

    /**
     * Translator constructor.
     *
     * @param TranslatorContract $translator
     */
    public function __construct(TranslatorContract $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Create an error for an invalid resource.
     *
     * @param string $path
     * @param string|null $detail
     *      the validation message (already translated).
     * @param array $failed
     *      rule failure details
     * @return Error
     */
    public function invalidResource(string $path, ?string $detail = null, array $failed = []): Error
    {
        return Error::make()
            ->setStatus(422)
            ->setTitle($this->trans('resource_invalid', 'title'))
            ->setDetail($detail ?: $this->trans('resource_invalid', 'detail'))
            ->setCode($this->trans('resource_invalid', 'code'))
            ->setSource($this->pointer($path))
            ->setMeta($failed ? compact('failed') : null);
    }

    /**
     * Translate validation failures.
     *
     * @param array $failures
     * @return Collection
     */
    public function validationFailures(array $failures): Collection
    {
        return collect($failures)
            ->map(fn($options, $rule) => $this->validationFailure($rule, $options))
            ->values();
    }

    /**
     * Translate an error member value.
     *
     * @param string $key
     *      the key for the JSON API error object.
     * @param string $member
     *      the JSON API error object member name.
     * @param array $replace
     * @param string|null $locale
     * @return string|null
     */
    private function trans(string $key, string $member, array $replace = [], ?string $locale = null)
    {
        $value = $this->translator->get(
            $key = "jsonapi::validation.{$key}.{$member}",
            $replace,
            $locale
        ) ?: null;

        return ($key !== $value) ? $value : null;
    }

    /**
     * Create a source pointer for the specified path and optional member at that path.
     *
     * @param string $path
     * @param string|null $member
     * @return array
     */
    private function pointer(string $path, ?string $member = null): array
    {
        /** Member can be '0' which is an empty string. */
        $withoutMember = is_null($member) || '' === $member;
        $pointer = !$withoutMember ? sprintf('%s/%s', rtrim($path, '/'), $member) : $path;

        return compact('pointer');
    }

    /**
     * @param string $rule
     * @param array|null $options
     * @return string[]
     */
    private function validationFailure(string $rule, ?array $options): array
    {
        $failure = ['rule' => $this->convertRuleName($rule)];

        if (!empty($options) && $this->failedRuleHasOptions($rule)) {
            $failure['options'] = $options;
        }

        return $failure;
    }

    /**
     * @param string $rule
     * @return string
     */
    private function convertRuleName(string $rule): string
    {
        return $this->translator->get(
            Str::dasherize(class_basename($rule))
        );
    }

    /**
     * Should options for the rule be displayed?
     *
     * @param string $rule
     * @return bool
     */
    private function failedRuleHasOptions(string $rule): bool
    {
        return !in_array(strtolower($rule), [
            'exists',
            'unique',
        ], true);
    }
}
