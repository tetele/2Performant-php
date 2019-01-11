<?php

namespace TPerformant\API\Model;

use TPerformant\API\HTTP\Advertiser as ApiHttpAdvertiser;

class AdvertiserAffiliate extends Affiliate {
    /**
     * @inheritdoc
     */
    public function __construct($data, ApiHttpAdvertiser $user = null) {
        parent::__construct($data, $user);
    }

    protected $status;
    protected $clicks;
    protected $salesCount;
    protected $salesAmount;
    protected $conversionsCount;
    protected $commissionsAmount;
    protected $costPerSale;
    protected $customCommission;
    protected $customCookieLife;
    protected $customConditions;
    protected $customCommissionConditions;
    protected $commissionLeadAmount;
    protected $commissionSaleRate;
    protected $newAffiliateRecruited;
    protected $existingAffiliateRecruited;
}
