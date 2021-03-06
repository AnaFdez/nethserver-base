<?php
namespace NethServer\Module\NetworkAdapter;

/*
 * Copyright (C) 2012 Nethesis S.r.l.
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

use Nethgui\System\PlatformInterface as Validate;
use Nethgui\Controller\Table\Modify as Table;

/**
 * Modify domain
 *
 * Generic class to create/update/delete Domain records
 * 
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class Modify extends \Nethgui\Controller\Table\Modify
{
    /**
     * @var Array list of valid roles
     */
    private $roles = array('green', 'red');

    public function initialize()
    {
        $parameterSchema = array(
            array('device', Validate::USERNAME, \Nethgui\Controller\Table\Modify::KEY),
            array('hwaddr', Validate::MACADDRESS, \Nethgui\Controller\Table\Modify::FIELD),
            array('role', $this->getPlatform()->createValidator()->memberOf($this->roles), \Nethgui\Controller\Table\Modify::FIELD),
            array('bootproto', $this->getPlatform()->createValidator()->memberOf(array('dhcp', 'static')), \Nethgui\Controller\Table\Modify::FIELD),
            array('ipaddr', Validate::IPv4_OR_EMPTY, \Nethgui\Controller\Table\Modify::FIELD),
            array('netmask', Validate::NETMASK_OR_EMPTY, \Nethgui\Controller\Table\Modify::FIELD),
            array('gateway', Validate::IPv4_OR_EMPTY, \Nethgui\Controller\Table\Modify::FIELD),
        );

        $this->setSchema($parameterSchema);
        $this->setDefaultValue('bootproto', 'static');

        parent::initialize();
    }

    public function bind(\Nethgui\Controller\RequestInterface $request)
    {
        parent::bind($request);
        // The delete case does not actually delete the record: it set role prop
        // to ''. See also delete()
        if ($this->getIdentifier() === 'delete') {
            $this->parameters['role'] = '';
            $this->parameters['bootproto'] = '';
        }
    }

    /**
     * Parse nic-info helper command output
     *
     * @param \Nethgui\View\ViewInterface $view
     * @return type
     */
    private function getNicInfo(\Nethgui\View\ViewInterface $view)
    {
        $v = array();
        $nicInfo = array();

        // only execute helper if request has been validated:
        if ($this->getRequest()->isValidated()) {
            // Array of informations about NIC.
            // Fields: name, hwaddr, bus, model, driver, speed, link
            // Eg: green,08:00:27:77:fd:be,pci,Intel Corporation 82540EM Gigabit Ethernet Controller (rev 02),e1000,1000,1
            $nicInfo = str_getcsv($this->getPlatform()->exec('/usr/libexec/nethserver/nic-info ' . $this->parameters['device'])->getOutput());
        }
        
        $v['bus'] = isset($nicInfo[2]) ? $nicInfo[2] : "";
        $v['model'] = isset($nicInfo[3]) ? $nicInfo[3] : "";
        $v['driver'] = isset($nicInfo[4]) ? $nicInfo[4] : "";
        $v['speed'] = isset($nicInfo[5]) ? $nicInfo[5] : "";
        if ( ! isset($nicInfo[6]) || (intval($nicInfo[6]) < 0)) {
            $v['link'] = "N/A";
        } else {
            $v['link'] = $nicInfo[6] ? $view->translate('Yes') : $view->translate('No');
        }

        return $v;
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        
        if ($this->getIdentifier() === 'update') {
            $view['roleDatasource'] = array_map(function($fmt) use ($view) {
                    return array($fmt, $view->translate($fmt . '_label'));
                }, $this->roles);
            $view->copyFrom($this->getNicInfo($view));
        }

        $templates = array(
            'create' => 'NethServer\Template\NetworkAdapter\Modify',
            'update' => 'NethServer\Template\NetworkAdapter\Modify',
            'delete' => 'Nethgui\Template\Table\Delete',
        );
        $view->setTemplate($templates[$this->getIdentifier()]);
    }

    public function validate(\Nethgui\Controller\ValidationReportInterface $report)
    {
        parent::validate($report);
        if ($this->getRequest()->isMutation()) {
            $v = $this->createValidator()->platform('interface-config');
            if ( ! $v->evaluate(json_encode($this->parameters->getArrayCopy()))) {
                $report->addValidationError($this, 'device', $v);
            }
        }
    }

    /**
     * Parent's implementation deletes the record. Here, don't delete
     * the record: we set role prop to '' in bind()
     * 
     * @param string $key
     */
    protected function processDelete($key)
    {
        // skip parent's implementation
    }

    protected function onParametersSaved($changedParameters)
    {
        $this->getPlatform()->signalEvent('interface-update@post-response &');
    }

}
