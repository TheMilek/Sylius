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

namespace spec\Sylius\Bundle\ApiBundle\Doctrine\QueryItemExtension;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sylius\Bundle\ApiBundle\Context\UserContextInterface;
use Sylius\Bundle\ApiBundle\Serializer\ContextKeys;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\User\Model\UserInterface;
use Symfony\Component\HttpFoundation\Request;

final class OrderVisitorItemExtensionSpec extends ObjectBehavior
{
    function let(UserContextInterface $userContext): void
    {
        $this->beConstructedWith($userContext, ['shop_select_payment_method']);
    }

    function it_filters_carts_for_visitors_to_not_authorized_for_methods_other_than_get(
        UserContextInterface $userContext,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        Expr $expr,
    ): void {
        $queryNameGenerator->generateParameterName('state')->shouldBeCalled()->willReturn('state');
        $queryBuilder->getRootAliases()->willReturn('o');

        $userContext->getUser()->willReturn(null);

        $queryBuilder
            ->leftJoin('o.customer', 'customer')
            ->shouldBeCalled()
            ->willReturn($queryBuilder)
        ;

        $queryBuilder
            ->leftJoin('customer.user', 'user')
            ->shouldBeCalled()
            ->willReturn($queryBuilder)
        ;

        $queryBuilder
            ->expr()
            ->shouldBeCalled()
            ->willReturn($expr)
        ;

        $expr
            ->isNull('user')
            ->shouldBeCalled()
            ->willReturn('user IS NULL')
        ;

        $expr
            ->eq('o.createdByGuest', ':createdByGuest')
            ->shouldBeCalled()
            ->willReturn('o.createdByGuest = :createdByGuest')
        ;

        $expr
            ->andX(
                'user IS NULL',
                'o.createdByGuest = :createdByGuest'
            )
            ->shouldBeCalled()
            ->willReturn('user IS NULL AND o.createdByGuest = :createdByGuest')
        ;

        $queryBuilder
            ->andWhere('user IS NULL AND o.createdByGuest = :createdByGuest')
            ->shouldBeCalled()
            ->willReturn($queryBuilder)
        ;

        $queryBuilder
            ->setParameter('createdByGuest', true)
            ->shouldBeCalled()
            ->willReturn($queryBuilder)
        ;

        $queryBuilder
            ->andWhere('o.state = :state')
            ->shouldBeCalled()
            ->willReturn($queryBuilder)
        ;

        $queryBuilder
            ->setParameter('state', OrderInterface::STATE_CART)
            ->shouldBeCalled()
            ->willReturn($queryBuilder)
        ;

        $this->applyToItem(
            $queryBuilder,
            $queryNameGenerator,
            OrderInterface::class,
            ['tokenValue' => 'xaza-tt_fee'],
            Request::METHOD_DELETE,
            [ContextKeys::HTTP_REQUEST_METHOD_TYPE => Request::METHOD_POST],
        );

        $this->applyToItem(
            $queryBuilder,
            $queryNameGenerator,
            OrderInterface::class,
            ['tokenValue' => 'xaza-tt_fee'],
            Request::METHOD_DELETE,
            [ContextKeys::HTTP_REQUEST_METHOD_TYPE => Request::METHOD_PATCH],
        );

        $this->applyToItem(
            $queryBuilder,
            $queryNameGenerator,
            OrderInterface::class,
            ['tokenValue' => 'xaza-tt_fee'],
            Request::METHOD_DELETE,
            [ContextKeys::HTTP_REQUEST_METHOD_TYPE => Request::METHOD_PUT],
        );

        $this->applyToItem(
            $queryBuilder,
            $queryNameGenerator,
            OrderInterface::class,
            ['tokenValue' => 'xaza-tt_fee'],
            Request::METHOD_DELETE,
            [ContextKeys::HTTP_REQUEST_METHOD_TYPE => Request::METHOD_DELETE],
        );
    }

    function it_filters_orders_for_visitors_to_not_authorized_orders_for_get_operations_and_payment_selection(
        UserContextInterface $userContext,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        Expr $expr,
    ): void {
        $queryBuilder->getRootAliases()->willReturn('o');

        $userContext->getUser()->willReturn(null);

        $queryBuilder
            ->leftJoin('o.customer', 'customer')
            ->shouldBeCalled()
            ->willReturn($queryBuilder)
        ;

        $queryBuilder
            ->leftJoin('customer.user', 'user')
            ->shouldBeCalled()
            ->willReturn($queryBuilder)
        ;

        $queryBuilder
            ->expr()
            ->shouldBeCalled()
            ->willReturn($expr)
        ;

        $expr
            ->isNull('user')
            ->shouldBeCalled()
            ->willReturn('user IS NULL')
        ;

        $expr
            ->eq('o.createdByGuest', ':createdByGuest')
            ->shouldBeCalled()
            ->willReturn('o.createdByGuest = :createdByGuest')
        ;

        $expr
            ->andX(
                'user IS NULL',
                'o.createdByGuest = :createdByGuest'
            )
            ->shouldBeCalled()
            ->willReturn('user IS NULL AND o.createdByGuest = :createdByGuest')
        ;

        $queryBuilder
            ->andWhere('user IS NULL AND o.createdByGuest = :createdByGuest')
            ->shouldBeCalled()
            ->willReturn($queryBuilder)
        ;

        $queryBuilder
            ->setParameter('createdByGuest', true)
            ->shouldBeCalled()
            ->willReturn($queryBuilder)
        ;

        $this->applyToItem(
            $queryBuilder,
            $queryNameGenerator,
            OrderInterface::class,
            ['tokenValue' => 'xaza-tt_fee'],
            Request::METHOD_GET,
            [ContextKeys::HTTP_REQUEST_METHOD_TYPE => Request::METHOD_GET],
        );

        $this->applyToItem(
            $queryBuilder,
            $queryNameGenerator,
            OrderInterface::class,
            ['tokenValue' => 'xaza-tt_fee'],
            'shop_select_payment_method',
            [ContextKeys::HTTP_REQUEST_METHOD_TYPE => Request::METHOD_PATCH],
        );
    }

    function it_does_nothing_if_any_user_is_logged_in(
        UserContextInterface $userContext,
        QueryBuilder $queryBuilder,
        UserInterface $user,
        QueryNameGeneratorInterface $queryNameGenerator,
    ): void {
        $queryBuilder->getRootAliases()->willReturn(['o']);

        $userContext->getUser()->willReturn($user);

        $queryBuilder->leftJoin(Argument::any())->shouldNotBeCalled();
        $queryBuilder->expr()->shouldNotBeCalled();
        $queryBuilder->setParameter(Argument::any())->shouldNotBeCalled();
        $queryBuilder->andWhere(Argument::any())->shouldNotBeCalled();

        $this->applyToItem(
            $queryBuilder,
            $queryNameGenerator,
            OrderInterface::class,
            ['tokenValue' => 'xaza-tt_fee'],
            Request::METHOD_PUT,
            [ContextKeys::HTTP_REQUEST_METHOD_TYPE => Request::METHOD_PUT],
        );
    }

    function it_does_nothing_if_object_passed_is_different_than_order(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
    ): void {
        $queryBuilder->leftJoin(Argument::any())->shouldNotBeCalled();
        $queryBuilder->expr()->shouldNotBeCalled();
        $queryBuilder->setParameter(Argument::any())->shouldNotBeCalled();
        $queryBuilder->andWhere(Argument::any())->shouldNotBeCalled();

        $this->applyToItem(
            $queryBuilder,
            $queryNameGenerator,
            \stdClass::class,
            ['tokenValue' => 'xaza-tt_fee'],
            Request::METHOD_PUT,
            [ContextKeys::HTTP_REQUEST_METHOD_TYPE => Request::METHOD_PUT],
        );
    }
}
