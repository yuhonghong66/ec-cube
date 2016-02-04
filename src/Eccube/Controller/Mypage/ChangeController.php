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


namespace Eccube\Controller\Mypage;

use Eccube\Application;
use Eccube\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;

class ChangeController extends AbstractController
{

    /**
     * Index
     *
     * @param  Application $app
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function index(Application $app, Request $request)
    {
        $Customer = $app->user();
        $CustomerForRestore = clone $app->user();

        $previous_password = $Customer->getPassword();
        $Customer->setPassword($app['config']['default_password']);

        /* @var $builder \Symfony\Component\Form\FormBuilderInterface */
        $builder = $app['form.factory']->createBuilder('entry', $Customer);

        /* @var $form \Symfony\Component\Form\FormInterface */
        $form = $builder->getForm();

        $event = new EventArgs(array(
                'form' => $form,
                'customer' => $Customer
            )
        );
        $app['eccube.event.dispatcher']->dispatch(EccubeEvents::MYPAGE_CHANGE_INDEX_INITIALIZE, $event);

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $event = new EventArgs(array(
                        'form' => $form,
                    )
                );
                $app['eccube.event.dispatcher']->dispatch(EccubeEvents::MYPAGE_CHANGE_INDEX_COMPLETE, $event);

                if ($Customer->getPassword() === $app['config']['default_password']) {
                    $Customer->setPassword($previous_password);
                } else {
                    $Customer->setPassword(
                        $app['eccube.repository.customer']->encryptPassword($app, $Customer)
                        );
                }

                $app['orm.em']->persist($Customer);
                $app['orm.em']->flush();

                return $app->redirect($app->url('mypage_change_complete'));

            } else {
                // invalidでもSession上の$app->user()が置き換えられてしまうため復元する
                $Customer = $CustomerForRestore;
                $this->getSecurity($app)->getToken()->setUser($Customer);
            }
        }

        return $app->renderView('Mypage/change.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * Complete
     *
     * @param  Application $app
     * @return mixed
     */
    public function complete(Application $app, Request $request)
    {
        return $app->renderView('Mypage/change_complete.twig');
    }
}
