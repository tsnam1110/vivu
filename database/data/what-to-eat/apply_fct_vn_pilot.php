<?php

declare(strict_types=1);

/**
 * Merge FCT VN pilot dish calories into calories_fact_a.json.
 *
 * Prefer full rebuild: php database/data/what-to-eat/build_fct_vn_phase_b.php
 * This script only re-applies existing recipes_fct_vn_pilot_v1.json.
 *
 * Run: php database/data/what-to-eat/apply_fct_vn_pilot.php
 */

$factsDir = __DIR__.DIRECTORY_SEPARATOR.'facts';
$caloriesPath = $factsDir.DIRECTORY_SEPARATOR.'calories_fact_a.json';
$pilotPath = $factsDir.DIRECTORY_SEPARATOR.'recipes_fct_vn_pilot_v1.json';

$calories = json_decode(file_get_contents($caloriesPath), true, 512, JSON_THROW_ON_ERROR);
$pilot = json_decode(file_get_contents($pilotPath), true, 512, JSON_THROW_ON_ERROR);

$updated = 0;
foreach ($pilot['by_slug'] as $slug => $row) {
    if (! is_string($slug) || $slug === '' || ! is_array($row)) {
        continue;
    }
    $prev = $calories['by_slug'][$slug] ?? null;
    $calories['by_slug'][$slug] = [
        'calories_kcal' => (int) $row['calories_kcal'],
        'serving_grams' => (int) $row['serving_grams'],
        'facts' => $row['facts'],
    ];
    if (is_array($prev) && isset($prev['facts'][0])) {
        $calories['by_slug'][$slug]['facts'][0]['replaced_from'] = [
            'calories_kcal' => $prev['calories_kcal'] ?? null,
            'method' => $prev['facts'][0]['method'] ?? null,
            'source_title' => $prev['facts'][0]['source_title'] ?? null,
        ];
    }
    $updated++;
    echo sprintf(
        "%s: %s → %d kcal / %dg\n",
        $slug,
        isset($prev['calories_kcal']) ? (string) $prev['calories_kcal'] : 'new',
        (int) $row['calories_kcal'],
        (int) $row['serving_grams'],
    );
}

$calories['kb_version'] = '2.3.0-fact-a';
$calories['fct_vn_pilot'] = [
    'kb_version' => $pilot['kb_version'] ?? '1.1.0',
    'applied_at' => date('Y-m-d'),
    'slugs' => array_keys($pilot['by_slug']),
    'note' => 'Pilot dishes use VN FCT 2007; prefer build_fct_vn_phase_b.php for full rebuild',
];

file_put_contents(
    $caloriesPath,
    json_encode($calories, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)."\n",
);

echo "updated={$updated} kb={$calories['kb_version']}\n";
