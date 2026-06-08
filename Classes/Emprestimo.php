<?php

class EmprestimoLivro {
    private $tituloLivro;
    private $nomeLeitor;
    private $dataEmprestimo;
    private $dataDevolucao;

    public function __construct($tituloLivro, $nomeLeitor, $dataEmprestimo, $dataDevolucao) {
        $this->tituloLivro = $tituloLivro;
        $this->nomeLeitor = $nomeLeitor;
        $this->dataEmprestimo = $dataEmprestimo;
        $this->dataDevolucao = $dataDevolucao;
    }
}