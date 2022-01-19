<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\Bundle\ApiBundle\DataProvider;

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use ApiPlatform\Core\DataProvider\SubresourceDataProviderInterface;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\ShippingMethodRepository;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;
use Sylius\Component\Core\Repository\PaymentRepositoryInterface;
use Sylius\Component\Payment\Resolver\PaymentMethodsResolverInterface;
use Webmozart\Assert\Assert;

/** @experimental */
final class CartPaymentMethodsCollectionDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private OrderRepositoryInterface $orderRepository;

    private PaymentRepositoryInterface $paymentRepository;

    private PaymentMethodRepositoryInterface $paymentMethodRepository;

    private PaymentMethodsResolverInterface $paymentMethodsResolver;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        PaymentRepositoryInterface $paymentRepository,
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        PaymentMethodsResolverInterface $paymentMethodsResolver
    ) {
        $this->orderRepository = $orderRepository;
        $this->paymentRepository = $paymentRepository;
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->paymentMethodsResolver = $paymentMethodsResolver;
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): array
    {
        $parameters = $context['filters'];

        Assert::keyExists($context['filters'], 'tokenValue');
        Assert::keyExists($context['filters'], 'paymentId');

        /** @var OrderInterface|null $order */
        $order = $this->orderRepository->findCartByTokenValue($parameters['tokenValue']);
        Assert::notNull($order);

        /** @var PaymentInterface|null $payment */
        $payment = $this->paymentRepository->find($parameters['payments']);
        Assert::notNull($payment);

        Assert::true($order->hasPayment($payment), 'Payment doesn\'t match for order');

        return $this->paymentMethodsResolver->getSupportedMethods($payment);
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return is_a($resourceClass, PaymentMethodInterface::class, true);
    }
}
