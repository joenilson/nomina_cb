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
    public $coddivisa;
    public $codsubcuenta;
    public $periodo;
    public $mes;
    public $lineas;
    public $sec_lineas;
    public $preliminar;
    private $archivobanco;
    public function __construct() {
        parent::__construct(__CLASS__, 'Procesar Archivo Pago', 'nomina', FALSE, FALSE);
    }
    
    protected function private_core() {
        parent::private_core();
        $this->resultado = false;
        $this->codbanco = \filter_input(INPUT_POST, 'codbanco');
        $this->tipoarchivo = \filter_input(INPUT_POST, 'tipoarchivo');
        $this->codsubcuenta = \filter_input(INPUT_POST, 'codsubcuenta');
        $this->coddivisa = \filter_input(INPUT_POST, 'coddivisa');
        $this->periodo = \filter_input(INPUT_POST, 'periodo');
        $this->mes = \filter_input(INPUT_POST, 'mes');
        $this->lineas = \filter_input(INPUT_POST,'codagente', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        
        if(\filter_input(INPUT_POST, 'procesar_archivo')){
            $this->guardar_archivo();
        }else{
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
            $monto = \filter_input(INPUT_POST, 'importe_'.$cod);
            $lineaarchivo = new lineasarchivobanco();
            $lineaarchivo->idarchivo = $idarchivo;
            $lineaarchivo->banco_receptor = $agente->codbanco;
            $lineaarchivo->codagente = $cod;
            $lineaarchivo->cuenta_banco = $agente->cuenta_banco;
            $lineaarchivo->tipo_cuenta = $agente->tipo_cuenta;
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
    
    public function cabecera()
    {
        $string = 'H';
        $string .= \str_pad($this->empresa->cifnif,15,' ');
        $string .= \str_pad(substr($this->empresa->nombre,0,35),35,' ');
        $string .= "01";
        $string .= \str_pad($this->archivobanco->secuencia, 7,"0",STR_PAD_LEFT);
        $string .= \date('Ymd');
        $string .= \str_pad(0, 11,"0",STR_PAD_LEFT);
        $string .= \str_pad(0, 13,"0",STR_PAD_LEFT);
        $string .= \str_pad(\filter_input(INPUT_POST, 'cantidad_registros'), 11,"0",STR_PAD_LEFT);
        $string .= \str_pad(\filter_input(INPUT_POST, 'importe_total'), 13,"0",STR_PAD_LEFT);
        $string .= \str_pad(0, 15,"0",STR_PAD_LEFT);
        $string .= \date('Ymd');
        $string .= \date('Hi');
        $string .= \str_pad(substr($this->empresa->email,0,40),40,' ');
        $string .= " ";
        $string .= \str_pad(" ", 136," ");
        $string .= "\n";
        return $string;
    }
    
    public function linea($cod)
    {
        $agente = $this->agente->get($cod);
        $divisa = $this->divisa->get($this->coddivisa);
        $monto = \filter_input(INPUT_POST, 'importe_'.$cod);
        $nombre_completo = $agente->nombre.' '.$agente->apellidos.' '.$agente->segundo_apellido;
        $string = "N";
        $string .= \str_pad($this->empresa->cifnif,15,' ');
        $string .= \str_pad($this->sec_lineas, 7,"0",STR_PAD_LEFT);
        $string .= \str_pad($this->archivobanco->secuencia, 7,"0",STR_PAD_LEFT);
        $string .= \str_pad($agente->cuenta_banco, 20,"0",STR_PAD_LEFT);
        $string .= \str_pad((int) $agente->tipo_cuenta,1,' ');
        $string .= \str_pad((int) $divisa->codiso, 3,"0",STR_PAD_LEFT);
        $string .= \str_pad($agente->codbanco, 8,"0",STR_PAD_LEFT);
        $string .= 8;
        $string .= "22";
        $string .= number_format($monto,FS_NF0,'','');
        $string .= "CE";
        $string .= \str_pad($agente->dnicif, 15," ",STR_PAD_RIGHT);
        $string .= \str_pad(substr($nombre_completo,0,35),35,' ');
        $string .= \str_pad(" ", 12," ");
        $string .= \str_pad(" ", 40," ");
        $string .= \str_pad(" ", 4," ");
        $string .= \str_pad(" ", 1," ");
        $string .= \str_pad(" ", 40," ");
        $string .= \str_pad(" ", 12," ");
        $string .= \str_pad("0", 2,"0");
        $string .= \str_pad(" ", 15," ");
        $string .= \str_pad(" ", 3," ");
        $string .= \str_pad(" ", 3," ");
        $string .= \str_pad(" ", 3," ");
        $string .= \str_pad(" ", 1," ");
        $string .= \str_pad(" ", 2," ");
        $string .= \str_pad(" ", 52," ");
        $string .= "\n";
        return $string;
    }

    public function crear_archivo()
    {
        $this->template = false;
        $archivo = \filter_input(INPUT_POST, 'nombre_archivo');
        if ($this->tipoarchivo == 'txt') {
            header("content-type:text/plain;charset=UTF-8");
            header("Content-Disposition: attachment; filename=\"$archivo\"");
            $cabecera = $this->cabecera();
            echo $cabecera;
            $this->sec_lineas = 1;
            foreach ($this->lineas as $l) {
                $linea = $this->linea($l);
                echo $linea;
                $this->sec_lineas++;
            }
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
                $item->codagente = ($agente)?$agente->codagente:false;
                $item->agente = ($agente)?$agente->nombreap:false;
                $item->cuenta_banco = ($agente)?$agente->cuenta_banco:false;
                $item->codbanco = ($banco)?$banco->codbanco:false;
                $item->desc_banco = ($banco)?$banco->nombre:false;
                $item->tipo_cuenta = ($agente)?$agente->tipo_cuenta:false;
                $item->importe = \number_format($importe,FS_NF0, FS_NF1, '');
                $res[] = $item;
                $this->resultado_total_importe += $item->importe;
                $this->show_numero();
            }
        }
        return $res;
    }
    
}
