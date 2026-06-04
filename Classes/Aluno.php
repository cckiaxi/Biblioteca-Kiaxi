<?php

class Aluno
{
    private int $id;
    private string $nome;
    private string $email;
    private string $curso;
    private array $livrosEmprestados = [];

    public function __construct(int $id,string $nome,string $email,string $curso) {
        $this->id = $id;
        $this->nome = $nome;
        $this->email = $email;
        $this->curso = $curso;
    }

    public function emprestarLivro(string $livro): void
    {
        $this->livrosEmprestados[] = $livro;
    }

    public function devolverLivro(string $livro): void
    {
        $chave = array_search($livro, $this->livrosEmprestados);

        if ($chave !== false) {
            unset($this->livrosEmprestados[$chave]);
        }
    }

    public function getLivrosEmprestados(): array
    {
        return $this->livrosEmprestados;
    }
}