<?php

declare(strict_types=1);

namespace Kodano\MobileCoupon\Plugin\DataProvider;

use Magento\SalesRule\Model\Rule\DataProvider;
use Magento\SalesRule\Model\Rule;

/**
 * Plugin for DataProvider to add 'on_mobile' field from the primary coupon.
 */
class RuleDataProviderPlugin
{
    /**
     * After getData, add the 'on_mobile' attribute from the primary coupon if it exists.
     *
     * @param DataProvider $subject
     * @param array|null $result
     * @return array|null
     */
    public function afterGetData(DataProvider $subject, ?array $result): ?array
    {
        if ($result === null) {
            return null;
        }

        foreach ($result as $ruleId => &$ruleData) {
            // The DataProvider loads the full rule object into its collection
            /** @var Rule|null $rule */
            $rule = $subject->getCollection()->getItemById($ruleId);

            if ($rule && $rule->getId()) {
                if ($rule->getCouponType() == Rule::COUPON_TYPE_SPECIFIC && !$rule->getUseAutoGeneration()) {
                    $primaryCoupon = $rule->getPrimaryCoupon();
                    if ($primaryCoupon && $primaryCoupon->getId()) {
                        $ruleData['on_mobile'] = $primaryCoupon->getData('on_mobile');
                    }
                }
            }
        }

        return $result;
    }
}

