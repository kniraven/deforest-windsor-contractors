<?php

namespace App\Support;

class ListingAnswerInterpreter
{
    public static function deriveOwnerLocal(?string $localConnectionAnswer): bool
    {
        return $localConnectionAnswer === 'yes';
    }

    public static function deriveLocallyIndependent(
        string $listingType,
        ?string $independentOperationAnswer,
        ?string $parentAffiliationAnswer
    ): bool {
        if ($listingType === 'individual') {
            return $independentOperationAnswer === 'yes';
        }

        return $independentOperationAnswer === 'yes'
            && $parentAffiliationAnswer === 'no';
    }
}