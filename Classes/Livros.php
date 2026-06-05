<?php

class Livro
{
    private int $id;
    private string $titulo;
    private string $autor;
    private string $isbn;
    private int $anoPublicacao;
    private bool $disponivel;

    public function __construct(int $id,string $titulo,string $autor,string $isbn,int $anoPublicacao) {
        $this->id = $id;
        $this->titulo = $titulo;
        $this->autor = $autor;
        $this->isbn = $isbn;
        $this->anoPublicacao = $anoPublicacao;
        $this->disponivel = true;
    }

    public function estaDisponivel(): bool
    {
        return $this->disponivel;
    }

    public function emprestar(): bool
    {
        if ($this->disponivel) {
            $this->disponivel = false;
            return true;
        }

        return false;
    }

    public function devolver(): void
    {
        $this->disponivel = true;
    }
}