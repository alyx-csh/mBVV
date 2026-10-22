use mBVV;

/*tabela para: pokemon(apiID,nome, tipo, hp, speed, atk, etc, img, habilidade), 
 * usuario(nome, senha,ID, pontos), 
 * item(id, nome,desc, preco), 
 * time(timeID, 6x idslot),
 * log(IDlog, idBatalha, Idtime1, Idtime2, Iduser1, Iduser2,texto)
 * slot(timeid, slotID, apiID, nome apelido, speed, atk, def, etc, img, EV, IV, genero, shiny, nivel, natureza, item, habilidade, move1, move2, move3, move4)
 * */
 
create table pokemon( /*Dados dados pela API, stats são os base*/
idPokemon int auto_increment primary key,
idApi int not null unique,
nome varchar(50) not null unique,
tipo1 varchar(20) not null,
tipo2 varchar(20),
hp int not null CHECK (hp >= 0 AND hp <= 255),
atk int not null CHECK (atk >= 0 AND atk <= 255),
def int not null CHECK (def >= 0 AND def <= 255),
spA int not null CHECK (speAtk  >= 0 AND speAtk <= 255),
spD int not null CHECK (speDef >= 0 AND speDef <= 255),
spe int not null CHECK (speed >= 0 AND speDef <= 255),
bst int not null unique CHECK (bst >= 0 and  bst <= 1530),
img varchar(150) not null,
habilidade varchar(40) not null
);

CREATE TABLE ataque(
idAtaque int auto_increment primary key,
nome varchar(50) NOT  null,
tipo varchar(30) NOT null,
fisSpe boolean, /*0=físico e 1=especial, o que acham?*/
poder int CHECK (poder >= 0 and poder <= 250) NOT null,
prioridade int DEFAULT 0 check (prioridade >= -7 and prioridade <= 7),
pp int check (pp >= 1 and pp <= 40)
);

create table usuario(
idUsuario int auto_increment primary key,
nome varchar(30) not null unique,
senha varchar(35) not null unique,
wins int DEFAULT 0,
loss int DEFAULT 0,
elo int DEFAULT 1000 CHECK (elo >= 1000),
pontos int CHECK (pontos >= 0),
dinheiro int CHECK (dinheiro >= 0)
);

create table item(
idItem int auto_increment primary key,
nome varchar(40),
descricao varchar(150),
preco float
);

CREATE TABLE batalha(
idBatalha int auto_increment primary key,
idUser1 int foreign KEY REFERENCES usuario(idUsuario),
idUser2 int foreign KEY REFERENCES usuario(idUsuario),
idTimes1 int foreign KEY REFERENCES times(idTimes),
idTimes2 foreign KEY REFERENCES times(idTimes)
);

create table log(
idLog int auto_increment primary key,
idBatalha int FOREIGN KEY REFERENCES batalha(idBatalha),
idUser1 int foreign KEY REFERENCES usuario(idUsuario),
idUser2 int foreign KEY REFERENCES usuario(idUsuario),
idTimes1 int foreign KEY REFERENCES times(idTimes),
idTimes2 foreign KEY REFERENCES times(idTimes),
arquivo varchar(50)
);

create table slot(
idSlot int auto_increment primary key,
idTimes int FOREIGN KEY REFERENCES times(idTime),
idApi int not null unique,
nome varchar(30),
apelido varchar(30),
hp int not null CHECK (hp >= 0 AND hp <= 255),
atk int not null CHECK (atk >= 0 AND atk <= 255),
def int not null CHECK (def >= 0 AND def <= 255),
spA int not null CHECK (spA  >= 0 AND spA <= 255),
spD int not null CHECK (spD >= 0 AND spD <= 255),
spe int not null CHECK (spe >= 0 AND spe <= 255),
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
nivel int NOT NULL CHECK (nivel >=1 AND nivel <= 100),
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
idSlot1 int FOREIGN KEY REFERENCES slot(idSlot),
idSlot2 int FOREIGN KEY REFERENCES slot(idSlot),
idSlot3 int FOREIGN KEY REFERENCES slot(idSlot),
idSlot4 int FOREIGN KEY REFERENCES slot(idSlot),
idSlot5 int FOREIGN KEY REFERENCES slot(idSlot),
idSlot6 INt FOREIGN KEY REFERENCES slot(idSlot)
);