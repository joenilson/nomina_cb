<?php

/*
 * Copyright (C) 2017 Joe Nilson <joenilson at gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
require_once 'plugins/nomina_cb/extras/nomina_cb_controller.php';
/**
 * Description of configuracion_nomina_cb
 *
 * @author Joe Nilson <joenilson at gmail.com>
 */
class configuracion_nomina_cb extends nomina_cb_controller {
    public function __construct() {
        parent::__construct(__CLASS__, 'Configuracion C y B', 'nomina');
    }

    protected function private_core() {
        parent::private_core();
    }

    public function share_extensions(){

    }
}
