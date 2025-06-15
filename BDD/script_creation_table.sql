CREATE TABLE Matiere(
   Id_Matiere INT AUTO_INCREMENT,
   nom VARCHAR(255),
   coefficient INT,
   PRIMARY KEY(Id_Matiere)
);

CREATE TABLE Document(
   Id_Document INT AUTO_INCREMENT,
   path VARCHAR(255),
   Id_Matiere INT NOT NULL,
   PRIMARY KEY(Id_Document),
   FOREIGN KEY(Id_Matiere) REFERENCES Matiere(Id_Matiere) ON DELETE CASCADE
);

CREATE TABLE Filière(
   Id_Filière INT AUTO_INCREMENT,
   Nom_filière VARCHAR(255),
   PRIMARY KEY(Id_Filière)
);

CREATE TABLE Role(
   Id_Role INT AUTO_INCREMENT,
   role VARCHAR(255),
   PRIMARY KEY(Id_Role)
);

CREATE TABLE User(
   Id_user INT AUTO_INCREMENT,
   numéro_étudiant VARCHAR(255),
   email VARCHAR(255),
   nom VARCHAR(255),
   prenom VARCHAR(255),
   photo_de_profil VARCHAR(255),
   mdp VARCHAR(250),
   Id_Role INT NOT NULL,
   Id_Filière INT,
   PRIMARY KEY(Id_user),
   FOREIGN KEY(Id_Role) REFERENCES Role(Id_Role) ON DELETE CASCADE,
   FOREIGN KEY(Id_Filière) REFERENCES Filière(Id_Filière) ON DELETE CASCADE
);

CREATE TABLE Cours(
   Id_Cours INT AUTO_INCREMENT,
   rdv DATETIME,
   duree INT,
   Id_Matiere INT NOT NULL,
   Id_user INT NOT NULL,
   PRIMARY KEY(Id_Cours),
   FOREIGN KEY(Id_Matiere) REFERENCES Matiere(Id_Matiere) ON DELETE CASCADE,
   FOREIGN KEY(Id_user) REFERENCES User(Id_user) ON DELETE CASCADE
);


CREATE TABLE Note(
   Id_Note INT AUTO_INCREMENT,
   Type VARCHAR(255),
   Note DOUBLE,
   Id_user INT NOT NULL,
   Id_Matiere INT NOT NULL,
   Id_user_1 INT NOT NULL,
   PRIMARY KEY(Id_Note),
   FOREIGN KEY(Id_user) REFERENCES User(Id_user) ON DELETE CASCADE, -- id_user est l'id de l'étudiant qui a la note
   FOREIGN KEY(Id_Matiere) REFERENCES Matiere(Id_Matiere) ON DELETE CASCADE,
   FOREIGN KEY(Id_user_1) REFERENCES User(Id_user) ON DELETE CASCADE -- id_user_1 est l'id du professeur qui a la note
);

CREATE TABLE Message(
   Id_Message INT AUTO_INCREMENT,
   Contenu VARCHAR(1000),
   created_at DATETIME,
   Id_user INT NOT NULL,
   Id_Matiere INT NOT NULL,
   PRIMARY KEY(Id_Message),
   FOREIGN KEY(Id_user) REFERENCES User(Id_user) ON DELETE CASCADE,
   FOREIGN KEY(Id_Matiere) REFERENCES Matiere(Id_Matiere) ON DELETE CASCADE
);

CREATE TABLE gerer(
   Id_user INT,
   Id_Matiere INT,
   PRIMARY KEY(Id_user, Id_Matiere),
   FOREIGN KEY(Id_user) REFERENCES User(Id_user) ON DELETE CASCADE,
   FOREIGN KEY(Id_Matiere) REFERENCES Matiere(Id_Matiere) ON DELETE CASCADE
);

CREATE TABLE Appartenir(
   Id_Matiere INT,
   Id_Filière INT,
   PRIMARY KEY(Id_Matiere, Id_Filière),
   FOREIGN KEY(Id_Matiere) REFERENCES Matiere(Id_Matiere) ON DELETE CASCADE,
   FOREIGN KEY(Id_Filière) REFERENCES Filière(Id_Filière) ON DELETE CASCADE
);
