<?php

/*
|--------------------------------------------------------------------------
| Identidade da marca
|--------------------------------------------------------------------------
|
| Todo o visual do sistema é derivado destes valores. Para atender outra
| empresa, basta alterar as variáveis BRAND_* no .env — nenhuma view precisa
| ser editada.
|
*/

return [

    // Nome exibido (sidebar, títulos, rodapé)
    'name' => env('BRAND_NAME', 'LBF'),

    // Nome curto usado em espaços apertados (ex.: "LBF" em "LBF Portal")
    'short_name' => env('BRAND_SHORT_NAME', env('BRAND_NAME', 'LBF')),

    // Nome jurídico / razão social (rodapé e documentos)
    'legal_name' => env('BRAND_LEGAL_NAME', 'LBF Semijoias'),

    // Sufixo do produto na barra de título ("LBF Portal")
    'product' => env('BRAND_PRODUCT', 'Portal'),

    'tagline' => env('BRAND_TAGLINE', 'Elegância em cada detalhe.'),

    'description' => env(
        'BRAND_DESCRIPTION',
        'Um espaço criado para transformar ideias, conectar processos e acompanhar cada etapa com excelência.'
    ),

    // Ano de fundação exibido na tela de login (null para ocultar)
    'founded' => env('BRAND_FOUNDED', '2010'),

    // Caminho público do logo (SVG ou PNG com fundo transparente)
    'logo' => env('BRAND_LOGO', 'images/logo/logo.png'),

    'support_email' => env('BRAND_SUPPORT_EMAIL'),

    /*
    |--------------------------------------------------------------------------
    | Cores
    |--------------------------------------------------------------------------
    |
    | As cinco primeiras formam a paleta da marca. As três últimas são cores
    | funcionais (status), propositalmente dessaturadas para conviver com a
    | paleta terrosa.
    |
    */

    'colors' => [
        'dark' => env('BRAND_COLOR_DARK', '#3D3935'),
        'taupe' => env('BRAND_COLOR_TAUPE', '#857874'),
        'sand' => env('BRAND_COLOR_SAND', '#C1B2A2'),
        'cream' => env('BRAND_COLOR_CREAM', '#E7E3DF'),
        'white' => env('BRAND_COLOR_WHITE', '#FFFFFF'),
        'danger' => env('BRAND_COLOR_DANGER', '#B45353'),
        'success' => env('BRAND_COLOR_SUCCESS', '#4F7A5B'),
        'warning' => env('BRAND_COLOR_WARNING', '#B8894A'),
        'info' => env('BRAND_COLOR_INFO', '#6B7C8C'),
    ],

];
