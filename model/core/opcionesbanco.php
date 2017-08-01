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
 * Description of opcionesbanco
 *
 * @author Joe Nilson <joenilson at gmail.com>
 */
class opcionesbanco extends \fs_model {
    /**
     * Id de la empresa
     * @var integer
     */
    public $idempresa;
    /**
     * Código del banco
     * @var varchar(6)
     */
    public $codbanco;
    /**
     * Codigo que le da el banco a la empresa
     * @var varchar(10)
     */
    public $codempresa;
    /**
     * Email de contacto para el archivo
     * @var varchar(80)
     */
    public $email_contacto;
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
        parent::__construct('hr_opcionesbanco');
        if($t){
            $this->idempresa = $t['idempresa'];
            $this->codbanco = $t['codbanco'];
            $this->codempresa = $t['codempresa'];
            $this->email_contacto = $t['email_contacto'];
            $this->fecha_creacion = \date('d-m-Y', strtotime($t['fecha_creacion']));
            $this->usuario_creacion = $t['usuario_creacion'];
            $this->fecha_modificacion = \date('d-m-Y', strtotime($t['fecha_modificacion']));
            $this->usuario_modificacion = $t['usuario_modificacion'];
        }else{
            $this->idempresa = null;
            $this->codbanco = null;
            $this->codempresa = null;
            $this->email_contacto = null;
            $this->fecha_creacion = null;
            $this->usuario_creacion = null;
            $this->fecha_modificacion = null;
            $this->usuario_modificacion = null;            
        }
    }
    
    protected function install() {
        return "";
    }
    
    public function all($idempresa)
    {
        $lista = array();
        $sql = "SELECT * FROM ".$this->table_name. " WHERE idempresa = ".$this->intval($idempresa)." ORDER BY codbanco;";
        $data = $this->db->select($sql);
        if($data){
            foreach($data as $d){
                $item = new opcionesbanco($d);
                $lista[] = $item;
            }
        }
        return $lista;
    }
    
    public function get($idempresa, $codbanco) {
        $sql = "SELECT * FROM ".$this->table_name. " WHERE idempresa = ".$this->intval($idempresa)." AND codbanco = ".$this->var2str($codbanco).";";
        $data = $this->db->select($sql);
        if($data){
            return new opcionesbanco($data[0]);
        }
        return false;
    }
    
    public function exists() {
        return $this->get($this->idempresa,$this->codbanco);
    }
    
    public function save() {
        if($this->exists()){
            $sql = "UPDATE ".$this->table_name." SET ".
            "codempresa = ".$this->var2str($this->codempresa).
            ", email_contacto = ".$this->var2str($this->email_contacto).
            ", fecha_modificacion = ".$this->var2str($this->fecha_modificacion).
            ", usuario_modificacion = ".$this->var2str($this->usuario_modificacion).
            " WHERE idempresa = ".$this->intval($this->idempresa)." AND codbanco = ".$this->var2str($this->codbanco).";";
            return $this->db->exec($sql);
        }else{
            $sql = "INSERT INTO ".$this->table_name." (idempresa, codbanco, codempresa, email_contacto, fecha_creacion, usuario_creacion) VALUES (".
            $this->intval($this->idempresa).",".
            $this->var2str($this->codbanco).",".
            $this->var2str($this->codempresa).",".
            $this->var2str($this->email_contacto).",".
            $this->var2str($this->fecha_creacion).",".
            $this->var2str($this->usuario_creacion).");";
            return $this->db->exec($sql);
        }
    }
    
    public function delete() {
        $sql = "DELETE FROM ".$this->table_name." WHERE idempresa = ".$this->intval($this->idempresa)." AND codbanco = ".$this->var2str($this->codbanco).";";
        return $this->db->exec($sql);
    }
}
