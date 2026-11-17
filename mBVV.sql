use mBVV;

/* create table pokemon( TABELA POKÉMON ATUALIZADA
idPokemon int auto_increment primary key,
nome varchar(50) not null unique,
tipos varchar(50) not null,
natureza varchar(30) not null,
hp int not null CHECK (hp >= 0 AND hp <= 255),
atk int not null CHECK (atk >= 0 AND atk <= 255),
def int not null CHECK (def >= 0 AND def <= 255),
spA int not null CHECK (speAtk  >= 0 AND speAtk <= 255),
spD int not null CHECK (speDef >= 0 AND speDef <= 255),
spe int not null CHECK (speed >= 0 AND speDef <= 255),
img varchar(150) not null
); */

DROP TABLE IF EXISTS log;
DROP TABLE IF EXISTS batalha;
DROP TABLE IF EXISTS times;
DROP TABLE IF EXISTS inventario;
DROP TABLE IF EXISTS slot;
DROP TABLE IF EXISTS ataque;
DROP TABLE IF EXISTS item;
DROP TABLE IF EXISTS usuario;

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
senha varchar(255) not null unique,
wins int DEFAULT 0,
loss int DEFAULT 0,
/* elo int DEFAULT 1000 CHECK (elo >= 1000), acho melhor retirar, vou deixar comentado por enquanto*/
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

create table slot( /*talvez isso vá virar a instancia de pokémon só colocando uma variável pra status*/
idSlot int auto_increment primary key,
nome varchar(30),
apelido varchar(30),
/*
hp int not null CHECK (hp >= 0 AND hp <= 255),
atk int not null CHECK (atk >= 0 AND atk <= 255),
def int not null CHECK (def >= 0 AND def <= 255),
spA int not null CHECK (spA  >= 0 AND spA <= 255),
spD int not null CHECK (spD >= 0 AND spD <= 255),
spe int not null CHECK (spe >= 0 AND spe <= 255),
*/
evHp int not null CHECK (evHp >= 0 AND evHp <= 252),
evAtk int not null CHECK (evAtk >= 0 AND evAtk <= 252),
evDef int not null CHECK (evDef >= 0 AND evDef <= 252),
evSpA int not null CHECK (evSpA >= 0 AND evSpA <= 252),
evSpD int not null CHECK (evSpD >= 0 AND evSpD <= 252),
evSpe int not null CHECK (evSpe >= 0 AND evSpe <= 252),
ivHp int not null CHECK (ivHp >= 0 AND ivHp <= 31),
ivAtk int not null CHECK (ivAtk >= 0 AND ivAtk <= 31),
ivDef int not null CHECK (ivDef >= 0 AND ivDef <= 31),
ivSpA int not null CHECK (ivSpA >= 0 AND ivSpA <= 31),
ivSpD int not null CHECK (ivSpD >= 0 AND ivSpD <= 31),
ivSpe int not null CHECK (ivSpe >= 0 AND ivSpe <= 31),
genero varchar(1),
shiny boolean,
nat varchar(30) NOT NULL,
item varchar(40),
ability varchar(30) NOT NULL,
move1 varchar(30) NOT NULL,
move2 varchar(30) NOT null,
move3 varchar(30) NOT null,
move4 varchar(30) NOT null,
img varchar(150) not null
);

create table times(
idTimes int auto_increment primary key,
nome varchar(20),
/* talvez a gente tenha que modificar isso e colocar o time primeiro,
daí o pokémon só é instaciado depois de ser adicionado no time, pq se não não faz
sentido eu acho */
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
foreign key (idSlot6) references slot(idSlot)
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