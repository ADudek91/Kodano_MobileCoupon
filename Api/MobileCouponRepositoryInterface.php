<?php

declare(strict_types=1);

namespace Kodano\MobileCoupon\Api;

use Kodano\MobileCoupon\Api\Data\MobileCouponInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * REST API service contract for mobile-only coupons.
 * @api
 */
interface MobileCouponRepositoryInterface
{
    /**
     * Return list of all coupons marked as mobile-only.
     *
     * @return \Kodano\MobileCoupon\Api\Data\MobileCouponInterface[]
     */
    public function getList(): array;

    /**
     * Mark an existing coupon as mobile-only by coupon code.
     *
     * @param string $couponCode
     * @return \Kodano\MobileCoupon\Api\Data\MobileCouponInterface
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     */
    public function save(string $couponCode): MobileCouponInterface;

    /**
     * Remove mobile-only flag from a coupon by coupon code.
     *
     * @param string $couponCode
     * @return bool
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    public function delete(string $couponCode): bool;
}

