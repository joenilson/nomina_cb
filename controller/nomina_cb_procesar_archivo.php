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
require_once 'plugins/nomina_cb/vendor/PHPOffice/PHPExcel.php';

require_model('agente.php');
require_model('bancos.php');
/**
 * Description of nomina_cb_procesar_archivo
 *
 * @author Joe Nilson <joenilson at gmail.com>
 */
class nomina_cb_procesar_archivo extends nomina_cb_controller {
    public $agente;
    public $bancos;
    public $resultado;
    public $resultado_total_importe;
    public function __construct() {
        parent::__construct(__CLASS__, 'Procesar Archivo Pago', 'nomina', FALSE, FALSE);
    }
    
    protected function private_core() {
        parent::private_core();
        $this->agente = new agente();
        $this->bancos = new bancos();
        $this->resultado = false;
        $archivo = $_FILES['archivo'];
        if($archivo){
            $this->resultado_total_importe = 0;
            $this->resultado = $this->leer_archivo($archivo);    
        }                    
    }
    
    public function leer_archivo($archivo) {
        $objPHPExcel = PHPExcel_IOFactory::load($archivo['tmp_name']);
        $worksheet = $objPHPExcel->getSheet(0);
        $lista = array();
        foreach ($worksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
            $item = array();
            foreach ($cellIterator as $cell) {
                $item[] = $cell->getCalculatedValue();
            }
            $lista[] = $item;
        }
        $res = array();
        foreach($lista as $linea){
            $item = new stdClass();
            $item->cifnif = str_pad($linea[0],11,'0',STR_PAD_LEFT);
            $agente = $this->agente->get_by_dnicif($item->cifnif);
            $banco = ($agente)?$this->bancos->get($agente->codbanco):false;
            $item->agente = ($agente)?$agente->nombreap:false;
            $item->cuenta_banco = ($agente)?$agente->cuenta_banco:false;
            $item->codbanco = ($banco)?$banco->codbanco:false;
            $item->desc_banco = ($banco)?$banco->nombre:false;
            $item->tipo_cuenta = ($agente)?$agente->tipo_cuenta:false;
            $item->importe = round($linea[1],0);
            $res[] = $item;
            $this->resultado_total_importe += $item->importe;
        }
        return $res;
    }
    
}
