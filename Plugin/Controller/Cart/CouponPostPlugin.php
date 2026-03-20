<?php

declare(strict_types=1);

namespace Kodano\MobileCoupon\Plugin\Controller\Cart;

use Kodano\MobileCoupon\Model\MobileRequestValidator;
use Magento\Checkout\Controller\Cart\CouponPost;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\SalesRule\Model\CouponFactory;
use Psr\Log\LoggerInterface;

/**
 * Plugin for CouponPost controller to block mobile-only coupons on web frontend.
 */
class CouponPostPlugin
{
    public function __construct(
        private readonly MobileRequestValidator $mobileRequestValidator,
        private readonly CouponFactory $couponFactory,
        private readonly ManagerInterface $messageManager,
        private readonly RequestInterface $request,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Before execute: check if the coupon being applied is mobile-only.
     * If it is and the request is not from mobile app, block the action.
     *
     * @param CouponPost $subject
     * @return void
     * @throws LocalizedException
     */
    public function beforeExecute(CouponPost $subject): void
    {
        // Only block when applying (not removing)
        if ((int)$this->request->getParam('remove') === 1) {
            return;
        }

        $couponCode = trim((string)$this->request->getParam('coupon_code', ''));
        if ($couponCode === '') {
            return;
        }

        // Skip validation if the request is from mobile app (has the header)
        if ($this->mobileRequestValidator->isMobileCouponRequest()) {
            return;
        }

        // Load coupon by code to check on_mobile flag
        $coupon = $this->couponFactory->create();
        $coupon->loadByCode($couponCode);

        if (!$coupon->getId()) {
            // Coupon does not exist — let the original controller handle it
            return;
        }

        $isMobileOnly = (int)$coupon->getData('on_mobile') === 1;
        if (!$isMobileOnly) {
            return;
        }

        $this->logger->warning(
            'Blocked non-mobile apply attempt for mobile-only coupon on web frontend.',
            [
                'coupon_code' => $couponCode,
                'channel' => 'web'
            ]
        );

        // Override the coupon_code param to empty string so the controller
        // treats it as a removal / invalid code — no side effects on the quote.
        $this->request->setParam('coupon_code', '');
        $this->request->setParam('remove', 1);

        $this->messageManager->addErrorMessage(
            __('This coupon can be applied only from the mobile application.')
        );
    }
}

