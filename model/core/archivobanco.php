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

namespace FacturaScripts\model;
require_model('lineasarchivobanco.php');
/**
 * Description of archivobanco
 *
 * @author Joe Nilson <joenilson at gmail.com>
 */
class archivobanco extends \fs_model {
    /**
     * Id del archivo de banco
     * @var integer
     */
    public $id;
    /**
     * Nombre del archivo generado
     * @var varchar(100)
     */
    public $archivo;
    /**
     * Año de la nómina YYYY
     * @var integer
     */
    public $periodo;
    /**
     * Mes de la nómina YYYYMM
     * @var integer
     */
    public $mes;
    /**
     * Secuencia de la nomina
     * @var integer
     */
    public $secuencia;
    /**
     * Codigo del banco pagador
     * @var varchar(6)
     */
    public $codbanco;
    /**
     * El tipo de archivo que se generó TXT o PDF
     * @var varchar(6)
     */
    public $tipo_archivo;
    /**
     * La subcuenta donde se contabilizará la salida de dinero
     * @var varchar(15)
     */
    public $codsubcuenta_pago;
    /**
     * Valor total del pago procesado
     * @var float
     */
    public $total;
    /**
     * Divisa del archivo a pagar
     * @var varchar(3)
     */
    public $coddivisa;
    /**
     * Estado del archivo si fué enviado al banco
     * @var boolean
     */
    public $enviado;
    /**
     * Estado del archivo si fue procesado por el banco
     * @var boolean
     */
    public $procesado;
    /**
     * Activo o inactivo el archivo de banco
     * se inactiva si es que hubo un error y no se envió al banco
     * o se generó erroneamente
     * @var boolean
     */
    public $estado;
    /**
     * Fecha de creación del registro
     * @var timestamp without timezone
     */
    public $fecha_creacion;
    /**
     * Usuario que crea el registro
     * @var varchar(12)
     */
    public $usuario_creacion;
    /**
     * Fecha de modificacion del registro
     * @var timestamp without timezone
     */
    public $fecha_modificacion;
    /**
     * Usuario que modifica el registro
     * @var varchar(12)
     */
    public $usuario_modificacion;
    
    public function __construct($t = '') {
        parent::__construct('hr_archivobanco');
        if($t){
            $this->id = $t['id'];
            $this->archivo = $t['archivo'];
            $this->periodo = $t['periodo'];
            $this->mes = $t['mes'];
            $this->secuencia = $t['secuencia'];
            $this->coddivisa = $t['coddivisa'];
            $this->codbanco = $t['codbanco'];
            $this->tipo_archivo = $t['tipo_archivo'];
            $this->codsubcuenta_pago = $t['codsubcuenta_pago'];
            $this->total = floatval($t['total']);
            $this->enviado = $this->str2bool($t['enviado']);
            $this->procesado = $this->str2bool($t['procesado']);
            $this->estado = $this->str2bool($t['estado']);
            $this->fecha_creacion = \date('d-m-Y', strtotime($t['fecha_creacion']));
            $this->usuario_creacion = $t['usuario_creacion'];
            $this->fecha_modificacion = \date('d-m-Y', strtotime($t['fecha_modificacion']));
            $this->usuario_modificacion = $t['usuario_modificacion'];
        }else{
            $this->id = null;
            $this->archivo = null;
            $this->periodo = null;
            $this->mes = null;
            $this->secuencia = null;
            $this->coddivisa = null;
            $this->codbanco = null;
            $this->tipo_archivo = null;
            $this->codsubcuenta_pago = null;
            $this->total = 0;
            $this->enviado = false;
            $this->procesado = false;
            $this->estado = false;
            $this->fecha_creacion = null;
            $this->usuario_creacion = null;
            $this->fecha_modificacion = null;
            $this->usuario_modificacion = null;
        }
        new lineasarchivobanco();
    }
    
    protected function install() {
        return '';
    }
    
    public function exists() {
        if(is_null($this->id)){
            return false;
        }else{
            return $this->get($this->id);
        }
    }
    
    public function all()
    {
        $lista = array();
        $sql = "SELECT * FROM ".$this->table_name." ORDER BY periodo,mes,codbanco;";
        $data = $this->db->select($sql);
        if($data){
            foreach($data as $d) {
                $linea = new archivobanco($d);
                $lista[] = $linea;
            }
        }
        return $lista;
    }
    
    public function get($id)
    {
        $item = '';
        $sql = "SELECT * FROM ".$this->table_name." WHERE id = ".$this->intval($id);
        $data = $this->db->select($sql);
        if(!empty($data)){
            $item = new archivobanco($data[0]);
        }
        return $item;
    }
    
    public function get_lineas()
    {
        $lineas = new lineasarchivobanco();
        return $lineas->all_from_archivo($this->id);
    }
    
    public function secuencia(){
        $sql = "SELECT max(secuencia) as siguiente FROM ".$this->table_name.
            " WHERE periodo = ".$this->intval($this->periodo).
            " AND codbanco = ".$this->var2str($this->codbanco);
        $data = $this->db->select($sql);
        return $data[0]['siguiente']+1;
    }
    
    public function save() {
        if($this->exists()){
            $sql = "UPDATE ".$this->table_name." SET ".
                "archivo = ".$this->var2str($this->archivo).
                ", periodo = ".$this->intval($this->periodo).
                ", mes = ".$this->intval($this->mes).
                ", secuencia = ".$this->intval($this->secuencia).
                ", coddivisa = ".$this->var2str($this->coddivisa).
                ", codbanco = ".$this->var2str($this->codbanco).
                ", tipo_archivo = ".$this->var2str($this->tipo_archivo).
                ", codsubcuenta_pago = ".$this->var2str($this->codsubcuenta_pago).
                ", total = ".$this->var2str($this->total).
                ", enviado = ".$this->var2str($this->enviado).
                ", procesado = ".$this->var2str($this->procesado).
                ", estado = ".$this->var2str($this->estado).
                ", fecha_modificacion = ".$this->var2str($this->fecha_modificacion).
                ", usuario_modificacion = ".$this->var2str($this->usuario_modificacion).
                " WHERE id = ".$this->intval($this->id);
            return $this->db->exec($sql);
        } else {
            $this->secuencia = $this->secuencia();
            $sql = "INSERT INTO ".$this->table_name.
                " (archivo,periodo,mes,secuencia,coddivisa,codbanco,tipo_archivo,codsubcuenta_pago,total, enviado, procesado, estado, fecha_creacion, usuario_creacion) ".
                " VALUES (".
                $this->var2str($this->archivo).
                ",".$this->intval($this->periodo).
                ",".$this->intval($this->mes).
                ",".$this->intval($this->secuencia).
                ",".$this->var2str($this->coddivisa).
                ",".$this->var2str($this->codbanco).
                ",".$this->var2str($this->tipo_archivo).
                ",".$this->var2str($this->codsubcuenta_pago).
                ",".$this->var2str($this->total).
                ",".$this->var2str($this->enviado).
                ",".$this->var2str($this->procesado).
                ",".$this->var2str($this->estado).
                ",".$this->var2str($this->fecha_creacion).
                ",".$this->var2str($this->usuario_creacion).
                ");";
            if( $this->db->exec($sql) ) {
                $this->id = $this->db->lastval();
                return TRUE;
            } else {
                return FALSE;
            }
        }
    }
    
    public function delete() {
        if($this->exists()){
            $sql = "DELETE FROM ".$this->table_name." WHERE id = ".$this->intval($this->id);
            return $this->db->exec($sql);
        }
        return false;
    }
}
