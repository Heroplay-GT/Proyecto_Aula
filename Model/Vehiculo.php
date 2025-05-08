<?php
class Vehiculo {
    public $placa;
    public $tipo;

    public function __construct($placa, $tipo) {
        $this->placa = $placa;
        $this->tipo = $tipo;
    }

    public function esValido() {
        return !empty($this->placa) && !empty($this->tipo);
    }
}
