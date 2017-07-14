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
require_model('bancos.php');
require_model('cuenta_banco.php');
require_model('ejercicio.php');
require_model('subcuenta.php');
require_model('tiponomina.php');
/**
 * Description of nomina_cb_generar_archivo
 *
 * @author Joe Nilson <joenilson at gmail.com>
 */
class nomina_cb_generar_archivo extends nomina_cb_controller {
    public $bancos;
    public $tiponomina;
    public $cuenta_banco;
    public function __construct() {
        parent::__construct(__CLASS__, 'Generar Archivo Pago', 'nomina');
    }
        
    protected function private_core() {
        parent::private_core();
        $this->bancos = new bancos();
        $this->tiponomina = new tiponomina();
        $this->cuenta_banco = new cuenta_banco();
        $this->share_extensions();
        

    }
       
    public function get_subcuentas_pago() {
        $subcuentas_pago = array();

        $eje0 = new ejercicio();
        $ejercicio = $eje0->get_by_fecha($this->today());
        if ($ejercicio) {
            /// añadimos todas las subcuentas de caja
            $sql = "SELECT * FROM co_subcuentas WHERE idcuenta IN "
                    . "(SELECT idcuenta FROM co_cuentas WHERE codejercicio = "
                    . $ejercicio->var2str($ejercicio->codejercicio) . " AND idcuentaesp = 'CAJA');";
            $data = $this->db->select($sql);
            if ($data) {
                foreach ($data as $d) {
                    $subcuentas_pago[] = new subcuenta($d);
                }
            }
        }

        return $subcuentas_pago;
    }

    public function share_extensions(){
        
    }
}
