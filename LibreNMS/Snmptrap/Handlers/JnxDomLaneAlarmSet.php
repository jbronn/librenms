<?php
/**
 * JnxDomLaneAlarmSet.php
 *
 * -Description-
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link       http://librenms.org
 * @copyright  2018 KanREN, Inc.
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;
use Log;

class JnxDomLaneAlarmSet implements SnmptrapHandler
{
    /**
     * Handle snmptrap.
     * Data is pre-parsed and delivered as a Trap.
     *
     * @param Device $device
     * @param Trap $trap
     * @return void
     */
    public function handle(Device $device, Trap $trap)
    {
        $currentAlarm = $trap->getOidData($trap->findOid('JUNIPER-DOM-MIB::jnxDomCurrentLaneAlarms'));
        $lane = $trap->getOidData($trap->findOid('JUNIPER-DOM-MIB::jnxDomLaneIndex'));
        $alarmList = JnxDomLaneAlarmId::getLaneAlarms($currentAlarm);

        $ifIndex = substr(strrchr($trap->findOid('IF-MIB::ifDescr'), '.'), 1);
        $port = $device->ports()->where('ifIndex', $ifIndex)->first();
        if (! $port) {
            Log::warning("Snmptrap JnxDomLaneAlarmSet: Could not find port at ifIndex $port->ifIndex for device: " . $device->hostname);

            return;
        }

        Log::event("DOM lane alarm on interface $port->ifDescr lane $lane. Current alarm(s): $alarmList", $device->device_id, 'trap', 5);
    }
}