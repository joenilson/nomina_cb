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

require_model('archivobanco.php');
require_model('lineasarchivobanco.php');
require_model('opcionesbanco.php');
/**
 * Description of nomina_cb_procesar_archivo
 *
 * @author Joe Nilson <joenilson at gmail.com>
 */
class nomina_cb_procesar_archivo extends nomina_cb_controller {
    public $bancos;
    public $resultado;
    public $resultado_total_importe;
    public $codbanco;
    public $tipoarchivo;
    public $codigo_empresa;
    public $email_contacto;
    public $coddivisa;
    public $codsubcuenta;
    public $periodo;
    public $mes;
    public $lineas;
    public $sec_lineas;
    public $preliminar;
    private $archivobanco;
    private $opcionesbanco;
    public function __construct() {
        parent::__construct(__CLASS__, 'Procesar Archivo Pago', 'nomina', FALSE, FALSE);
    }
    
    protected function private_core() {
        parent::private_core();
        $this->opcionesbanco = new opcionesbanco();
        $this->resultado = false;
        $this->codbanco = \filter_input(INPUT_POST, 'codbanco');
        $this->tipoarchivo = \filter_input(INPUT_POST, 'tipoarchivo');
        $this->codsubcuenta = \filter_input(INPUT_POST, 'codsubcuenta');
        $this->coddivisa = \filter_input(INPUT_POST, 'coddivisa');
        $this->periodo = \filter_input(INPUT_POST, 'periodo');
        $this->mes = \filter_input(INPUT_POST, 'mes');
        $this->lineas = \filter_input(INPUT_POST,'codagente', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $ob=$this->opcionesbanco->get($this->empresa->id, $this->codbanco);
        $this->codigo_empresa = ($ob)?$ob->codempresa:'';
        $this->email_contacto = ($ob)?$ob->email_contacto:'';
        if(\filter_input(INPUT_POST, 'procesar_archivo')){
            $this->guardar_archivo();
        }elseif(isset($_FILES['archivo'])){
            $archivo = $_FILES['archivo'];
            $this->preliminar = new archivobanco();
            $this->preliminar->codbanco = $this->codbanco;
            $this->preliminar->periodo = $this->periodo;
            $this->preliminar->secuencia = str_pad($this->preliminar->secuencia(),7,'0',STR_PAD_LEFT);
            if($archivo){
                $this->resultado_total_importe = 0;
                $this->resultado = $this->leer_archivo($archivo);
            }
        }
    }
    
    public function guardar_archivo(){
        $archivo = new archivobanco();
        $archivo->archivo = \filter_input(INPUT_POST, 'nombre_archivo');
        $archivo->codbanco = $this->codbanco;
        $archivo->coddivisa = $this->coddivisa;
        $archivo->codsubcuenta_pago = $this->codsubcuenta;
        $archivo->estado = TRUE;
        $archivo->enviado = FALSE;
        $archivo->procesado = FALSE;
        $archivo->periodo = $this->periodo;
        $archivo->mes = $this->mes;
        $archivo->tipo_archivo = $this->tipoarchivo;
        $archivo->total = \filter_input(INPUT_POST, 'importe_total');
        $archivo->fecha_creacion = \date('Y-m-d H:i:s');
        $archivo->usuario_creacion = $this->user->nick;
        if($archivo->save()){
            if(!$this->guardar_linea_archivo($archivo->id)){
                $archivo->delete();
                $this->new_message('¡Ocurrió un error guardando la información del archivo, se han eliminado los registros del mismo hasta que se corrija la información! '.$archivo->id);
            }else{
                $this->archivobanco = $archivo;
                header('location: '.FS_PATH.FS_MYDOCS.'index.php?page=nomina_cb_generar_archivo&id='.$archivo->id);
            }
        }
        
    }
    
    public function guardar_linea_archivo($idarchivo){
        $estado = false;
        foreach($this->lineas as $cod){
            $agente = $this->agente->get($cod);
            $tipocuenta = $this->tipocuenta->get($agente->tipo_cuenta);
            $banco = ($agente)?$this->bancos->get($agente->codbanco):false;
            $monto = \filter_input(INPUT_POST, 'importe_'.$cod);
            $lineaarchivo = new lineasarchivobanco();
            $lineaarchivo->idarchivo = $idarchivo;
            $lineaarchivo->banco_receptor = $agente->codbanco;
            $lineaarchivo->codagente = $cod;
            $lineaarchivo->cuenta_banco = $agente->cuenta_banco;
            $lineaarchivo->tipo_cuenta = (!empty($tipocuenta->codigo_banco))?$tipocuenta->codigo_banco:$agente->tipo_cuenta;
            $lineaarchivo->periodo = $this->periodo;
            $lineaarchivo->mes = $this->mes;
            $lineaarchivo->monto = $monto;
            if(!$lineaarchivo->save()){
                $this->new_message('¡Ocurrió un error guardando la información del empleado '.$agente->nombreap.', revise la información e intente nuevamente!');
                $estado = false;
                break;
            }else{
                $estado = true;
            }
        }
        return $estado;
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
        
        return $this->agregar_valores($lista);
        
    }
    
    public function agregar_valores($lista){
        $res = array();
        if($lista){
            foreach($lista as $linea){
                $importe = round($linea[1],2);
                //$importe = 100;
                $item = new stdClass();
                $item->dnicif = str_pad($linea[0],11,'0',STR_PAD_LEFT);
                $agente = $this->agente->get_by_dnicif($item->dnicif);
                $banco = ($agente)?$this->bancos->get($agente->codbanco):false;
                $tipocuenta = ($agente and !empty($agente->tipo_cuenta))?$this->tipocuenta->get($agente->tipo_cuenta):false;
                $item->codagente = ($agente)?$agente->codagente:false;
                $item->agente = ($agente)?$agente->nombreap:false;
                $item->cuenta_banco = ($agente)?$agente->cuenta_banco:false;
                $item->codbanco = ($banco)?$banco->codbanco:false;
                $item->desc_banco = ($banco)?$banco->nombre. ' - '.$banco->codigo_alterno:false;
                $item->tipo_cuenta = ($tipocuenta)?$tipocuenta->codtipo.' - '.$tipocuenta->codigo_banco:false;
                $item->importe = \number_format($importe,FS_NF0, FS_NF1, '');
                $res[] = $item;
                $this->resultado_total_importe += $item->importe;
            }
        }
        return $res;
    }
    
}
