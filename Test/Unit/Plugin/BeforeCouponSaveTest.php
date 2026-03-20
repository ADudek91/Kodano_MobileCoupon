<?php

declare(strict_types=1);

namespace Kodano\MobileCoupon\Test\Unit\Plugin;

use Kodano\MobileCoupon\Plugin\BeforeCouponSave;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\SalesRule\Api\CouponRepositoryInterface;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\SalesRule\Model\Coupon;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Unit tests for BeforeCouponSave plugin
 *
 * Tests the before-save hook that marks coupons as mobile-only when on_mobile flag is set in POST data.
 */
class BeforeCouponSaveTest extends TestCase
{
    private BeforeCouponSave $plugin;
    private RuleRepositoryInterface|MockObject $ruleRepository;
    private LoggerInterface|MockObject $logger;
    private HttpRequest|MockObject $request;
    private CouponRepositoryInterface|MockObject $couponRepository;

    protected function setUp(): void
    {
        $this->ruleRepository = $this->createMock(RuleRepositoryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->request = $this->getMockBuilder(HttpRequest::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPostValue'])
            ->getMock();
        $this->couponRepository = $this->createMock(CouponRepositoryInterface::class);

        $this->plugin = new BeforeCouponSave(
            $this->ruleRepository,
            $this->logger,
            $this->request,
            $this->couponRepository
        );
    }

    /**
     * Test that on_mobile flag is set when present in POST data and differs from current value
     */
    public function testBeforeSaveSetsMobileFlag(): void
    {
        $coupon = $this->createMock(Coupon::class);
        $coupon->expects($this->once())->method('getData')->with('on_mobile')->willReturn(0);
        $coupon->expects($this->once())->method('setData')->with('on_mobile', 1)->willReturnSelf();

        $this->request->expects($this->once())
            ->method('getPostValue')
            ->willReturn(['on_mobile' => 1]);

        $this->couponRepository->expects($this->once())
            ->method('save')
            ->with($coupon);

        $result = $this->plugin->beforeSave($coupon);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test that coupon is not saved if on_mobile flag hasn't changed
     */
    public function testBeforeSaveSkipsSaveWhenFlagUnchanged(): void
    {
        $coupon = $this->createMock(Coupon::class);
        $coupon->expects($this->once())->method('getData')->with('on_mobile')->willReturn(1);
        $coupon->expects($this->never())->method('setData');

        $this->request->expects($this->once())
            ->method('getPostValue')
            ->willReturn(['on_mobile' => 1]);

        $this->couponRepository->expects($this->never())->method('save');

        $result = $this->plugin->beforeSave($coupon);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test that method returns empty array when no POST data
     */
    public function testBeforeSaveReturnsEmptyArrayWhenNoPostData(): void
    {
        $coupon = $this->createMock(Coupon::class);
        $coupon->expects($this->never())->method('setData');

        $this->request->expects($this->once())
            ->method('getPostValue')
            ->willReturn(null);

        $this->couponRepository->expects($this->never())->method('save');

        $result = $this->plugin->beforeSave($coupon);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test that on_mobile flag is removed when set to 0 in POST data
     */
    public function testBeforeSaveRemovesMobileFlag(): void
    {
        $coupon = $this->createMock(Coupon::class);
        $coupon->expects($this->once())->method('getData')->with('on_mobile')->willReturn(1);
        $coupon->expects($this->once())->method('setData')->with('on_mobile', 0)->willReturnSelf();

        $this->request->expects($this->once())
            ->method('getPostValue')
            ->willReturn(['on_mobile' => 0]);

        $this->couponRepository->expects($this->once())
            ->method('save')
            ->with($coupon);

        $result = $this->plugin->beforeSave($coupon);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test that exceptions are caught and logged
     */
    public function testBeforeSaveLogsExceptionWhenSaveFails(): void
    {
        $coupon = $this->createMock(Coupon::class);
        $coupon->expects($this->once())->method('getData')->with('on_mobile')->willReturn(0);
        $coupon->expects($this->once())->method('setData')->with('on_mobile', 1)->willReturnSelf();
        $coupon->expects($this->once())->method('getId')->willReturn(1);
        $coupon->expects($this->once())->method('getRuleId')->willReturn(5);

        $this->request->expects($this->once())
            ->method('getPostValue')
            ->willReturn(['on_mobile' => 1]);

        $exception = new \Magento\Framework\Exception\NoSuchEntityException(__('Test exception'));
        $this->couponRepository->expects($this->once())
            ->method('save')
            ->willThrowException($exception);

        $this->logger->expects($this->once())
            ->method('error')
            ->with(
                'Could not find rule for coupon during save.',
                $this->callback(function ($context) {
                    return isset($context['coupon_id']) &&
                           isset($context['rule_id']) &&
                           isset($context['exception']);
                })
            );

        $result = $this->plugin->beforeSave($coupon);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test that on_mobile flag is ignored if not present in POST data
     */
    public function testBeforeSaveIgnoresMissingOnMobileKey(): void
    {
        $coupon = $this->createMock(Coupon::class);
        $coupon->expects($this->never())->method('setData');

        $this->request->expects($this->once())
            ->method('getPostValue')
            ->willReturn(['some_other_field' => 'value']);

        $this->couponRepository->expects($this->never())->method('save');

        $result = $this->plugin->beforeSave($coupon);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
}

