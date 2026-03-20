<?php

declare(strict_types=1);

namespace Kodano\MobileCoupon\Test\Unit\Model;

use Kodano\MobileCoupon\Model\MobileRequestValidator;
use Magento\Framework\App\Request\Http as HttpRequest;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for MobileRequestValidator
 *
 * Tests the detection of mobile coupon requests via HTTP header.
 */
class MobileRequestValidatorTest extends TestCase
{
    private MobileRequestValidator $validator;
    private HttpRequest|MockObject $request;

    protected function setUp(): void
    {
        $this->request = $this->createMock(HttpRequest::class);
        $this->validator = new MobileRequestValidator($this->request);
    }

    /**
     * Test that isMobileCouponRequest returns true when header is present
     */
    public function testIsMobileCouponRequestReturnsTrueWhenHeaderExists(): void
    {
        $this->request->expects($this->once())
            ->method('getHeader')
            ->with('X-Mobile-Coupon')
            ->willReturn('1');

        $result = $this->validator->isMobileCouponRequest();

        $this->assertTrue($result);
    }

    /**
     * Test that isMobileCouponRequest returns true for any non-false header value
     */
    public function testIsMobileCouponRequestReturnsTrueForAnyHeaderValue(): void
    {
        $this->request->expects($this->once())
            ->method('getHeader')
            ->with('X-Mobile-Coupon')
            ->willReturn('any-value');

        $result = $this->validator->isMobileCouponRequest();

        $this->assertTrue($result);
    }

    /**
     * Test that isMobileCouponRequest returns false when header is missing (false)
     */
    public function testIsMobileCouponRequestReturnsFalseWhenHeaderMissing(): void
    {
        $this->request->expects($this->once())
            ->method('getHeader')
            ->with('X-Mobile-Coupon')
            ->willReturn(false);

        $result = $this->validator->isMobileCouponRequest();

        $this->assertFalse($result);
    }

    /**
     * Test that isMobileCouponRequest returns true when header is an empty string
     */
    public function testIsMobileCouponRequestReturnsTrueForEmptyHeaderValue(): void
    {
        $this->request->expects($this->once())
            ->method('getHeader')
            ->with('X-Mobile-Coupon')
            ->willReturn('');

        $result = $this->validator->isMobileCouponRequest();

        // An empty header is not the same as a missing header (false), so it should be considered present.
        $this->assertTrue($result);
    }

    /**
     * Test that isMobileCouponRequest returns true when header is null
     */
    public function testIsMobileCouponRequestReturnsTrueForNullHeaderValue(): void
    {
        $this->request->expects($this->once())
            ->method('getHeader')
            ->with('X-Mobile-Coupon')
            ->willReturn(null);

        $result = $this->validator->isMobileCouponRequest();

        // A null value is not strictly `false`, so the request validator considers the header present.
        $this->assertTrue($result);
    }
}

