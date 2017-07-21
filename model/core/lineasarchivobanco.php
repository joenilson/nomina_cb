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

/**
 * Lineas de los archivos de pago generados ya sea por banco o por pago en caja
 *
 * @author Joe Nilson <joenilson at gmail.com>
 */
class lineasarchivobanco extends \fs_model 
{
    /**
     * id de la linea a insertar
     * @var integer
     */
    public $idlinea;
    /**
     * id del archivo al que pertenece esta linea
     * @var integer
     */
    public $idarchivo;
    /**
     * codigo del empleado
     * @var varchar(10)
     */
    public $codagente;
    /**
     * Codigo del banco del empleado
     * @var varchar(6)
     */
    public $banco_receptor;
    /**
     * Cuenta de banco del empleado
     * @var varchar(34)
     */
    public $cuenta_banco;
    /**
     * Tipo de cuenta del empleado, ahorros, nomina, cuenta corriente
     * @var varchar(4)
     */
    public $tipo_cuenta;
    /**
     * Año de la nomina
     * @var integer
     */
    public $periodo;
    /**
     * Mes de la nomina
     * @var integer
     */
    public $mes;
    /**
     * monto a pagar al empleado
     * @var float
     */
    public $monto;
    public function __construct($t = '')
    {
        parent::__construct('hr_lineasarchivobanco');
        if($t){
            $this->idlinea = $t['idlinea'];
            $this->idarchivo = $t['idarchivo'];
            $this->codagente = $t['codagente'];
            $this->banco_receptor = $t['banco_receptor'];
            $this->cuenta_banco = $t['cuenta_banco'];
            $this->tipo_cuenta = $t['tipo_cuenta'];
            $this->periodo = $t['periodo'];
            $this->mes = $t['mes'];
            $this->monto = $t['monto'];
        }else{
            $this->idlinea = null;
            $this->idarchivo = null;
            $this->codagente = null;
            $this->banco_receptor = null;
            $this->cuenta_banco = null;
            $this->tipo_cuenta = null;
            $this->periodo = null;
            $this->mes = null;
            $this->monto = null;
        }
    }
    
    protected function install()
    {
        parent::install();
    }
    
    public function all()
    {
        $lineas = array();
        $sql = "SELECT * FROM ".$this->table_name." ORDER BY idlinea;";
        $data = $this->db->select($sql);
        if($data){
            foreach($data as $d){
                $linea = new lineasarchivobanco($d);
                $lineas[] = $linea;
            }
        }
        return $lineas;
    }
    
    public function all_from_archivo($idarchivo)
    {
        $lineas = array();
        $sql = "SELECT * FROM ".$this->table_name." WHERE idarchivo = ".$this->intval($idarchivo)." order by idlinea;";
        $data = $this->db->select($sql);
        if($data){
            foreach($data as $d){
                $linea = new lineasarchivobanco($d);
                $lineas[] = $linea;
            }
        }
        return $lineas;
    }
    
    public function all_from_agente($codagente)
    {
        $lineas = array();
        $sql = "SELECT * FROM ".$this->table_name." WHERE codagente = ".$this->var2str($codagente)." order by idlinea;";
        $data = $this->db->select($sql);
        if($data){
            foreach($data as $d){
                $linea = new lineasarchivobanco($d);
                $lineas[] = $linea;
            }
        }
        return $lineas;
    }

    public function all_from_banco_receptor($banco_receptor)
    {
        $lineas = array();
        $sql = "SELECT * FROM ".$this->table_name." WHERE banco_receptor = ".$this->var2str($banco_receptor)." order by idlinea;";
        $data = $this->db->select($sql);
        if($data){
            foreach($data as $d){
                $linea = new lineasarchivobanco($d);
                $lineas[] = $linea;
            }
        }
        return $lineas;
    }    
    
    public function exists()
    {
        if(is_null($this->idlinea)){
            return false;
        }else{
            return $this->get($this->idlinea);
        }
    }
    
    public function save()
    {
        if($this->exists()){
            $sql = "UPDATE ".$this->table_name." SET ".
                "idarchivo = ".$this->intval($this->idarchivo).
                ", codagente = ".$this->var2str($this->codagente).
                ", banco_receptor = ".$this->var2str($this->banco_receptor).
                ", cuenta_banco = ".$this->var2str($this->cuenta_banco).
                ", tipo_cuenta = ".$this->var2str($this->tipo_cuenta).
                ", periodo = ".$this->intval($this->periodo).
                ", mes = ".$this->intval($this->mes).
                ", monto = ".$this->var2str($this->monto).
                " WHERE idlinea = ".$this->intval($this->idlinea).
                ";";
            return $this->db->exec($sql);
        }else{
            $sql = "INSERT INTO ".$this->table_name.
                " (idarchivo, codagente, banco_receptor, cuenta_banco, tipo_cuenta, periodo, mes, monto ) ".
                " VALUES (".    
                $this->intval($this->idarchivo).
                ",".$this->var2str($this->codagente).
                ",".$this->var2str($this->banco_receptor).
                ",".$this->var2str($this->cuenta_banco).
                ",".$this->var2str($this->tipo_cuenta).
                ",".$this->intval($this->periodo).
                ",".$this->intval($this->mes).
                ",".$this->var2str($this->monto).
                ");";
            $this->db->exec($sql);
            $this->idlinea = $this->db->lastval();
            return true;
        }
        return false;
    }
    
    public function delete()
    {
        $sql = "DELETE FROM ".$this->table_name." WHERE idlinea = ".$this->intval($this->idlinea).";";
        return $this->db->exec($sql);
    }
    
    public function get($idlinea)
    {
        $sql = "SELECT * FROM ".$this->table_name." WHERE idlinea = ".$this->intval($idlinea).";";
        $data = $this->db->select($sql);
        if($data){
            return new lineasarchivobanco($data[0]);
        }
        return false;
    }
}
