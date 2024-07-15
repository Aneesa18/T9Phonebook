<?php

namespace App\Phonebook;

/**
 * T9Search class for generating possible names from T9 input.
 */
class T9search {
    private array $t9Map = [
        '2' => 'abc', '3' => 'def', '4' => 'ghi',
        '5' => 'jkl', '6' => 'mno', '7' => 'pqrs',
        '8' => 'tuv', '9' => 'wxyz'
    ];

    /**
     * Get all matching name combinations from T9 input.
     *
     * @param string $digits The T9 digits input
     * @return array The matching name combinations
     */
    public function getPossibleNames(string $digits): array {
        if (empty($digits)) return [];
        $combinations = [''];
        foreach (str_split($digits) as $digit) {
            if (!isset($this->t9Map[$digit])) continue;
            $letters = str_split($this->t9Map[$digit]);
            $newCombinations = [];
            foreach ($combinations as $combination) {
                foreach ($letters as $letter) {
                    $newCombinations[] = $combination . $letter;
                }
            }
            $combinations = $newCombinations;
        }
        return array_map(function ($comb) {
            return "$comb%";
        }, $combinations);
    }
}
