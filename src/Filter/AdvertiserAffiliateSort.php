<?php

namespace TPerformant\API\Filter;

class AdvertiserAffiliateSort extends CollectionSort {
    protected function sortableFields() {
        return [
            'username' => 'username',
            'clicks' => 'clicks',
            'conversions' => 'conversions_count',
            'saleAmounts' => 'sale_amounts',
            'commissionAmounts' => 'commission_amounts',
            'cps' => 'cps',
            'customConditions' => 'custom_conditions'
        ];
    }
}
