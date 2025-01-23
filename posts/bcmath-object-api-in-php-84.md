---
title: "Handling Decimal Calculations in PHP 8.4: Integrating BCMath Object API with Laravel"
date: 2025-01-22
author: Takeshi Yu
gravatar: 3075c9103f3dddee28d255b45df7bfc4c58459e182f8c600a07930856e97dc39
twitter: '@takeshi_hey'
---

When developing enterprise applications, particularly those handling financial transactions, accounting systems, or inventory management, precise numerical calculations are non-negotiable. A small rounding error could lead to significant discrepancies and debugging nightmares. Let's dive into how PHP 8.4's new BCMath Object API makes handling these calculations both precise and elegant.

---

If you've been working with PHP for a while, you've probably encountered this classic floating-point precision issue:

```php
$a = 0.1;
$b = 0.2;
var_dump($a + $b);  // Outputs: 0.30000000000000004
```

This kind of imprecision is unacceptable in financial calculations. It's not just about aesthetics – these small errors can compound and lead to real-world discrepancies in your applications.

## The Foundation: Database Structure

The first step in handling precise decimal calculations starts at the database level. Using the `DECIMAL` type is crucial:

```php
// In Laravel Migration
Schema::create('items', function (Blueprint $table) {
    $table->id();
    $table->decimal('quantity', 10, 3);  // Total length 10, 3 decimal places
    $table->decimal('price', 10, 3);     // Total length 10, 3 decimal places
    $table->decimal('discount', 10, 3);  // Total length 10, 3 decimal places
    $table->decimal('tax', 10, 3);       // Total length 10, 3 decimal places
    // other columns ...
});
```

The `DECIMAL` type ensures:

* Exact decimal point precision
* Configurable scale and precision
* Suitable for financial calculations

While `DECIMAL` might be slightly slower than `FLOAT`, the trade-off for precision is well worth it in business-critical applications.

## Laravel's Decimal Casting

If you're using [Laravel](https://laravel.com/), you can leverage its [casting system](https://laravel.com/docs/master/eloquent-mutators#attribute-casting) to handle decimal values:

```php
class Item extends Model
{
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'price' => 'decimal:3',
            'discount' => 'decimal:3',
            'tax' => 'decimal:3',
        ];
    }
}
```

However, it's important to understand that Laravel's casting primarily handles:

* Data formatting
* Consistent value representation

## The Hidden Type Conversion Trap

Even with proper database types and Laravel casting, you can still run into precision issues when performing calculations:

```php
// Values from database
$item1 = Item::find(1);  // price: "99.99"
$item2 = Item::find(2);  // price: "149.99"

// Calculation without BCMath
$subtotal = $item1->price + $item2->price;
$tax = $subtotal * 0.05;  // 5% tax

var_dump($tax);  // Outputs: float(12.499000000000002) instead of 12.499
```

This behavior occurs because PHP automatically converts strings to numbers when you perform arithmetic operations:

```php
// String values from database
$price1 = "99.99";
$price2 = "149.99";
echo gettype($price1);  // string

// PHP automatically converts to float during arithmetic
$total = $price1 + $price2;
echo gettype($total);   // double (float)
```

## BCMath Before PHP 8.4: Precise But Verbose

The solution uses [PHP's BCMath](https://www.php.net/manual/en/book.bc.php) extension:

```php
// Database values
$item1 = Item::find(1);  // price: "99.99"
$item2 = Item::find(2);  // price: "149.99"

// Using BCMath functions
$subtotal = bcadd($item1->price, $item2->price, 3);
$tax = bcmul($subtotal, $item2->tax, 3);

var_dump($tax);  // Precisely outputs: string(5) "12.499"
```

However, when calculations become more complex, the code becomes harder to read and maintain:

```php
// Complex order calculation
$subtotal = bcmul($item1->price, $item1->quantity, 3);  // Calculate quantity
$discount = bcmul($subtotal, $item1->discount, 3);      // 10% discount
$afterDiscount = bcsub($subtotal, $discount, 3);        // Apply discount
$tax = bcmul($afterDiscount, $item1->tax, 3);           // Add 5% tax
$total = bcadd($afterDiscount, $tax, 3);                // Final amount
```

## PHP 8.4's BCMath Object API

[PHP 8.4](https://www.php.net/releases/8.4/en.php) introduces a new [object-oriented API for BCMath](https://wiki.php.net/rfc/support_object_type_in_bcmath), making precise calculations both elegant and intuitive. When using `BCMath\Number`, values must be passed as `strings` to maintain precision - any `float` values will be converted to `integers`, leading to precision loss:

```php
$num = new Number(3.5);    // Output: "3"
$num = new Number('4.1');  // Output: "4.1"
```

This is why we properly configure our database columns as `DECIMAL` and ensure proper [PHP PDO](https://www.php.net/manual/en/book.pdo.php) settings or explicit casting - to maintain string representation of decimal values. Here's how we use the new API to perform precise calculations:

```php
use BCMath\Number;

$item1 = Item::find(1);
$price = new Number($item1->price);            // price: "99.99"
$quantity = new Number($item1->quantity);      // quantity: "2"
$discountRate = new Number($item1->discount);  // 10% discount
$taxRate = new Number($item1->tax);            // 5% tax

// Calculations become natural and readable
$subtotal = $price * $quantity;
$discount = $subtotal * $discountRate;
$afterDiscount = $subtotal - $discount;
$tax = $afterDiscount * $taxRate;
$total = $afterDiscount + $tax;

var_dump($total);  // Automatically converts to string
```

Key benefits of the new API:

* Intuitive object-oriented interface
* Support for standard mathematical operators
* Immutable objects ensuring value safety
* Implementation of the Stringable interface

## Elegant Integration with Laravel

We can make this even more elegant using [Laravel's accessor pattern](https://laravel.com/docs/master/eloquent-mutators#accessors-and-mutators):

```php
use BCMath\Number;

class Item extends Model
{
    protected function quantity(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => new Number($value),
        );
    }

    protected function price(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => new Number($value),
        );
    }

    protected function discount(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => new Number($value),
        );
    }

    protected function tax(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => new Number($value),
        );
    }
}
```

Or with a [custom cast](https://laravel.com/docs/master/eloquent-mutators#custom-casts):

```php
use BCMath\Number;

class DecimalCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return new Number($value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value instanceof Number) {
            return (string) $value;
        }
        return (string) new Number($value);
    }
}
```

```php
class Item extends Model
{
    protected function casts(): array
    {
        return [
            'quantity' => DecimalCast::class,
            'price' => DecimalCast::class,
            'discount' => DecimalCast::class,
            'tax' => DecimalCast::class,
        ];
    }
}
```

Then:

```php
$item1 = Item::find(1);

$subtotal = $item1->price * $item1->quantity;
$discount = $subtotal * $item1->discount;
$afterDiscount = $subtotal - $discount;
$tax = $afterDiscount * $item1->tax;
$total = $afterDiscount + $tax;
```

## Conclusion

In my work developing healthcare inventory systems, precise decimal calculations are crucial. Whether it's converting medication units or calculating exact costs, even small rounding errors could have serious implications. [PHP 8.4's BCMath Object API](https://wiki.php.net/rfc/support_object_type_in_bcmath), combined with [Laravel's elegant model layer](https://laravel.com/docs/master/eloquent), has transformed how we handle these critical calculations.

This integration brings multiple benefits:
- The precision we require for medical calculations
- Improved code readability through object-oriented syntax
- Better maintainability with Laravel's elegant abstraction
- Type safety through PHP's static typing

While the traditional BCMath functions served us well for many years, this new approach significantly improves our daily development work.


















