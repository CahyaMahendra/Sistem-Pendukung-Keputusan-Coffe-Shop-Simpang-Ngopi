<?php

function getRandomIndex($n)
{
    $RI = [
        1 => 0.00, 2 => 0.00, 3 => 0.58, 4 => 0.90, 5 => 1.12,
        6 => 1.24, 7 => 1.32, 8 => 1.41, 9 => 1.45, 10 => 1.49,
    ];
    return $RI[$n] ?? 1.49;
}

/**
 *
 * @param array $matrix  matriks NxN, $matrix[$i][$j] = nilai perbandingan kriteria i terhadap j
 * @return array [
 *      'weights'  => array bobot prioritas per kriteria (index sama dengan urutan matrix),
 *      'lambdaMax'=> float,
 *      'CI'       => float,
 *      'RI'       => float,
 *      'CR'       => float,
 *      'consistent'=> bool (CR <= 0.1 dianggap konsisten)
 * ]
 */
function hitungAHP(array $matrix)
{
    $n = count($matrix);

 
    $colSum = array_fill(0, $n, 0.0);
    for ($j = 0; $j < $n; $j++) {
        for ($i = 0; $i < $n; $i++) {
            $colSum[$j] += $matrix[$i][$j];
        }
    }


    $normalized = [];
    for ($i = 0; $i < $n; $i++) {
        for ($j = 0; $j < $n; $j++) {
            $normalized[$i][$j] = $colSum[$j] != 0 ? $matrix[$i][$j] / $colSum[$j] : 0;
        }
    }


    $weights = [];
    for ($i = 0; $i < $n; $i++) {
        $weights[$i] = array_sum($normalized[$i]) / $n;
    }


    $weightedSum = array_fill(0, $n, 0.0);
    for ($i = 0; $i < $n; $i++) {
        for ($j = 0; $j < $n; $j++) {
            $weightedSum[$i] += $matrix[$i][$j] * $weights[$j];
        }
    }


    $consistencyVector = [];
    for ($i = 0; $i < $n; $i++) {
        $consistencyVector[$i] = $weights[$i] != 0 ? $weightedSum[$i] / $weights[$i] : 0;
    }

    $lambdaMax = array_sum($consistencyVector) / $n;
    $CI = ($lambdaMax - $n) / ($n - 1 == 0 ? 1 : ($n - 1));
    $RI = getRandomIndex($n);
    $CR = $RI != 0 ? $CI / $RI : 0;

    return [
        'weights'    => $weights,
        'lambdaMax'  => $lambdaMax,
        'CI'         => $CI,
        'RI'         => $RI,
        'CR'         => $CR,
        'consistent' => $CR <= 0.1,
    ];
}

/**
 * Menghitung perangkingan TOPSIS.
 *
 * @param array $decisionMatrix  matriks keputusan, $decisionMatrix[$altIndex][$critIndex] = nilai
 * @param array $weights         bobot per kriteria (hasil AHP), index sama dengan urutan kolom kriteria
 * @param array $types           jenis kriteria per kolom: 'benefit' atau 'cost'
 * @return array [
 *   'normalized'      => matriks ternormalisasi,
 *   'weightedNormalized' => matriks ternormalisasi terbobot,
 *   'idealPositive'   => array nilai solusi ideal positif per kriteria,
 *   'idealNegative'   => array nilai solusi ideal negatif per kriteria,
 *   'distancePositive'=> array jarak D+ per alternatif,
 *   'distanceNegative'=> array jarak D- per alternatif,
 *   'preference'      => array nilai preferensi V per alternatif,
 * ]
 */
function hitungTOPSIS(array $decisionMatrix, array $weights, array $types)
{
    $m = count($decisionMatrix);       // jumlah alternatif
    $n = count($weights);              // jumlah kriteria


    $denominator = array_fill(0, $n, 0.0);
    for ($j = 0; $j < $n; $j++) {
        $sumSq = 0.0;
        for ($i = 0; $i < $m; $i++) {
            $sumSq += pow($decisionMatrix[$i][$j], 2);
        }
        $denominator[$j] = sqrt($sumSq);
    }

    $normalized = [];
    for ($i = 0; $i < $m; $i++) {
        for ($j = 0; $j < $n; $j++) {
            $normalized[$i][$j] = $denominator[$j] != 0
                ? $decisionMatrix[$i][$j] / $denominator[$j]
                : 0;
        }
    }


    $weightedNormalized = [];
    for ($i = 0; $i < $m; $i++) {
        for ($j = 0; $j < $n; $j++) {
            $weightedNormalized[$i][$j] = $normalized[$i][$j] * $weights[$j];
        }
    }

 
    $idealPositive = array_fill(0, $n, 0.0);
    $idealNegative = array_fill(0, $n, 0.0);
    for ($j = 0; $j < $n; $j++) {
        $col = array_column($weightedNormalized, $j);
        if ($types[$j] === 'benefit') {
            $idealPositive[$j] = max($col);
            $idealNegative[$j] = min($col);
        } else { // cost
            $idealPositive[$j] = min($col);
            $idealNegative[$j] = max($col);
        }
    }


    $distancePositive = [];
    $distanceNegative = [];
    for ($i = 0; $i < $m; $i++) {
        $sumPos = 0.0;
        $sumNeg = 0.0;
        for ($j = 0; $j < $n; $j++) {
            $sumPos += pow($weightedNormalized[$i][$j] - $idealPositive[$j], 2);
            $sumNeg += pow($weightedNormalized[$i][$j] - $idealNegative[$j], 2);
        }
        $distancePositive[$i] = sqrt($sumPos);
        $distanceNegative[$i] = sqrt($sumNeg);
    }


    $preference = [];
    for ($i = 0; $i < $m; $i++) {
        $total = $distancePositive[$i] + $distanceNegative[$i];
        $preference[$i] = $total != 0 ? $distanceNegative[$i] / $total : 0;
    }

    return [
        'normalized'         => $normalized,
        'weightedNormalized' => $weightedNormalized,
        'idealPositive'      => $idealPositive,
        'idealNegative'      => $idealNegative,
        'distancePositive'   => $distancePositive,
        'distanceNegative'   => $distanceNegative,
        'preference'         => $preference,
    ];
}

/** Helper: format angka desimal untuk ditampilkan */
function fnum($n, $decimals = 4)
{
    return number_format((float)$n, $decimals, ',', '.');
}
