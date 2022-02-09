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

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validation rules for this package.
    |
    */

    'invalid' => [
        'title' => 'Onverwerkbare Entiteit',
        'detail' => 'Het document was goed opgemaakt, maar bevat semantische fouten.',
        'code' => '',
    ],

    'resource_invalid' => [
        'title' => 'Onverwerkbare Entiteit',
        'detail' => 'Het document was goed opgemaakt, maar bevat semantische fouten.',
        'code' => '',
    ],

    'query_invalid' => [
        'title' => 'Ongeldige queryparameter',
        'detail' => 'De queryparameters van het verzoek zijn ongeldig.',
        'code' => '',
    ],

    'delete_invalid' => [
        'title' => 'Niet Verwijderbaar',
        'detail' => 'Deze resource kan niet worden verwijderd.',
        'code' => '',
    ],

    'allowed_field_sets' => [
        'default' => 'Spaarzame veldsets mogen alleen toegestane bevatten.',
        'singular' => 'Spaarzame veldset :values is niet toegestaan.',
        'plural' => 'Spaarzame veldsets :values zijn niet toegestaan.',

        'unrecognised' => [
            'singular' => 'Resource type :type wordt niet herkend.',
            'plural' => 'Resource types :types worden niet herkend.',
        ],
    ],

    'allowed_filter_parameters' => [
        'default' => 'Filterparameters mogen alleen toegestane bevatten.',
        'singular' => 'Filterparameter :values is niet toegestaan.',
        'plural' => 'Filterparameters :values zijn niet toegestaan.',
    ],

    'allowed_include_paths' => [
        'default' => 'Insluit-paden mogen alleen toegestane bevatten.',
        'singular' => 'Insluit-pad :values is niet toegestaan.',
        'plural' => 'Insluit-paden :values zijn niet toegestaan.',
    ],

    'allowed_sort_parameters' => [
        'default' => 'Sorteerparameters mogen alleen toegestane bevatten.',
        'singular' => 'Sorteerparameter :values is niet toegestaan.',
        'plural' => 'Sorteerparameters :values zijn niet toegestaan.',
    ],

    'allowed_page_parameters' => [
        'default' => 'Pagina-parameters mogen alleen toegestane bevatten.',
        'singular' => 'Pagina-parameter :values is niet toegestaan.',
        'plural' => 'Pagina-parameters :values zijn niet toegestaan.',
    ],

    /** @TODO requires translation */
    'allowed_countable_fields' => [
        'default' => 'Count fields must contain only allowed ones.',
        'singular' => 'Field :values is not countable.',
        'plural' => 'Fields :values are not countable.',
    ],

    'boolean_string' => 'Het :attribute moet waar of onwaar zijn.',

    /** @TODO requires translation */
    'client_id' => 'The :attribute format is invalid.',

    'date_time_iso_8601' => 'Het attribuut :attribute heeft geen geldig ISO 8601 datum/tijd formaat.',

    'json_boolean' => 'Het veld :attribute moet een boolean zijn.',

    'json_integer' => 'Het veld :attribute moet een geheel getal zijn.',

    'json_number' => 'Het veld :attribute moet een nummer zijn.',

    'parameter_not_supported' => 'Parameter :name is niet toegestaan.',

    'has_one' => 'Het veld :attribute moet een naar-één relatie zijn die :types resources bevat.',

    'has_many' => 'Het veld :attribute moet een naar-velen relatie zijn die :types resources bevat.',
];
