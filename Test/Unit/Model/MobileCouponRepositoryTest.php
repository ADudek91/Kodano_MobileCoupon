<?php

declare(strict_types=1);

namespace Kodano\MobileCoupon\Test\Unit\Model;

use Kodano\MobileCoupon\Api\Data\MobileCouponInterface;
use Kodano\MobileCoupon\Api\Data\MobileCouponInterfaceFactory;
use Kodano\MobileCoupon\Model\MobileCouponRepository;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\CouponFactory;
use Magento\SalesRule\Model\ResourceModel\Coupon as CouponResource;
use Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory as CouponCollectionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Unit tests for MobileCouponRepository
 *
 * Tests CRUD operations for mobile-only coupons.
 */
class MobileCouponRepositoryTest extends TestCase
{
    private MobileCouponRepository $repository;
    private CouponFactory|MockObject $couponFactory;
    private CouponResource|MockObject $couponResource;
    private CouponCollectionFactory|MockObject $collectionFactory;
    private MobileCouponInterfaceFactory|MockObject $mobileCouponFactory;
    private LoggerInterface|MockObject $logger;

    protected function setUp(): void
    {
        $this->couponFactory = $this->createMock(CouponFactory::class);
        $this->couponResource = $this->createMock(CouponResource::class);
        $this->collectionFactory = $this->createMock(CouponCollectionFactory::class);
        $this->mobileCouponFactory = $this->createMock(MobileCouponInterfaceFactory::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->repository = new MobileCouponRepository(
            $this->couponFactory,
            $this->couponResource,
            $this->collectionFactory,
            $this->mobileCouponFactory,
            $this->logger
        );
    }

    /**
     * Test getList returns array of mobile coupons
     */
    public function testGetListReturnsMobileCoupons(): void
    {
        $collection = $this->createMock(\Magento\SalesRule\Model\ResourceModel\Coupon\Collection::class);

        $coupon1 = $this->createMock(Coupon::class);
        $coupon1->method('getId')->willReturn(1);
        $coupon1->method('getCode')->willReturn('MOBILE10');
        $coupon1->method('getRuleId')->willReturn(5);
        $coupon1->method('getData')->with('on_mobile')->willReturn(1);

        $coupon2 = $this->createMock(Coupon::class);
        $coupon2->method('getId')->willReturn(2);
        $coupon2->method('getCode')->willReturn('MOBILE20');
        $coupon2->method('getRuleId')->willReturn(6);
        $coupon2->method('getData')->with('on_mobile')->willReturn(1);

        $collection->expects($this->once())
            ->method('addFieldToFilter')
            ->with('on_mobile', ['eq' => 1])
            ->willReturnSelf();

        // Make collection iterable
        $collection->method('getIterator')
            ->willReturn(new \ArrayIterator([$coupon1, $coupon2]));

        $this->collectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($collection);

        $dto1 = $this->createMock(MobileCouponInterface::class);
        $dto2 = $this->createMock(MobileCouponInterface::class);

        $this->mobileCouponFactory->expects($this->exactly(2))
            ->method('create')
            ->willReturnOnConsecutiveCalls($dto1, $dto2);

        $result = $this->repository->getList();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }

    /**
     * Test getList returns empty array when no mobile coupons exist
     */
    public function testGetListReturnsEmptyArrayWhenNoMobileCoupons(): void
    {
        $collection = $this->createMock(\Magento\SalesRule\Model\ResourceModel\Coupon\Collection::class);

        $collection->expects($this->once())
            ->method('addFieldToFilter')
            ->with('on_mobile', ['eq' => 1])
            ->willReturnSelf();

        $collection->method('getIterator')
            ->willReturn(new \ArrayIterator([]));

        $this->collectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($collection);

        $result = $this->repository->getList();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test save marks coupon as mobile-only
     */
    public function testSaveMarksCouponAsMobileOnly(): void
    {
        $coupon = $this->createMock(Coupon::class);
        $coupon->expects($this->exactly(2))->method('getId')->willReturn(10);
        $coupon->expects($this->once())->method('getCode')->willReturn('MOBILE10');
        $coupon->expects($this->once())->method('getRuleId')->willReturn(5);
        $coupon->expects($this->once())->method('getData')->with('on_mobile')->willReturn(1);
        $coupon->expects($this->once())->method('setData')->with('on_mobile', 1)->willReturnSelf();

        $this->couponFactory->expects($this->once())
            ->method('create')
            ->willReturn($coupon);

        $this->couponResource->expects($this->once())
            ->method('load')
            ->with($coupon, 'MOBILE10', 'code');

        $this->couponResource->expects($this->once())
            ->method('save')
            ->with($coupon);

        $dto = $this->createMock(MobileCouponInterface::class);
        $this->mobileCouponFactory->expects($this->once())
            ->method('create')
            ->willReturn($dto);

        $result = $this->repository->save('MOBILE10');

        $this->assertInstanceOf(MobileCouponInterface::class, $result);
    }

    /**
     * Test save throws NoSuchEntityException when coupon not found
     */
    public function testSaveThrowsNoSuchEntityExceptionWhenCouponNotFound(): void
    {
        $coupon = $this->createMock(Coupon::class);
        $coupon->expects($this->once())->method('getId')->willReturn(null);

        $this->couponFactory->expects($this->once())
            ->method('create')
            ->willReturn($coupon);

        $this->couponResource->expects($this->once())
            ->method('load')
            ->with($coupon, 'NONEXISTENT', 'code');

        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage('Coupon with code "NONEXISTENT" does not exist');

        $this->repository->save('NONEXISTENT');
    }

    /**
     * Test save throws CouldNotSaveException when save fails
     */
    public function testSaveThrowsCouldNotSaveExceptionOnError(): void
    {
        $coupon = $this->createMock(Coupon::class);
        $coupon->expects($this->once())->method('getId')->willReturn(10);
        $coupon->expects($this->once())->method('setData')->with('on_mobile', 1)->willReturnSelf();

        $this->couponFactory->expects($this->once())
            ->method('create')
            ->willReturn($coupon);

        $this->couponResource->expects($this->once())
            ->method('load')
            ->with($coupon, 'MOBILE10', 'code');

        $this->couponResource->expects($this->once())
            ->method('save')
            ->willThrowException(new \Exception('DB error'));

        $this->logger->expects($this->once())->method('error');

        $this->expectException(CouldNotSaveException::class);

        $this->repository->save('MOBILE10');
    }

    /**
     * Test delete removes mobile-only flag from coupon
     */
    public function testDeleteRemovesMobileFlag(): void
    {
        $coupon = $this->createMock(Coupon::class);
        $coupon->expects($this->once())->method('getId')->willReturn(10);
        $coupon->expects($this->once())->method('setData')->with('on_mobile', 0)->willReturnSelf();

        $this->couponFactory->expects($this->once())
            ->method('create')
            ->willReturn($coupon);

        $this->couponResource->expects($this->once())
            ->method('load')
            ->with($coupon, 'MOBILE10', 'code');

        $this->couponResource->expects($this->once())
            ->method('save')
            ->with($coupon);

        $result = $this->repository->delete('MOBILE10');

        $this->assertTrue($result);
    }

    /**
     * Test delete throws NoSuchEntityException when coupon not found
     */
    public function testDeleteThrowsNoSuchEntityExceptionWhenCouponNotFound(): void
    {
        $coupon = $this->createMock(Coupon::class);
        $coupon->expects($this->once())->method('getId')->willReturn(null);

        $this->couponFactory->expects($this->once())
            ->method('create')
            ->willReturn($coupon);

        $this->couponResource->expects($this->once())
            ->method('load')
            ->with($coupon, 'NONEXISTENT', 'code');

        $this->expectException(NoSuchEntityException::class);

        $this->repository->delete('NONEXISTENT');
    }

    /**
     * Test delete throws CouldNotDeleteException when save fails
     */
    public function testDeleteThrowsCouldNotDeleteExceptionOnError(): void
    {
        $coupon = $this->createMock(Coupon::class);
        $coupon->expects($this->once())->method('getId')->willReturn(10);
        $coupon->expects($this->once())->method('setData')->with('on_mobile', 0)->willReturnSelf();

        $this->couponFactory->expects($this->once())
            ->method('create')
            ->willReturn($coupon);

        $this->couponResource->expects($this->once())
            ->method('load')
            ->with($coupon, 'MOBILE10', 'code');

        $this->couponResource->expects($this->once())
            ->method('save')
            ->willThrowException(new \Exception('DB error'));

        $this->logger->expects($this->once())->method('error');

        $this->expectException(CouldNotDeleteException::class);

        $this->repository->delete('MOBILE10');
    }
}

