<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eccube\Entity\Master;

use Doctrine\ORM\Mapping as ORM;

/**
 * DeviceType
 *
 * @ORM\Table(name="mtb_device_type")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="Eccube\Repository\Master\DeviceTypeRepository")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class DeviceType extends \Eccube\Entity\Master\AbstractMasterEntity
{
    const DEVICE_TYPE_MB = 1;
    const DEVICE_TYPE_SP = 2;
    // const DEVICE_TYPE_TABLET = 3;
    const DEVICE_TYPE_PC = 10;
    const DEVICE_TYPE_ADMIN = 99;
}
