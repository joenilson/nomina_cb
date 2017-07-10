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
 * Description of nomina_cb_beneficios
 *
 * @author Joe Nilson <joenilson at gmail.com>
 */
class nomina_cb_beneficios extends nomina_cb_controller {
    public function __construct() {
        parent::__construct(__CLASS__, 'Nomina CB :: Beneficios', 'nomina',FALSE,FALSE);
    }

    protected function private_core() {
        parent::private_core();
        $this->share_extensions();
    }

    public function share_extensions(){
        $extensiones = array(
            //Tabs de Configuracion
            array(
                'name' => 'config_nomina_cb_beneficios',
                'page_from' => __CLASS__,
                'page_to' => 'configuracion_nomina_cb',
                'type' => 'tab',
                    'text' => '<span class="fa fa-plus-circle" aria-hidden="true"></span>&nbsp;Beneficios',
                'params' => ''
            ),
        );

        foreach ($extensiones as $ext) {
            $fsext0 = new fs_extension($ext);
            if (!$fsext0->save()) {
                $this->new_error_msg('Imposible guardar los datos de la extensión ' . $ext['name'] . '.');
            }
        }
    }
}
