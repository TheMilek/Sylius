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

namespace Sylius\Bundle\AttributeBundle\Doctrine\ORM;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Attribute\Model\AttributeTranslationInterface;
use Sylius\Component\Attribute\Repository\AttributeTranslationRepositoryInterface;

/**
 * @implements AttributeTranslationRepositoryInterface<AttributeTranslationInterface>
 */
class AttributeTranslationRepository extends EntityRepository implements AttributeTranslationRepositoryInterface
{
}
