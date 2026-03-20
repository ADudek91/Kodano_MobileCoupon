<?php

declare(strict_types=1);

namespace Kodano\MobileCoupon\Test\Unit\Plugin\Controller\Cart;

use Kodano\MobileCoupon\Model\MobileRequestValidator;
use Kodano\MobileCoupon\Plugin\Controller\Cart\CouponPostPlugin;
use Magento\Checkout\Controller\Cart\CouponPost;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\CouponFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Unit tests for CouponPostPlugin
 *
 * Tests the blocking of mobile-only coupons when applied from web frontend.
 */
class CouponPostPluginTest extends TestCase
{
    private CouponPostPlugin $plugin;
    private MobileRequestValidator|MockObject $mobileValidator;
    private CouponFactory|MockObject $couponFactory;
    private ManagerInterface|MockObject $messageManager;
    private RequestInterface|MockObject $request;
    private LoggerInterface|MockObject $logger;
    private CouponPost|MockObject $controller;

    protected function setUp(): void
    {
        $this->mobileValidator = $this->createMock(MobileRequestValidator::class);
        $this->couponFactory = $this->createMock(CouponFactory::class);
        $this->messageManager = $this->createMock(ManagerInterface::class);
        $this->request = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getParam'])
            ->addMethods(['setParam'])
            ->getMockForAbstractClass();
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->controller = $this->createMock(CouponPost::class);

        $this->plugin = new CouponPostPlugin(
            $this->mobileValidator,
            $this->couponFactory,
            $this->messageManager,
            $this->request,
            $this->logger
        );
    }

    /**
     * Test that mobile-only coupon is blocked when applied from web frontend
     */
    public function testBlocksMobileOnlyCouponFromWebFrontend(): void
    {
        $coupon = $this->createMock(Coupon::class);
        $coupon->expects($this->once())->method('getId')->willReturn(10);
        $coupon->expects($this->once())->method('getData')->with('on_mobile')->willReturn(1);

        $this->request->expects($this->exactly(2))
            ->method('getParam')
            ->willReturnCallback(function ($key, $default = null) {
                return match ($key) {
                    'remove' => null,
                    'coupon_code' => 'MOBILE10',
                    default => $default,
                };
            });

        $this->mobileValidator->expects($this->once())
            ->method('isMobileCouponRequest')
            ->willReturn(false);

        $this->couponFactory->expects($this->once())
            ->method('create')
            ->willReturn($coupon);

        $this->request->expects($this->exactly(2))
            ->method('setParam')
            ->willReturnCallback(function ($key, $value) {
                $this->assertContains($key, ['coupon_code', 'remove']);
                if ($key === 'coupon_code') {
                    $this->assertSame('', $value);
                }
                if ($key === 'remove') {
                    $this->assertSame(1, $value);
                }
            });

        $this->messageManager->expects($this->once())
            ->method('addErrorMessage')
            ->with(
                $this->callback(function ($message) {
                    return str_contains((string)$message, 'mobile application');
                })
            );

        $this->logger->expects($this->once())
            ->method('warning');

        $this->plugin->beforeExecute($this->controller);
    }

    /**
     * Test that mobile-only coupon is allowed when request has mobile header
     */
    public function testAllowsMobileOnlyCouponWhenMobileHeaderPresent(): void
    {
        $this->request->expects($this->exactly(2))
            ->method('getParam')
            ->willReturnMap([
                ['remove', null, null],
                ['coupon_code', '', 'MOBILE10'],
            ]);

        $this->mobileValidator->expects($this->once())
            ->method('isMobileCouponRequest')
            ->willReturn(true);

        // Coupon should not be loaded when mobile request is detected
        $this->couponFactory->expects($this->never())->method('create');
        $this->messageManager->expects($this->never())->method('addErrorMessage');

        $this->plugin->beforeExecute($this->controller);
    }

    /**
     * Test that regular coupons are allowed from web frontend
     */
    public function testAllowsRegularCouponsFromWebFrontend(): void
    {
        $coupon = $this->createMock(Coupon::class);
        $coupon->expects($this->once())->method('getId')->willReturn(10);
        $coupon->expects($this->once())->method('getData')->with('on_mobile')->willReturn(0);

        $this->request->expects($this->exactly(2))
            ->method('getParam')
            ->willReturnCallback(function ($key, $default = null) {
                return match ($key) {
                    'remove' => null,
                    'coupon_code' => 'REGULAR10',
                    default => $default,
                };
            });

        $this->mobileValidator->expects($this->once())
            ->method('isMobileCouponRequest')
            ->willReturn(false);

        $this->couponFactory->expects($this->once())
            ->method('create')
            ->willReturn($coupon);

        // Regular coupon should be allowed — no error message
        $this->messageManager->expects($this->never())->method('addErrorMessage');
        $this->request->expects($this->never())->method('setParam');

        $this->plugin->beforeExecute($this->controller);
    }

    /**
     * Test that removal of mobile-only coupon is always allowed
     */
    public function testAllowsRemovalOfMobileOnlyCoupon(): void
    {
        $this->request->expects($this->once())
            ->method('getParam')
            ->with('remove', null)
            ->willReturn(1);

        // Should return early without loading coupon
        $this->couponFactory->expects($this->never())->method('create');
        $this->messageManager->expects($this->never())->method('addErrorMessage');

        $this->plugin->beforeExecute($this->controller);
    }

    /**
     * Test that empty coupon code is ignored
     */
    public function testIgnoresEmptyCouponCode(): void
    {
        $this->request->expects($this->exactly(2))
            ->method('getParam')
            ->willReturnMap([
                ['remove', null, null],
                ['coupon_code', '', ''],
            ]);

        // Should return early without loading coupon
        $this->couponFactory->expects($this->never())->method('create');
        $this->messageManager->expects($this->never())->method('addErrorMessage');

        $this->plugin->beforeExecute($this->controller);
    }

    /**
     * Test that non-existent coupon is handled gracefully
     */
    public function testHandlesNonExistentCoupon(): void
    {
        $coupon = $this->createMock(Coupon::class);
        $coupon->expects($this->once())->method('getId')->willReturn(null);

        $this->request->expects($this->exactly(2))
            ->method('getParam')
            ->willReturnCallback(function ($key, $default = null) {
                return match ($key) {
                    'remove' => null,
                    'coupon_code' => 'NONEXISTENT',
                    default => $default,
                };
            });

        $this->mobileValidator->expects($this->once())
            ->method('isMobileCouponRequest')
            ->willReturn(false);

        $this->couponFactory->expects($this->once())
            ->method('create')
            ->willReturn($coupon);

        // Non-existent coupon should be handled by original controller
        $this->messageManager->expects($this->never())->method('addErrorMessage');

        $this->plugin->beforeExecute($this->controller);
    }

    /**
     * Test that whitespace in coupon code is trimmed
     */
    public function testTrimsWhitespaceFromCouponCode(): void
    {
        $coupon = $this->createMock(Coupon::class);
        $coupon->expects($this->once())->method('getId')->willReturn(10);
        $coupon->expects($this->once())->method('getData')->with('on_mobile')->willReturn(1);

        $this->request->expects($this->exactly(2))
            ->method('getParam')
            ->willReturnCallback(function ($key, $default = null) {
                return match ($key) {
                    'remove' => null,
                    'coupon_code' => '  MOBILE10  ',
                    default => $default,
                };
            });

        $this->mobileValidator->expects($this->once())
            ->method('isMobileCouponRequest')
            ->willReturn(false);

        $this->couponFactory->expects($this->once())
            ->method('create')
            ->willReturn($coupon);

        $this->request->expects($this->exactly(2))
            ->method('setParam');

        $this->messageManager->expects($this->once())
            ->method('addErrorMessage');

        $this->plugin->beforeExecute($this->controller);
    }
}

