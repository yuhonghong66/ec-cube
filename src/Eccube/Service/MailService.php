<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace Eccube\Service;

use Eccube\Application;

class MailService
{
    /** @var \Eccube\Application */
    public $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Send order mail.
     *
     * @param Order $Order
     */
    public function sendOrderMail(\Eccube\Entity\Order $Order)
    {

        $body = $this->app['view']->render('Mail/order.twig', array(
            'Order' => $Order,
        ));

        $message = \Swift_Message::newInstance()
            ->setSubject('[EC-CUBE3] 購入が完了しました。')
            ->setFrom(array('sample@example.com'))
            ->setBcc($this->app['config']['mail_cc'])
            ->setTo(array($Order->getEmail()))
            ->setBody($body);

        $this->app['mailer']->send($message);

    }

}
