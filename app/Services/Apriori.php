<?php

namespace App\Services;

class Apriori
{
    private $transactions;
    private $minSupport;

    public function __construct($transactions, $minSupport) {
        $this->transactions = $transactions;
        $this->minSupport = $minSupport;
    }

    // Menyusun itemset dari transaksi (item pairs)
    public function generateItemsets() {
        $itemsets = [];
        foreach ($this->transactions as $transaction) {
            // Generate pairs of items from the transaction
            $transactionItemsets = $this->generatePairs($transaction);
            foreach ($transactionItemsets as $pair) {
                $itemsets[] = $pair;
            }
        }
        return $itemsets;
    }

    // Generate pairs of items from a single transaction
    public function generatePairs($transaction) {
        $pairs = [];
        $count = count($transaction);
        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                $pairs[] = [$transaction[$i], $transaction[$j]];
            }
        }
        return $pairs;
    }

    // Menghitung support dari itemset
    public function countSupport($itemsets) {
        $supportCount = [];
        foreach ($itemsets as $itemset) {
            $itemsetKey = implode(",", $itemset);  // Convert array to a string key for pairs
            if (!isset($supportCount[$itemsetKey])) {
                $supportCount[$itemsetKey] = 0;
            }
            foreach ($this->transactions as $transaction) {
                if (in_array($itemset[0], $transaction) && in_array($itemset[1], $transaction)) {
                    $supportCount[$itemsetKey]++;
                }
            }
        }
        return $supportCount;
    }

    // Menyaring itemset yang memenuhi minimal support
    public function filterBySupport($supportCount) {
        return array_filter($supportCount, function($count) {
            return $count >= $this->minSupport;
        });
    }

    // Menjalankan algoritma Apriori
    public function run() {
        $itemsets = $this->generateItemsets();  // Membuat itemset
        $supportCount = $this->countSupport($itemsets);  // Menghitung support

        // Debugging: Log the itemsets and support counts
        // dd($itemsets, $supportCount);

        return $this->filterBySupport($supportCount);  // Menyaring itemset yang memenuhi syarat support
    }
}
