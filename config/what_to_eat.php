<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Allow unverified sensitive fields in seed/import
    |--------------------------------------------------------------------------
    |
    | Production / default: false — calo, ngũ hành, thermal, recipe… only when
    | provenance is present (see docs/features/what-to-eat-seed-and-kb.md).
    | Local experiment only: SEED_ALLOW_UNVERIFIED=true
    |
    */
    'seed_allow_unverified' => (bool) env('SEED_ALLOW_UNVERIFIED', false),

    /*
    |--------------------------------------------------------------------------
    | Ruleset version (documentation / logging)
    |--------------------------------------------------------------------------
    */
    'ruleset_version' => '0.3.0',

    /*
    |--------------------------------------------------------------------------
    | Implicit staple kcal (optional verified constant)
    |--------------------------------------------------------------------------
    |
    | Only set when product has an approved estimate (e.g. 1 chén cơm). Null = UI
    | shows staple label without kcal (data-gate).
    |
    */
    // Aligned with Fact-A com-trang (150 g cooked white rice ≈ 206 kcal):
    // FCT VN 1004 dry 344 kcal/100g ÷ vivu-yield-v1 2.5 → ~137.6 × 1.5 ≈ 206 (medium).
    'implicit_rice_kcal' => env('WHAT_TO_EAT_IMPLICIT_RICE_KCAL') !== null
        ? (int) env('WHAT_TO_EAT_IMPLICIT_RICE_KCAL')
        : 206,

    'implicit_rice_grams' => (int) env('WHAT_TO_EAT_IMPLICIT_RICE_GRAMS', 150),

];
