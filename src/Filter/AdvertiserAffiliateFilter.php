<?php

namespace TPerformant\API\Filter;

class AdvertiserAffiliateFilter extends CollectionFilter {
    protected function filterableFields() {
        return [
            'query' => 'query',
            'status' => 'status',
            'recruited' => 'affiliate_is_recruited'
        ];
    }
}
