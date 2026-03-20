<?php

declare(strict_types=1);

namespace Kodano\MobileCoupon\Model\Data;

use Kodano\MobileCoupon\Api\Data\MobileCouponInterface;
use Magento\Framework\DataObject;

/**
 * DTO implementation for mobile-only coupon data.
 */
class MobileCoupon extends DataObject implements MobileCouponInterface
{
    /**
     * @return int
     */
    public function getCouponId(): int
    {
        return (int)$this->getData(self::COUPON_ID);
    }

    /**
     * @param int $couponId
     * @return $this
     */
    public function setCouponId(int $couponId): static
    {
        return $this->setData(self::COUPON_ID, $couponId);
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return (string)$this->getData(self::CODE);
    }

    /**
     * @param string $code
     * @return $this
     */
    public function setCode(string $code): static
    {
        return $this->setData(self::CODE, $code);
    }

    /**
     * @return int
     */
    public function getRuleId(): int
    {
        return (int)$this->getData(self::RULE_ID);
    }

    /**
     * @param int $ruleId
     * @return $this
     */
    public function setRuleId(int $ruleId): static
    {
        return $this->setData(self::RULE_ID, $ruleId);
    }

    /**
     * @return int
     */
    public function getOnMobile(): int
    {
        return (int)$this->getData(self::ON_MOBILE);
    }

    /**
     * @param int $onMobile
     * @return $this
     */
    public function setOnMobile(int $onMobile): static
    {
        return $this->setData(self::ON_MOBILE, $onMobile);
    }
}

