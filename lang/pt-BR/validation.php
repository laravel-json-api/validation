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
        'title' => 'Entidade Não Processável',
        'detail' => 'O documento estava bem formado mas contém erros semânticos.',
        'code' => '',
    ],

    'resource_invalid' => [
        'title' => 'Entidade Não Processável',
        'detail' => 'O documento estava bem formado mas contém erros semânticos.',
        'code' => '',
    ],

    'query_invalid' => [
        'title' => 'Parâmetro de consulta inválido',
        'detail' => 'Os parâmetros de solicitação de consulta não são válidos.',
        'code' => '',
    ],

    'delete_invalid' => [
        'title' => 'Não é possível deletar',
        'detail' => 'O recurso não pode ser deletado.',
        'code' => '',
    ],

    'allowed_field_sets' => [
        'default' => 'Conjuntos de campos esparsos devem conter apenas os permitidos.',
        'singular' => 'Conjunto de campos esparsos :values no é permitido.',
        'plural' => 'Conjuntos de campos esparsos :values valores não são permitidos.',

        'unrecognised' => [
            'singular' => 'Tipo de recurso :types não foi reconhecido.',
            'plural' => 'Tipos de recursos :types não foram reconhecidos.',
        ],
    ],

    'allowed_filter_parameters' => [
        'default' => 'Parâmetros do filtro devem conter apenas os permitidos.',
        'singular' => 'O parâmetro de filtro :values não é permitido.',
        'plural' => 'Os parâmetros de filtro :values não são permitidos.',
    ],

    'allowed_include_paths' => [
        'default' => 'Rotas a serem incluídas devem conter apenas aquelas permitidas.',
        'singular' => 'A rota :values incluída não é permitida.',
        'plural' => 'As rotas :values incluídas ​​não são permitidas.',
    ],

    'allowed_sort_parameters' => [
        'default' => 'Parâmetros de ordenação devem conter apenas os permitidos.',
        'singular' => 'O parâmetro de ordenação :values não é permitido.',
        'plural' => 'Os parâmetros de ordenação :values não são permitidos.',
    ],

    'allowed_page_parameters' => [
        'default' => 'Parâmetros de página devem conter apenas os permitidos.',
        'singular' => 'O parâmetro de página :values não é permitido.',
        'plural' => 'Os parâmetros de página :values não são permitidos.',
    ],

    'allowed_countable_fields' => [
        'default' => 'Campos de contagem devem conter apenas os permitidos.',
        'singular' => 'O campo :values não pode ser contabilizado.',
        'plural' => 'Os campos :values não podem ser contabilizados.',
    ],

    'boolean_string' => 'O campo :attribute deve ser verdadeiro ou falso.',

    'client_id' => 'O formato de :attribute não é válido.',

    'date_time_iso_8601' => 'O campo :attribute não tem um valor de data/hora ISO 8601 válido.',

    'json_boolean' => 'O campo :attribute deve ser booleano.',

    'json_integer' => 'O campo :attribute deve ser um inteiro.',

    'json_number' => 'O campo :attribute deve ser numérico.',

    'parameter_not_supported' => 'O parâmetro :name não é permitido.',

    'has_one' => 'O campo :attribute deve ser uma relação do tipo um-para-um que contenha os recursos :types.',

    'has_many' => 'O campo :attribute deve ser uma relação do tipo um-para-muitos de muitos que contenha os recursos :types.',
];
