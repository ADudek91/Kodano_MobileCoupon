<?php

declare(strict_types=1);

namespace Kodano\MobileCoupon\Model;

use Magento\Framework\App\RequestInterface;

class MobileRequestValidator
{
    private const HEADER_NAME = 'X-Mobile-Coupon';

    public function __construct(
        private readonly RequestInterface $request
    ) {
    }

    public function isMobileCouponRequest(): bool
    {
        return $this->request->getHeader(self::HEADER_NAME) !== false;
    }
}
