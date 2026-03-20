<?php

declare(strict_types=1);

namespace Kodano\MobileCoupon\Plugin;

use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\SalesRule\Model\Coupon;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\SalesRule\Api\CouponRepositoryInterface;

class BeforeCouponSave
{
    /**
     * @param RuleRepositoryInterface $ruleRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly RuleRepositoryInterface $ruleRepository,
        private readonly LoggerInterface $logger,
        private readonly RequestInterface $request,
        private  CouponRepositoryInterface $couponRepository,
    ) {
    }

    /**
     * @param Coupon $coupon
     * @return array
     */
    public function beforeSave(Coupon $coupon): array
    {
        $data = $this->request->getPostValue();

        try {
            if ($data) {
                if (isset($data['on_mobile']) && $data['on_mobile'] != $coupon->getData('on_mobile')) {
                    $coupon->setData('on_mobile', $data['on_mobile']);
                    $this->couponRepository->save($coupon);
                }
            }
        } catch (NoSuchEntityException $e) {
            $this->logger->error(
                'Could not find rule for coupon during save.',
                [
                    'coupon_id' => $coupon->getId(),
                    'rule_id' => $coupon->getRuleId(),
                    'exception' => $e->getMessage()
                ]
            );
        }

        return [];
    }
}
