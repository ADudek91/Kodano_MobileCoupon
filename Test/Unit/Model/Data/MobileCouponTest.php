<?php

declare(strict_types=1);

namespace Kodano\MobileCoupon\Test\Unit\Model\Data;

use Kodano\MobileCoupon\Model\Data\MobileCoupon;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for MobileCoupon DTO
 *
 * Tests getters and setters for coupon data transfer object.
 */
class MobileCouponTest extends TestCase
{
    private MobileCoupon $dto;

    protected function setUp(): void
    {
        $this->dto = new MobileCoupon();
    }

    /**
     * Test setCouponId and getCouponId
     */
    public function testSetAndGetCouponId(): void
    {
        $result = $this->dto->setCouponId(123);

        // Method should return $this for fluent interface
        $this->assertSame($this->dto, $result);
        $this->assertSame(123, $this->dto->getCouponId());
    }

    /**
     * Test getCouponId returns 0 when not set
     */
    public function testGetCouponIdReturnsZeroWhenNotSet(): void
    {
        $this->assertSame(0, $this->dto->getCouponId());
    }

    /**
     * Test setCode and getCode
     */
    public function testSetAndGetCode(): void
    {
        $result = $this->dto->setCode('MOBILE10');

        $this->assertSame($this->dto, $result);
        $this->assertSame('MOBILE10', $this->dto->getCode());
    }

    /**
     * Test getCode returns empty string when not set
     */
    public function testGetCodeReturnsEmptyStringWhenNotSet(): void
    {
        $this->assertSame('', $this->dto->getCode());
    }

    /**
     * Test setRuleId and getRuleId
     */
    public function testSetAndGetRuleId(): void
    {
        $result = $this->dto->setRuleId(5);

        $this->assertSame($this->dto, $result);
        $this->assertSame(5, $this->dto->getRuleId());
    }

    /**
     * Test getRuleId returns 0 when not set
     */
    public function testGetRuleIdReturnsZeroWhenNotSet(): void
    {
        $this->assertSame(0, $this->dto->getRuleId());
    }

    /**
     * Test setOnMobile and getOnMobile
     */
    public function testSetAndGetOnMobile(): void
    {
        $result = $this->dto->setOnMobile(1);

        $this->assertSame($this->dto, $result);
        $this->assertSame(1, $this->dto->getOnMobile());
    }

    /**
     * Test getOnMobile returns 0 when not set
     */
    public function testGetOnMobileReturnsZeroWhenNotSet(): void
    {
        $this->assertSame(0, $this->dto->getOnMobile());
    }

    /**
     * Test fluent interface — chaining multiple setters
     */
    public function testFluentInterfaceChaining(): void
    {
        $this->dto
            ->setCouponId(100)
            ->setCode('TEST100')
            ->setRuleId(10)
            ->setOnMobile(1);

        $this->assertSame(100, $this->dto->getCouponId());
        $this->assertSame('TEST100', $this->dto->getCode());
        $this->assertSame(10, $this->dto->getRuleId());
        $this->assertSame(1, $this->dto->getOnMobile());
    }

    /**
     * Test type casting for getCouponId with string input
     */
    public function testGetCouponIdCastsStringToInt(): void
    {
        $this->dto->setCouponId(999);
        $result = $this->dto->getCouponId();

        $this->assertIsInt($result);
        $this->assertSame(999, $result);
    }

    /**
     * Test type casting for getCode with non-string input
     */
    public function testGetCodeCastsToString(): void
    {
        // DataObject setData accepts any value
        $this->dto->setData('code', 12345);
        $result = $this->dto->getCode();

        $this->assertIsString($result);
        $this->assertSame('12345', $result);
    }

    /**
     * Test type casting for getRuleId with string input
     */
    public function testGetRuleIdCastsStringToInt(): void
    {
        $this->dto->setRuleId(50);
        $result = $this->dto->getRuleId();

        $this->assertIsInt($result);
        $this->assertSame(50, $result);
    }

    /**
     * Test type casting for getOnMobile with string input
     */
    public function testGetOnMobileCastsStringToInt(): void
    {
        $this->dto->setOnMobile(1);
        $result = $this->dto->getOnMobile();

        $this->assertIsInt($result);
        $this->assertSame(1, $result);
    }

    /**
     * Test that all properties can be set and retrieved independently
     */
    public function testMultipleIndependentProperties(): void
    {
        $dto1 = new MobileCoupon();
        $dto1->setCouponId(1)->setCode('CODE1')->setRuleId(10)->setOnMobile(1);

        $dto2 = new MobileCoupon();
        $dto2->setCouponId(2)->setCode('CODE2')->setRuleId(20)->setOnMobile(0);

        // Verify they are independent
        $this->assertSame(1, $dto1->getCouponId());
        $this->assertSame(2, $dto2->getCouponId());
        $this->assertSame('CODE1', $dto1->getCode());
        $this->assertSame('CODE2', $dto2->getCode());
    }
}

