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
        'title' => 'Entité non traitable',
        'detail' => 'Le document est correctement structuré mais contient des erreurs sémantiques.',
        'code' => '',
    ],

    'resource_invalid' => [
        'title' => 'Entité non traitable',
        'detail' => 'Le document est correctement structuré mais contient des erreurs sémantiques.',
        'code' => '',
    ],

    'query_invalid' => [
        'title' => 'Paramètre de requête invalide',
        'detail' => 'Les paramètres de la requête ne sont pas valides.',
        'code' => '',
    ],

    'delete_invalid' => [
        'title' => 'Non supprimable',
        'detail' => 'La ressource ne peut être supprimée.',
        'code' => '',
    ],

    'allowed_field_sets' => [
        'default' => 'Certains champs soumis ne sont pas autorisés.',
        'singular' => "Le champ soumis :values n'est pas autorisé.",
        'plural' => 'Les champs soumis :values ne sont pas autorisés.',

        'unrecognised' => [
            'singular' => "Le type de ressource :types n'est pas reconnu.",
            'plural' => 'Les types de ressources :types ne sont pas reconnus.',
        ],
    ],

    'allowed_filter_parameters' => [
        'default' => 'Certains paramètres de filtre de sont pas autorisés.',
        'singular' => "Le paramètre de filtre :values n'est pas autorisé.",
        'plural' => 'Les paramètres de filtre :values ne sont pas autorisés.',
    ],

    'allowed_include_paths' => [
        'default' => 'Certains chemins inclus ne sont pas autorisés.',
        'singular' => "Le chemin inclus :values n'est pas autorisé.",
        'plural' => 'Les chemins inclus :values ne sont pas autorisés.',
    ],

    'allowed_sort_parameters' => [
        'default' => 'Certains paramètres de tri ne sont pas autorisés.',
        'singular' => "Le paramètre de tri :values n'est pas autorisé.",
        'plural' => 'Les paramètres de tri :values ne sont pas autorisés.',
    ],

    'allowed_page_parameters' => [
        'default' => 'Certains paramètres de pagination ne sont pas autorisés.',
        'singular' => "Le paramètre de pagination :values n'est pas autorisé.",
        'plural' => 'Les paramètres de pagination :values ne sont pas autorisés.',
    ],

    /** @TODO requires translation */
    'allowed_countable_fields' => [
        'default' => 'Count fields must contain only allowed ones.',
        'singular' => 'Field :values is not countable.',
        'plural' => 'Fields :values are not countable.',
    ],

    'boolean_string' => 'Le champ :attribute doit être vrai ou faux.',

    'client_id' => "Le format de :attribute n'est pas valide.",

    'date_time_iso_8601' => ":attribute n'est pas au format ISO 8601 de date et heure.",

    'json_boolean' => 'Le champ :attribute doit être un booléen.',

    'json_integer' => 'Le champ :attribute doit être un entier.',

    'json_number' => 'Le champ :attribute doit être un nombre.',

    'parameter_not_supported' => "Le paramètre :name n'est pas autorisé.",

    'has_one' => 'Le champ :attribute doit être une relation "to-one" contenant des ressources de type :types.',

    'has_many' => 'Le champ :attribute doit être une relation "to-many" contenant des ressources de type :types.',
];
