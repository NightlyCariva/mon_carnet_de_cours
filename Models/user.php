<?php
class User {
    private $pdo;
    private $numéro_étudiant;
    private $email;
    private $nom;
    private $prenom;
    private $photo_de_profil;
    private $mdp;

    public function __construct($pdo, $numéro_étudiant, $email, $nom, $prenom, $photo_de_profil, $mdp) {
        $this->pdo = $pdo;
        $this->numéro_étudiant = $numéro_étudiant;
        $this->email = $email;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->photo_de_profil = $photo_de_profil;
        $this->mdp = $mdp;
    }

    // Récupérer un utilisateur par son ID
    public function getUserById($id_user) {
        $stmt = $this->pdo->prepare("SELECT * FROM User WHERE Id_user = ?");
        $stmt->execute([$id_user]);
        return $stmt->fetch();
    }
    // Récupérer le role d'un utilisateur
    public function getRoleByUserId($id_user) {
        $stmt = $this->pdo->prepare("SELECT * FROM Role WHERE Id_Role = ?");
        $stmt->execute([$id_user]);
        return $stmt->fetch();
    }
    // Récupérer la filière d'un utilisateur
    public function getFiliereByUserId($id_user) {
        $stmt = $this->pdo->prepare("SELECT * FROM Filière WHERE Id_Filière = ?");
        $stmt->execute([$id_user]);
        return $stmt->fetch();
    }
    // Ajouter un étudiant (role étudiant à ajouter automatiquement)
    public function addEtudiant($numéro_étudiant, $email, $nom, $prenom, $photo_de_profil, $mdp, $id_filiere) {
        //recuperation de l'id du role étudiant
        $role = $this->getRoleByName('étudiant');
        $stmt = $this->pdo->prepare("INSERT INTO User (numéro_étudiant, email, nom, prenom, photo_de_profil, mdp, Id_Role, Id_Filière) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$numéro_étudiant, $email, $nom, $prenom, $photo_de_profil, $mdp, $role['Id_Role'], $id_filiere]);
    }
    // Ajouter un Professeur responsable (role Professeur responsable à ajouter automatiquement et filière à ajouter automatiquement)
    public function addProfesseurResponsable($email, $nom, $prenom, $photo_de_profil, $mdp) {
        //recuperation de l'id du role professeur responsable
        $role = $this->getRoleByName('Professeur responsable');
        //recuperation de l'id de la filière responsable
        $filiere = $this->getFiliereByName('Responsable');
        $stmt = $this->pdo->prepare("INSERT INTO User (email, nom, prenom, photo_de_profil, mdp, Id_Role, Id_Filière) VALUES (?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$email, $nom, $prenom, $photo_de_profil, $mdp, $role['Id_Role'], $filiere['Id_Filière']]);
    }

    // Modifier un Utilisateur
    public function updateUser($id_user, $email, $nom, $prenom, $photo_de_profil, $mdp, $id_filiere) {
        $stmt = $this->pdo->prepare("UPDATE User SET email = ?, nom = ?, prenom = ?, photo_de_profil = ?, mdp = ?, Id_Filière = ? WHERE Id_user = ?");
        return $stmt->execute([$email, $nom, $prenom, $photo_de_profil, $mdp, $id_filiere, $id_user]);
    }

    // Calcul de la moyenne générale pondérée de l'étudiant
    public function getMoyenneGenerale($id_etudiant) {
        $stmt = $this->pdo->prepare("SELECT n.Note, m.coefficient FROM Note n JOIN Matiere m ON n.Id_Matiere = m.Id_Matiere WHERE n.Id_user = ?");
        $stmt->execute([$id_etudiant]);
        $notes = $stmt->fetchAll();
        $somme = 0;
        $total_coeff = 0;
        foreach ($notes as $row) {
            $somme += $row['Note'] * $row['coefficient'];
            $total_coeff += $row['coefficient'];
        }
        if ($total_coeff == 0) return null;
        return $somme / $total_coeff;
    }

    // Calcul de la moyenne simple de l'étudiant pour une matière donnée
    public function getMoyenneByMatiere($id_etudiant, $id_matiere) {
        $stmt = $this->pdo->prepare("SELECT Note FROM Note WHERE Id_user = ? AND Id_Matiere = ?");
        $stmt->execute([$id_etudiant, $id_matiere]);
        $notes = $stmt->fetchAll();
        if (count($notes) == 0) return null;
        $somme = 0;
        foreach ($notes as $row) {
            $somme += $row['Note'];
        }
        return $somme / count($notes);
    }

    
}