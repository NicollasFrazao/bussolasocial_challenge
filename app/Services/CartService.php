<?php

/**
 * 
 *  Classe para genrenciamento de um carrinho de compras básico.
 * 
 *  Observação:
 *      *   Segundo o objetivo, descrição e especificações da implementação, os dados de cartão não contribuem com nada ao resultado, portanto, não vai ser implementada a persistência desses dados.
 * 
 */
class CartService
{
    private array $items;

    private array $available_payment_methods = [
        'pix', 
        'credit',
    ];

    private string $payment_method;
    private int $installment_quantity;

    public function __construct(array $items, string $payment_method, int $installment_quantity = 1)
    {
        $this->setItems($items);
        $this->setPaymentMethod($payment_method);
        $this->setInstallmentQuantity($installment_quantity);
    }

    public function getItems() : array
    {
        return $this->items;
    }
    public function setItems(array $items) : bool
    {
        $new_items = [];

        foreach ($items as $item_index => $item)
        {
            if (!isset($item['name'])) throw new \Exception('Item #'.$item_index.' => O nome do item é obrigatório!');
            else if (!isset($item['unit_price'])) throw new \Exception('Item #'.$item_index.' => O preço unitário do item é obrigatório!');
            else if ($item['unit_price'] < 0) throw new \Exception('Item #'.$item_index.' => O preço unitário do item não pode ser um valor negativo!');
            else if (!isset($item['quantity'])) throw new \Exception('Item #'.$item_index.' => A quantidade do item é obrigatória!');
            else if ($item['quantity'] < 0) throw new \Exception('Item #'.$item_index.' => A quantidade do item não pode ser um valor negativo!');
            else 
            {
                $new_items[] = [
                    'name' => $item['name'],
                    'unit_price' => abs($item['unit_price']),
                    'quantity' => abs($item['quantity']),
                ];
            }
        }

        $this->items = $new_items;
        return true;
    }

    public function getAvailablePaymentMethods() : array
    {
        return $this->available_payment_methods;
    }

    public function getPaymentMethod() : string
    {
        return $this->payment_method;
    }
    public function setPaymentMethod(string $payment_method) : bool
    {
        $available_payment_methods = $this->getAvailablePaymentMethods();

        if (!in_array($payment_method, $available_payment_methods)) throw new \Exception('O método de pagamento informado não é válido! Métodos de pagamento disponíveis: '.implode(', ', $available_payment_methods));
        else $this->payment_method = $payment_method;

        return true;
    }

    public function getInstallmentQuantity() : int
    {
        return $this->installment_quantity;
    }
    public function setInstallmentQuantity(int $installment_quantity) : bool
    {
        switch ($this->getPaymentMethod())
        {
            case 'pix': if (!($installment_quantity === 1)) throw new \Exception('A quantidade de parcelas está inválida! Para o método de pagamento pix a quantidade de parcelas deve ser 1.');
            break;

            case 'credit': if (!(2 <= $installment_quantity && $installment_quantity <= 12)) throw new \Exception('A quantidade de parcelas está inválida! Para o método de pagamento pix a quantidade de parcelas deve ser entre 1 e 12.');
            break;
        }

        $this->installment_quantity = $installment_quantity;
        return true;
    }

    public function getFinalValue() : float
    {
        $final_value = 0;
        foreach ($this->getItems() as $item) $final_value += ($item['unit_price']*$item['quantity']);

        $installment_quantity = $this->getInstallmentQuantity();
        if ($installment_quantity === 1) 
        {
            $discount_percentage = 10;
            $final_value = $final_value*(1 - ($discount_percentage/100));
        }
        else 
        {
            $fee_percentage = 1;
            $final_value = $final_value*pow(1 + ($fee_percentage/100), $installment_quantity);
        }

        return $final_value;
    }
}