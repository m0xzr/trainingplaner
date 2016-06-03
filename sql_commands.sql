/* ------------------------------ Table creation ------------------------------ */

CREATE TABLE trainingplaner.UserAccount
(
ID INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
Username VARCHAR(255) NOT NULL,
PASSWORD VARCHAR(255) NOT NULL,
PRIMARY KEY (ID)
)

CREATE TABLE trainingplaner.UserData
(
ID INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
Weight float unsigned NOT NULL,
HrRest int(10) unsigned NOT NULL,
HrMax int(10) unsigned NOT NULL,
CreationDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
UserID INT(10) UNSIGNED NOT NULL,
PRIMARY KEY (ID),
CONSTRAINT fk_User FOREIGN KEY (UserID)
REFERENCES trainingplaner.UserAccount(id)
)

CREATE TABLE trainingplaner.Sport
(
ID INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
Name VARCHAR(255) NOT NULL,
PRIMARY KEY (ID)
)

CREATE TABLE trainingplaner.SportType
(
ID INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
Name VARCHAR(255) NOT NULL,
SportID INT(10) UNSIGNED NOT NULL,
PRIMARY KEY (ID),
CONSTRAINT fk_SportTP FOREIGN KEY (SportID)
REFERENCES trainingplaner.Sport(id)
)

CREATE TABLE trainingplaner.Training
(
ID int(10) unsigned NOT NULL AUTO_INCREMENT,
Week int(10) unsigned NOT NULL,
Day int NOT NULL,
Sport int(10) unsigned,
Type int(10) unsigned,
DurationHours int(10) unsigned DEFAULT 0,
DurationMinutes int(10) unsigned DEFAULT,
PlanedDone bool NOT NULL DEFAULT FALSE,
AvgHR int(5) unsigned Default 0,
Annotation varchar(255),
PRIMARY KEY (ID),
CONSTRAINT fk_Week FOREIGN KEY (Week)
REFERENCES trainingplaner.Week(id),
CONSTRAINT fk_Sport FOREIGN KEY (Sport)
REFERENCES trainingplaner.Sport(id),
CONSTRAINT fk_Type FOREIGN KEY (Type)
REFERENCES trainingplaner.SportType(id)
)

CREATE TABLE trainingplaner.Week
(
ID int(10) unsigned NOT NULL AUTO_INCREMENT,
Plan int(10) unsigned NOT NULL,
WeekNumber int(10) unsigned NOT NULL,
Annotation varchar(255),
PRIMARY KEY (ID),
CONSTRAINT fk_Plan FOREIGN KEY (Plan)
REFERENCES trainingplaner.Plan(id)
)

CREATE TABLE trainingplaner.Plan
(
ID int(10) unsigned NOT NULL AUTO_INCREMENT,
Title varchar(255) NOT NULL UNIQUE,
Active bool NOT NULL DEFAULT FALSE,
CreationDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY (ID)
)

CREATE TABLE trainingplaner.Thoughts
(
Training int(10) unsigned NOT NULL,
Thought MEDIUMTEXT,
PRIMARY KEY (Training),
CONSTRAINT fk_Training FOREIGN KEY (Training)
REFERENCES trainingplaner.Training(id)
)




/* ------------------------------ Inserts ------------------------------ */

insert into trainingplaner.UserData (weight, hrrest, hrmax, userid) values (77.8, 43, 192, 1);

insert into trainingplaner.Sport (Name) Values ('Laufen');
insert into trainingplaner.Sport (Name) Values ('Indoor Training');
insert into trainingplaner.Sport (Name) Values ('Radfahren');
insert into trainingplaner.Sport (Name) Values ('Skitouring');
insert into trainingplaner.Sport (Name) Values ('Sonstige');

update trainingplaner.Training set sport = 1 where sport = 6;
update trainingplaner.Training set sport = 2 where sport = 11;
update trainingplaner.Training set sport = 3 where sport = 8;
update trainingplaner.Training set sport = 4 where sport = 12;
update trainingplaner.Training set sport = 5 where sport = 15;


alter table trainingplaner.Training add CONSTRAINT fk_Sport FOREIGN KEY (Sport) REFERENCES trainingplaner.Sport(id)
alter table trainingplaner.Training drop FOREIGN KEY fk_Type


insert into trainingplaner.SportType (Name, SportID) Values ('Dauerlauf', 1);
insert into trainingplaner.SportType (Name, SportID) Values ('Fahrtspiel', 1);
insert into trainingplaner.SportType (Name, SportID) Values ('Intervalltraining', 1);
insert into trainingplaner.SportType (Name, SportID) Values ('Tempodauerlauf', 1);
insert into trainingplaner.SportType (Name, SportID) Values ('Wettkampf', 1);
insert into trainingplaner.SportType (Name, SportID) Values ('Regenerationslauf', 1);
insert into trainingplaner.SportType (Name, SportID) Values ('Langer Lauf', 1);
insert into trainingplaner.SportType (Name, SportID) Values ('Warm-/Auslaufen', 1);
insert into trainingplaner.SportType (Name, SportID) Values ('Kraft', 2);
insert into trainingplaner.SportType (Name, SportID) Values ('Rollentraining', 2);
insert into trainingplaner.SportType (Name, SportID) Values ('Piste', 4);
insert into trainingplaner.SportType (Name, SportID) Values ('Gelände', 4);
insert into trainingplaner.SportType (Name, SportID) Values ('Schwimmen', 5);
insert into trainingplaner.SportType (Name, SportID) Values ('Skaten', 5);
insert into trainingplaner.SportType (Name, SportID) Values ('MultiSport', 5);
insert into trainingplaner.SportType (Name, SportID) Values ('Poolswimming', 5);
insert into trainingplaner.SportType (Name, SportID) Values ('Mountainbike', 3);
insert into trainingplaner.SportType (Name, SportID) Values ('Rennrad', 3);



update trainingplaner.Training set type = 1 where type = 9;
update trainingplaner.Training set type = 2 where type = 10;
update trainingplaner.Training set type = 3 where type = 11;
update trainingplaner.Training set type = 4 where type = 12;
update trainingplaner.Training set type = 5 where type = 13;
update trainingplaner.Training set type = 6 where type = 14;
update trainingplaner.Training set type = 7 where type = 15;
update trainingplaner.Training set type = 8 where type = 16;
update trainingplaner.Training set type = 9 where type = 17;
update trainingplaner.Training set type = 10 where type = 18;
update trainingplaner.Training set type = 11 where type = 19;
update trainingplaner.Training set type = 12 where type = 20;
update trainingplaner.Training set type = 13 where type = 21;
update trainingplaner.Training set type = 14 where type = 22;
update trainingplaner.Training set type = 15 where type = 23;
update trainingplaner.Training set type = 16 where type = 24;
update trainingplaner.Training set type = 17 where type = 25;
update trainingplaner.Training set type = 18 where type = 26;


alter table trainingplaner.Training add CONSTRAINT fk_Type FOREIGN KEY (Type) REFERENCES trainingplaner.SportType(id)