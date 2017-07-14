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
 * Description of tiponomina
 *
 * @author Joe Nilson <joenilson at gmail.com>
 */
class tiponomina extends \fs_model {
    /**
     * Id del tipo de nomina
     * @var integer
     */
    public $id;
    /**
     * Descripción del tipo de nomina
     * @var varchar(100)
     */
    public $descripcion;
    /**
     * Cada cuantos días se calcula la nómina
     * @var integer
     */
    public $dias;
    /**
     * Frecuencia de calculo de nómina diario | semanal | quincenal | mensual
     * @var varchar(32)
     */
    public $frecuencia;
    /**
     * Activo o inactivo el tipo de nomina
     * @var boolean
     */
    public $estado;
    public function __construct($t = '') {
        parent::__construct('hr_tiponomina');
        if($t){
            $this->id = $t['id'];
            $this->descripcion = $t['descripcion'];
            $this->dias = $t['dias'];
            $this->frecuencia = $t['frecuencia'];
            $this->estado = $this->str2bool($t['estado']);
        }else{
            $this->id = null;
            $this->descripcion = null;
            $this->dias = null;
            $this->frecuencia = null;
            $this->estado = false;
        }
    }
    
    protected function install() {
        return "INSERT INTO ".$this->table_name. " (descripcion, dias, frecuencia, estado ) VALUES ".
                "('Diaria',1,'diaria',TRUE),".
                "('Semanal',7,'semanal',TRUE),".
                "('Quincenal',15,'quincenal',TRUE),".
                "('Mensual',30,'mensual',TRUE);";
    }
    
    public function exists() {
        ;
    }
    
    public function save() {
        ;
    }
    
    public function delete() {
        ;
    }
}
