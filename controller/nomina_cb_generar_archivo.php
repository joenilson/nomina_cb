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
require_model('archivobanco.php');
require_model('opcionesbanco.php');
/**
 * Description of nomina_cb_generar_archivo
 *
 * @author Joe Nilson <joenilson at gmail.com>
 */
class nomina_cb_generar_archivo extends nomina_cb_controller {

    public $archivobanco;
    public $opcionesbanco;
    public $infobancos;
    public $id;
    public $resultado;
    public $resultado_total_importe;
    public $idarchivo_ver;
    public $sec_lineas;
    public function __construct() {
        parent::__construct(__CLASS__, 'Generar Archivo Pago', 'nomina');
    }
        
    protected function private_core() {
        parent::private_core();
        $this->share_extensions();
        $this->archivobanco = new archivobanco();
        $this->opcionesbanco = new opcionesbanco();
        $this->id = \filter_input(INPUT_GET, 'id');
        
        if(\filter_input(INPUT_POST, 'opciones_archivo')){
            $this->opciones_archivo();
        }
        
        if(\filter_input(INPUT_GET, 'confirmar')){
            $this->confirmar_archivo();
        }
        
        if(\filter_input(INPUT_GET, 'desconfirmar')){
            $this->confirmar_archivo();
        }
        
        if(\filter_input(INPUT_GET, 'eliminar')){
            $this->eliminar_archivo();
        }
        
        if(\filter_input(INPUT_GET, 'mostrar')){
            $this->mostrar_archivo();
        }
        
        if(\filter_input(INPUT_GET, 'descargar')){
            $this->descargar_archivo();
        }
        $this->infobancos = $this->infobancos();
    }
    
    public function infobancos()
    {
        $bancos = $this->bancos->all();
        $lista = array();
        foreach($bancos as $b){
            $ob0 = $this->opcionesbanco->get($this->empresa->id, $b->codbanco);
            $item = new stdClass();
            $item->codbanco = $b->codbanco;
            $item->nombre = $b->nombre;
            $item->codempresa = ($ob0)?$ob0->codempresa:'';
            $item->email_contacto = ($ob0)?$ob0->email_contacto:'';
            $lista[] = $item;
        }
        return $lista;
    }
    
    public function opciones_archivo()
    {
        $lista_bancos = $this->bancos->all();
        $error = 0;
        $exito = 0;
        foreach($lista_bancos as $banco){
            $opc0 = new opcionesbanco();
            $opc0->idempresa = $this->empresa->id;
            $opc0->codbanco = $banco->codbanco;
            $opc0->codempresa = \filter_input(INPUT_POST, 'codigo_empresa_'.$banco->codbanco);
            $opc0->email_contacto = \filter_input(INPUT_POST, 'email_contacto_'.$banco->codbanco);
            $opc0->fecha_creacion = \date('Y-m-d H:i:s');
            $opc0->usuario_creacion = $this->user->nick;
            $opc0->fecha_modificacion = \date('Y-m-d H:i:s');
            $opc0->usuario_modificacion = $this->user->nick;
            if($opc0->save()){
                $exito++;
            }else{
                $error++;
            }
        }
        $this->new_message('Se han guardado las opciones de '.$exito.' bancos');
    }
    
    public function cabecera_txt($archivo)
    {
        $cantidad_registros = count($archivo->get_lineas());
        $string = 'H';
        $string .= \str_pad($this->empresa->cifnif,15,' ');
        $string .= \str_pad(substr($this->empresa->nombre,0,35),35,' ');
        $string .= "01";
        $string .= \str_pad($archivo->secuencia, 7,"0",STR_PAD_LEFT);
        $string .= \date('Ymd');
        $string .= \str_pad(0, 11,"0",STR_PAD_LEFT);
        $string .= \str_pad(0, 13,"0",STR_PAD_LEFT);
        $string .= \str_pad($cantidad_registros, 11,"0",STR_PAD_LEFT);
        $string .= \str_pad(\number_format($archivo->total,2,'',''), 13,"0",STR_PAD_LEFT);
        $string .= \str_pad(0, 15,"0",STR_PAD_LEFT);
        $string .= \date('Ymd');
        $string .= \date('Hi');
        $string .= \str_pad(substr($this->empresa->email,0,40),40,' ');
        $string .= " ";
        $string .= \str_pad(" ", 136," ");
        $string .= "\n";
        return $string;
    }
    
    public function linea_txt($l,$sec,$coddivisa)
    {
        $agente = $this->agente->get($l->codagente);
        $divisa = $this->divisa->get($coddivisa);
        $nombre_completo = $agente->nombre.' '.$agente->apellidos.' '.$agente->segundo_apellido;
        $string = "N";
        $string .= \str_pad($this->empresa->cifnif,15,' ');
        $string .= \str_pad($this->sec_lineas, 7,"0",STR_PAD_LEFT);
        $string .= \str_pad($sec, 7,"0",STR_PAD_LEFT);
        $string .= \str_pad($agente->cuenta_banco, 20,"0",STR_PAD_LEFT);
        $string .= \str_pad((int) $agente->tipo_cuenta,1,' ');
        $string .= \str_pad((int) $divisa->codiso, 3,"0",STR_PAD_LEFT);
        $string .= \str_pad('10101070', 8,"0",STR_PAD_LEFT);
        $string .= 8;
        $string .= "22";
        $string .= \number_format($l->monto,FS_NF0,'','');
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

    public function descargar_archivo()
    {
        $this->template = false;
        $id = \filter_input(INPUT_GET, 'idarchivo');
        $archivo = $this->archivobanco->get($id);
        if ($archivo->tipo_archivo == 'txt') {
            header("content-type:text/plain;charset=UTF-8");
            header("Content-Disposition: attachment; filename=\"".$archivo->archivo."\"");
            $cabecera = $this->cabecera_txt($archivo);
            echo $cabecera;
            $this->sec_lineas = 1;
            $lineas = $archivo->get_lineas();
            foreach ($lineas as $l) {
                $linea = $this->linea_txt($l, $archivo->secuencia, $archivo->coddivisa);
                echo $linea;
                $this->sec_lineas++;
            }
        }
    }
    
    public function mostrar_archivo()
    {
        $this->template = 'archivo_generado';
        $this->resultado = array();
        $id = \filter_input(INPUT_GET, 'idarchivo');        
        $ab0 = $this->archivobanco->get($id);
        $this->resultado_total_importe = $ab0->total;
        $this->idarchivo_ver = $id;
        if($ab0){
            $agt0 = new agente();
            $lineas = $ab0->get_lineas();
            foreach($lineas as $linea){
                $a = $agt0->get($linea->codagente);
                $banco = $this->bancos->get($linea->banco_receptor);
                $linea->agente = $a->nombre.' '.$a->apellidos.' '.$a->segundo_apellido; 
                $linea->dnicif = $a->dnicif;
                $linea->desc_banco = $banco->nombre;
                $this->resultado[] = $linea;
            }
        }
    }
    
    public function eliminar_archivo()
    {
        $id = \filter_input(INPUT_POST, 'idarchivo');
        $data = array();
        $ab0 = $this->archivobanco->get($id);
        if($ab0){
            if($ab0->delete()){
                $data['success'] = true;
                $data['mensaje'] = "¡arcnivo eliminado correctamente!";
            }else{
                $data['success'] = false;
                $data['mensaje'] = "¡No se pudo eliminar la información del archivo!";
            }
        }else{
            $data['success'] = false;
            $data['mensaje'] = "¡No existe el archivo!";
        }
        $this->template = false;
        header('Content-Type: application/json');
        echo json_encode($data);
    }
    
    public function confirmar_archivo()
    {
        $id = \filter_input(INPUT_POST, 'idarchivo');
        $enviado =\filter_input(INPUT_POST, 'enviado');
        $procesado =\filter_input(INPUT_POST, 'procesado');
        $data = array();
        $ab0 = $this->archivobanco->get($id);
        if($ab0){
            if($enviado){
                $ab0->enviado = ($enviado=='TRUE')?TRUE:FALSE;
            }
            if($procesado){
                $ab0->procesado = ($procesado=='TRUE')?TRUE:FALSE;
            }
            $ab0->fecha_modificacion = \date('Y-m-d H:i:s');
            $ab0->usuario_modificacion = $this->user->nick;
            $pre = ($enviado=='FALSE' OR $procesado == 'FALSE')?'Des':'';
            if($ab0->save()){
                $data['success'] = true;
                $data['mensaje'] = "¡Archivo {$pre}confirmado correctamente!";
            }else{
                $data['success'] = false;
                $data['mensaje'] = "¡No se pudo {$pre}confirmar la información del archivo!";
            }
        }else{
            $data['success'] = false;
            $data['mensaje'] = "¡No existe el archivo!";
        }
        $this->template = false;
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    public function share_extensions(){
        
    }
}
