<?php

namespace App\Infrastructure\Persistence\Doctrine\Helpers;


use Doctrine\ORM\QueryBuilder;


class AddressSearchHelper
{
    /**
     *  Applies flexible and tolerant address filtering to QueryBuilder.
     *
     * @param QueryBuilder $qb  The Current QueryBuilder
     * @param string $searchQuery The search string entered
     * @param string $addressAlias  The alias for the Address table in the QueryBuilder (e.g., ‘a’)
     */
    public static function applyAddressSearch(QueryBuilder $qb, string $searchQuery, string $addressAlias = 'a'): void
    {
        $rawQuery = trim($searchQuery);
        if (empty($rawQuery)) {
            return;
        }

        // Standardization: Replacing common punctuation marks and separators with spaces (replace each ", - . ; " -> "")
        $normalized = preg_replace('/[,;.\-]+/u', ' ', $rawQuery);

        // Tokenization: Splitting into Multiple Segments
        $tokens = preg_split('/\s+/u', $normalized, -1, PREG_SPLIT_NO_EMPTY);

        if (empty($tokens)) {
            return;
        }

        // Optional filtering of very short or empty words (e.g., retains ZIP codes, filters out noise)
        $tokens = array_values(array_filter($tokens, function (string $token) {
            return mb_strlen($token) >= 2 || is_numeric($token);
        }));

        //  Dynamic Construction of the SQL Clause for Each Token
        foreach ($tokens as $index => $token) {
            $paramName = sprintf('addr_token_%d', $index);
            $searchTerm = '%' . mb_strtolower($token) . '%';

            //  Each word (token) must appear in AT LEAST ONE of the address fields
            $qb->andWhere(sprintf('
                LOWER(%1$s.street) LIKE :%2$s OR 
                LOWER(%1$s.city) LIKE :%2$s OR 
                LOWER(%1$s.postalCode) LIKE :%2$s OR 
                LOWER(%1$s.country) LIKE :%2$s
            ', $addressAlias, $paramName))
            ->setParameter($paramName, $searchTerm);
        }
    }
}