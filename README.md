# Kodano_MobileCoupon

Magento 2 module for mobile-only coupons management.

## Features

- Mark coupons as "mobile-only" (can be applied only from mobile app)
- Block mobile-only coupons from being applied on web frontend
- Mobile request validation via custom HTTP header `X-Mobile-Coupon-Request`
- REST API for mobile coupon management
- Admin panel UI for managing mobile-only flag
- Full unit test coverage (48 tests, 175 assertions)

## Requirements

- Magento 2.4.8
- PHP 8.3+

## Languages

- English (en_US)
- Polish (pl_PL)

## Installation

1. Copy module to `app/code/Kodano/MobileCoupon`
2. Enable module:
```bash
bin/magento module:enable Kodano_MobileCoupon
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento cache:flush
```

## Database Schema

Module adds `on_mobile` column to `salesrule_coupon` table:
- Type: `smallint`
- Default: `0`
- Nullable: `true`
- Comment: "Coupon is mobile-only"

## REST API Endpoints

### GET /V1/mobile-coupons
Returns list of all mobile-only coupons.

**Response:**
```json
[
  {
    "coupon_id": 1,
    "code": "MOBILE10",
    "rule_id": 5,
    "on_mobile": 1
  }
]
```

### POST /V1/mobile-coupons/:couponCode
Marks coupon as mobile-only.

**Example:**
```bash
curl -X POST https://example.com/rest/V1/mobile-coupons/MOBILE10
```

### DELETE /V1/mobile-coupons/:couponCode
Removes mobile-only flag from coupon.

**Example:**
```bash
curl -X DELETE https://example.com/rest/V1/mobile-coupons/MOBILE10
```

## Mobile App Integration

When applying coupon from mobile app, include custom header:

```
X-Mobile-Coupon-Request: 1
```

Without this header, mobile-only coupons will be rejected on web frontend.

## Admin Panel

Navigate to **Marketing > Cart Price Rules > Edit Rule**

New field: **Available on Mobile Only**
- When enabled, coupon can only be applied from mobile app requests
- Coupon remains valid on web after being applied from mobile

## Testing

Run unit tests:
```bash
vendor/bin/phpunit app/code/Kodano/MobileCoupon/Test/Unit/
```

**Test Coverage:**
- 48 tests
- 175 assertions
- 100% coverage of business logic

## Architecture

### Plugins
- `BeforeCouponSave` - Saves mobile flag when rule is saved in admin
- `CouponPostPlugin` - Blocks mobile-only coupons on web frontend
- `RuleDataProviderPlugin` - Adds mobile flag to admin form data

### Models
- `MobileCouponRepository` - CRUD operations for mobile coupons
- `MobileRequestValidator` - Validates mobile app requests via header

### API
- `MobileCouponRepositoryInterface` - Service contract for REST API
- `MobileCouponInterface` - DTO for mobile coupon data

## Standards

- PSR-12 code style
- PHP 8.3 strict types
- Service Contract Pattern
- Dependency Injection
- SOLID principles
- Unit tests with PHPUnit 10

## License

Proprietary

## Author

Kodano

