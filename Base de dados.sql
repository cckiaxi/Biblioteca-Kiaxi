create database biblioteca;
use biblioteca;

create table autor(
id_autor int auto_increment primary key,
nome varchar(150) not null,
nacionalidade varchar(50),
data_nascimento date,
biografia TEXT
);
create table editora(
id_editora int primary key ,
nome varchar(100) not null unique,
telefone varchar(20),
email varchar(100)
);
create table categoria(
id_categoria int auto_increment primary key,
nome varchar(50) not null unique
);
create table livros(
id_livro int auto_increment primary key,
titulo varchar (20) not null,
id_editora int,
ano_publicacao year,
qtd_total int not null default 1,
id_categoria int
);

create table usuario(
id_usuario int auto_increment primary key,
nome varchar(50) not null,
email varchar(50) not null,
data_registro date not null
);

create table emprestimo(
id_emprestimo int primary key auto_increment,
data_emprestimo date not null,
data_devolucao date not null
);

