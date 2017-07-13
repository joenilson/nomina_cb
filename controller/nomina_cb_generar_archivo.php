<?php

/*
 * Copyright (C) 2017 Joe Nilson <joenilson at gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
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
 * Description of nomina_cb_generar_archivo
 *
 * @author Joe Nilson <joenilson at gmail.com>
 */
class nomina_cb_generar_archivo extends nomina_cb_controller {
    public function __construct() {
        parent::__construct(__CLASS__, 'Generar Archivo Pago', 'nomina');
    }
        
    protected function private_core() {
        parent::private_core();
        $this->share_extensions();
    }
    
    public function share_extensions(){
        
    }
}
