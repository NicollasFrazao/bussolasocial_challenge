<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Depends;

require_once __DIR__.'/../app/Services/CartService.php';

final class CartServiceTest extends TestCase
{
    private $items = [
        [
            'name' => 'Produto 01',
            'unit_price' => 20.6,
            'quantity' => 3,
        ],
        [
            'name' => 'Produto 02',
            'unit_price' => 30.3,
            'quantity' => 1,
        ],
        [
            'name' => 'Produto 03',
            'unit_price' => 50.7,
            'quantity' => 2,
        ],
    ];

    private $payment_method = 'credit';
    private $installment_quantity = 12;

    public function testNewCartService(): CartService
    {
        $items = $this->items;
        $payment_method = $this->payment_method;
        $installment_quantity = $this->installment_quantity;

        $cart = new CartService($items, $payment_method, $installment_quantity);
        $this->assertTrue(isset($cart));

        return $cart;
    }

    #[Depends('testNewCartService')]
    public function testGetItemsWithAllAttributes(CartService $cart): void
    {
        $items = $cart->getItems();

        $this->assertArrayHasKey('name', $items[0]);
        $this->assertArrayHasKey('unit_price', $items[0]);
        $this->assertArrayHasKey('quantity', $items[0]);
    }

    #[Depends('testNewCartService')]
    public function testSetItems(CartService $cart): void
    {
        $items = $this->items;

        $this->assertTrue($cart->setItems($items));
    }

    #[Depends('testNewCartService')]
    public function testSetItemsWithoutNameAttribute(CartService $cart): void
    {
        $items = $this->items;
        unset($items[0]['name']);

        $this->expectException(\Exception::class);
        $cart->setItems($items);
    }

    #[Depends('testNewCartService')]
    public function testSetItemsWithoutUnitPriceAttribute(CartService $cart): void
    {
        $items = $this->items;
        unset($items[0]['unit_price']);
        
        $this->expectException(\Exception::class);
        $cart->setItems($items);
    }

    #[Depends('testNewCartService')]
    public function testSetItemsWithUnitPriceAttributeLessThanZero(CartService $cart): void
    {
        $items = $this->items;
        $items[0]['unit_price'] = -1;

        $this->expectException(\Exception::class);
        $cart->setItems($items);
    }

    #[Depends('testNewCartService')]
    public function testSetItemsWithoutQuantityAttribute(CartService $cart): void
    {
        $items = $this->items;
        unset($items[0]['quantity']);

        $this->expectException(\Exception::class);
        $cart->setItems($items);
    }

    #[Depends('testNewCartService')]
    public function testSetItemsWithQuantityAttributeLessThanZero(CartService $cart): void
    {
        $items = $this->items;
        $items[0]['quantity'] = -1;

        $this->expectException(\Exception::class);
        $cart->setItems($items);
    }

    #[Depends('testNewCartService')]
    public function testGetAvailablePaymentMethods(CartService $cart): void
    {
        $this->assertIsArray($cart->getAvailablePaymentMethods());
    }

    #[Depends('testNewCartService')]
    public function testGetPaymentMethodAvailable(CartService $cart): void
    {
        $this->assertTrue(in_array($cart->getPaymentMethod(), $cart->getAvailablePaymentMethods()));
    }

    #[Depends('testNewCartService')]
    public function testSetPaymentMethod(CartService $cart): void
    {
        $payment_method = $this->payment_method;

        $this->assertTrue($cart->setPaymentMethod($payment_method));
    }

    #[Depends('testNewCartService')]
    public function testSetPaymentMethodInvalid(CartService $cart): void
    {
        $payment_method = $this->payment_method;
        $payment_method .= 'teste';

        $this->expectException(\Exception::class);
        $cart->setPaymentMethod($payment_method);
    }

    #[Depends('testNewCartService')]
    public function testGetInstallmentQuantity(CartService $cart): void
    {
        $this->assertIsInt($cart->getInstallmentQuantity());
    }

    #[Depends('testNewCartService')]
    public function testSetInstallmentQuantity(CartService $cart): void
    {
        $installment_quantity = $this->installment_quantity;

        $this->assertTrue($cart->setInstallmentQuantity($installment_quantity));
    }

    #[Depends('testNewCartService')]
    public function testSetInstallmentQuantityInvalidWithPaymentMethodPix(CartService $cart): void
    {
        $payment_method = $this->payment_method;
        $payment_method = 'pix';

        $cart->setPaymentMethod($payment_method);

        $installment_quantity = $this->installment_quantity;
        $installment_quantity = 5;

        $this->expectException(\Exception::class);
        $cart->setInstallmentQuantity($installment_quantity);
    }

    #[Depends('testNewCartService')]
    public function testSetInstallmentQuantityInvalidWithPaymentMethodCredit(CartService $cart): void
    {
        $payment_method = $this->payment_method;
        $payment_method = 'credit';

        $cart->setPaymentMethod($payment_method);

        $installment_quantity = $this->installment_quantity;
        $installment_quantity = -125;

        $this->expectException(\Exception::class);
        $cart->setInstallmentQuantity($installment_quantity);
    }

    #[Depends('testNewCartService')]
    public function testGetFinalValue(CartService $cart): void
    {
        $this->assertIsFloat($cart->getFinalValue());
    }
}