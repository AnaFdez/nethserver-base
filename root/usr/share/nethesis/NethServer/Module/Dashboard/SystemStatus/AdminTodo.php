<?php
namespace NethServer\Module\Dashboard\SystemStatus;

/*
 * Copyright (C) 2013 Nethesis S.r.l.
 *
 * This script is part of NethServer.
 *
 * NethServer is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NethServer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NethServer.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Retrieve Administrator todos
 *
 * @author Giacomo Sanchietti
 */
class AdminTodo extends \Nethgui\Controller\AbstractController
{

    public $sortId = -1;
 
    private $todos = array();
    private $todo;

    private function readAdminTodo()
    {
        $todos = $this->getPlatform()->getDatabase('todos')->getAll('todo');
        foreach ($todos as $todo => $props) {
            if ($props['status'] == 'disabled') {
                unset($todos[$todo]);
            } else { 
                $todos[$todo]['name'] = $todo;
            }
        }
        return $todos;
    }

    public function process()
    {
        if (isset($this->todo)) {
            $this->getPlatform()->getDatabase('todos')->setProp($this->todo, array('status' => 'disabled'));
        }
    }

    public function bind(\Nethgui\Controller\RequestInterface $request)
    {
        parent::bind($request);
        if ($request->isMutation()) {
           $this->todo = $this->getRequest()->getParameter('key');
        }
    }
 
    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        if ( ! $this->todos) {
            $this->todos = $this->readAdminTodo();
        }
        $view['todos'] = $this->todos;
	if(isset($this->todo)) {
            $view->getCommandList()->closeTodo($this->todo);
        }
    }
}
