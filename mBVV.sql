use mBVV;

DROP TABLE IF EXISTS pokemon;
DROP TABLE IF EXISTS log;
DROP TABLE IF EXISTS batalha;
DROP TABLE IF EXISTS times;
DROP TABLE IF EXISTS inventario;
DROP TABLE IF EXISTS slot;
DROP TABLE IF EXISTS ataque;
DROP TABLE IF EXISTS item;
DROP TABLE IF EXISTS usuario;

create table pokemon(
idPokemon int auto_increment primary key,
nome varchar(50) not null,
tipo1 varchar(30) not null,
tipo2 varchar(30),
hp int not null CHECK (hp >= 0 AND hp <= 255),
atk int not null CHECK (atk >= 0 AND atk <= 255),
def int not null CHECK (def >= 0 AND def <= 255),
spA int not null CHECK (spA  >= 0 AND spA <= 255),
spD int not null CHECK (spD >= 0 AND spD <= 255),
spe int not null CHECK (spe >= 0 AND spe <= 255),
img varchar(150) not null
);

CREATE TABLE ataque(
idAtaque int auto_increment primary key,
nome varchar(50) NOT  null,
tipo varchar(30) NOT null,
fisSpe boolean, /*0=físico e 1=especial, o que acham?*/
poder int not null,
prioridade int default 0 check (prioridade >= -7 and prioridade <= 5),
pp int check (pp >= 5 and pp <= 40)
);

create table usuario(
idUsuario int auto_increment primary key,
nome varchar(30) not null unique,
senha varchar(255) not null,
wins int DEFAULT 0,
loss int DEFAULT 0,
dinheiro int CHECK (dinheiro >= 0)
);

create table item(
idItem int auto_increment primary key,
nome varchar(40),
descricao varchar(150),
preco int CHECK (preco >= 0)
);

create table inventario (
idInventario int auto_increment primary key,
idUsuario int not null,
idItem int not null,
quantidade int default 0 CHECK (quantidade >= 0),
foreign key (idUsuario) references usuario(idUsuario),
foreign key (idItem) references item(idItem)
);

create table slot(
idSlot int auto_increment primary key,
idPokemon int,
idUsuario int,
nome varchar(50) not null,
tipo1 varchar(30) not null,
tipo2 varchar(30),
status varchar(30),
hp int not null CHECK (hp >= 0 AND hp <= 255),
atk int not null CHECK (atk >= 0 AND atk <= 255),
def int not null CHECK (def >= 0 AND def <= 255),
spA int not null CHECK (spA  >= 0 AND spA <= 255),
spD int not null CHECK (spD >= 0 AND spD <= 255),
spe int not null CHECK (spe >= 0 AND spe <= 255),
ataque1 varchar(50) not null,
ataque2 varchar(50) not null,
img varchar(150) not null,
foreign key (idUsuario) references usuario(idUsuario)
);

create table times(
idTimes int auto_increment primary key,
idUsuario int,
nome varchar(20),
idSlot1 int, 
idSlot2 int,
idSlot3 int,
idSlot4 int,
idSlot5 int,
idSlot6 INt,
foreign key (idSlot1) references slot(idSlot),
foreign key (idSlot2) references slot(idSlot),
foreign key (idSlot3) references slot(idSlot),
foreign key (idSlot4) references slot(idSlot),
foreign key (idSlot5) references slot(idSlot),
foreign key (idSlot6) references slot(idSlot),
foreign key (idUsuario) references usuario(idUsuario)
);

CREATE TABLE batalha(
idBatalha int auto_increment primary key,
idUser1 int,
idUser2 int,
idTimes1 int,
idTimes2 int,
foreign key (idUser1) references usuario(idUsuario),
foreign key (idUser2) references usuario(idUsuario),
foreign key (idTimes1) references times(idTimes),
foreign key (idTimes2) references times(idTimes)
);

create table log(
idLog int auto_increment primary key,
idBatalha int,
idUser1 int,
idUser2 int,
idTimes1 int,
idTimes2 int,
arquivo varchar(50),
foreign key (idBatalha) references batalha(idBatalha),
foreign key (idUser1) references usuario(idUsuario),
foreign key (idUser2) references usuario(idUsuario),
foreign key (idTimes1) references times(idTimes),
foreign key (idTimes2) references times(idTimes)
);

insert into item (nome, descricao, preco) values
('Potion', 'Restaura 20 HP de um Pokémon.', 300),
('Super Potion', 'Restaura 50 HP de um Pokémon.', 700),
('Hyper Potion', 'Restaura 200 HP de um Pokémon.', 1200),
('Max Potion', 'Restaura todos os HP de um Pokémon.', 2500),
('Revive', 'Revive um Pokémon desmaiado com metade do HP máximo.', 1500),
('Full Restore', 'Restaura todos os HP e cura qualquer condição de status.', 3000),
('Antidote', 'Cura envenenamento de um Pokémon.', 100),
('Paralyze Heal', 'Cura paralisia de um Pokémon.', 100),
('Burn Heal', 'Cura queimadura de um Pokémon.', 100),
('Ice Heal', 'Cura congelamento de um Pokémon.', 100),
('Awakening', 'Desperta um Pokémon adormecido.', 100),
('Full Heal', 'Cura qualquer condição de status de um Pokémon.', 500),
('Pedra de evolução', 'Usada para evoluir qualquer Pokémon.', 5000);