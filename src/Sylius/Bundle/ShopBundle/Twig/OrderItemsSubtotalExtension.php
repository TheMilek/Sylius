<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\Bundle\ShopBundle\Twig;

use Sylius\Bundle\ShopBundle\Calculator\OrderItemsSubtotalCalculator;
use Sylius\Bundle\ShopBundle\Calculator\OrderItemsSubtotalCalculatorInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class OrderItemsSubtotalExtension extends AbstractExtension
{
    /** @var OrderItemsSubtotalCalculatorInterface */
    private $calculator;

    public function __construct(?OrderItemsSubtotalCalculatorInterface $calculator = null)
    {
        if (null === $calculator) {
            $calculator = new OrderItemsSubtotalCalculator();

            trigger_deprecation(
                'sylius/shop-bundle',
                '1.6',
                'Not passing a $calculator is deprecated. Argument will no longer be optional from Sylius 2.0.',
            );
        }

        $this->calculator = $calculator;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('sylius_order_items_subtotal', [$this, 'getSubtotal']),
        ];
    }

    public function getSubtotal(OrderInterface $order): int
    {
        return $this->calculator->getSubtotal($order);
    }
}
