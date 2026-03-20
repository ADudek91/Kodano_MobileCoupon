<?php

declare(strict_types=1);

namespace Kodano\MobileCoupon\Model;

use Kodano\MobileCoupon\Api\Data\MobileCouponInterface;
use Kodano\MobileCoupon\Api\Data\MobileCouponInterfaceFactory;
use Kodano\MobileCoupon\Api\MobileCouponRepositoryInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\SalesRule\Model\CouponFactory;
use Magento\SalesRule\Model\ResourceModel\Coupon as CouponResource;
use Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory as CouponCollectionFactory;
use Psr\Log\LoggerInterface;

/**
 * Repository implementation for mobile-only coupons.
 */
class MobileCouponRepository implements MobileCouponRepositoryInterface
{
    public function __construct(
        private readonly CouponFactory $couponFactory,
        private readonly CouponResource $couponResource,
        private readonly CouponCollectionFactory $couponCollectionFactory,
        private readonly MobileCouponInterfaceFactory $mobileCouponFactory,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getList(): array
    {
        $collection = $this->couponCollectionFactory->create();
        $collection->addFieldToFilter('on_mobile', ['eq' => 1]);

        $result = [];
        foreach ($collection as $coupon) {
            $result[] = $this->hydrate($coupon);
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function save(string $couponCode): MobileCouponInterface
    {
        $coupon = $this->couponFactory->create();
        $this->couponResource->load($coupon, $couponCode, 'code');

        if (!$coupon->getId()) {
            throw new NoSuchEntityException(
                __('Coupon with code "%1" does not exist.', $couponCode)
            );
        }

        try {
            $coupon->setData('on_mobile', 1);
            $this->couponResource->save($coupon);
        } catch (\Exception $e) {
            $this->logger->error(
                'Could not save mobile flag for coupon.',
                ['coupon_code' => $couponCode, 'exception' => $e->getMessage()]
            );
            throw new CouldNotSaveException(
                __('Could not mark coupon "%1" as mobile-only.', $couponCode),
                $e
            );
        }

        return $this->hydrate($coupon);
    }

    /**
     * @inheritDoc
     */
    public function delete(string $couponCode): bool
    {
        $coupon = $this->couponFactory->create();
        $this->couponResource->load($coupon, $couponCode, 'code');

        if (!$coupon->getId()) {
            throw new NoSuchEntityException(
                __('Coupon with code "%1" does not exist.', $couponCode)
            );
        }

        try {
            $coupon->setData('on_mobile', 0);
            $this->couponResource->save($coupon);
        } catch (\Exception $e) {
            $this->logger->error(
                'Could not remove mobile flag from coupon.',
                ['coupon_code' => $couponCode, 'exception' => $e->getMessage()]
            );
            throw new CouldNotDeleteException(
                __('Could not remove mobile-only flag from coupon "%1".', $couponCode),
                $e
            );
        }

        return true;
    }

    /**
     * Map a coupon model to MobileCouponInterface DTO.
     *
     * @param \Magento\SalesRule\Model\Coupon $coupon
     * @return MobileCouponInterface
     */
    private function hydrate(\Magento\SalesRule\Model\Coupon $coupon): MobileCouponInterface
    {
        /** @var MobileCouponInterface $dto */
        $dto = $this->mobileCouponFactory->create();
        $dto->setCouponId((int)$coupon->getId())
            ->setCode((string)$coupon->getCode())
            ->setRuleId((int)$coupon->getRuleId())
            ->setOnMobile((int)$coupon->getData('on_mobile'));

        return $dto;
    }
}

