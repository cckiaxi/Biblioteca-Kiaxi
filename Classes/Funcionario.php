<?php

class Funcionario {
    private $nome;
    private $cargo;
    private $codigo;

    public function __construct($nome, $cargo, $codigo) {
        $this->nome = $nome;
        $this->cargo = $cargo;
        $this->codigo = $codigo;
    }

    public function exibirDados() {
       
    }
}