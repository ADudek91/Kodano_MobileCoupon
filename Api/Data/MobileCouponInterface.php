<?php

declare(strict_types=1);

namespace Kodano\MobileCoupon\Api\Data;

/**
 * DTO representing a mobile-only coupon.
 * @api
 */
interface MobileCouponInterface
{
    public const COUPON_ID = 'coupon_id';
    public const CODE = 'code';
    public const RULE_ID = 'rule_id';
    public const ON_MOBILE = 'on_mobile';

    /**
     * @return int
     */
    public function getCouponId(): int;

    /**
     * @param int $couponId
     * @return $this
     */
    public function setCouponId(int $couponId): static;

    /**
     * @return string
     */
    public function getCode(): string;

    /**
     * @param string $code
     * @return $this
     */
    public function setCode(string $code): static;

    /**
     * @return int
     */
    public function getRuleId(): int;

    /**
     * @param int $ruleId
     * @return $this
     */
    public function setRuleId(int $ruleId): static;

    /**
     * @return int
     */
    public function getOnMobile(): int;

    /**
     * @param int $onMobile
     * @return $this
     */
    public function setOnMobile(int $onMobile): static;
}

