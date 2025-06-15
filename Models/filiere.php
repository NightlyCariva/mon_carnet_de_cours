<?php
class Filiere {
    private $pdo;
    private $nom_filière;

    public function __construct($pdo, $nom_filière) {
        $this->pdo = $pdo;
        $this->nom_filière = $nom_filière;
    }
    
    // Récupérer toutes les informations d'une filière par son ID
    public function getFiliereById($id) {
        $stmt = $this->pdo->prepare('SELECT * FROM Filière WHERE Id_Filière = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Récupérer tous les étudiants d'une filière
    public function getEtudiantsByFiliere($idFiliere) {
        $stmt = $this->pdo->prepare('SELECT * FROM User WHERE Id_Filière = ?');
        $stmt->execute([$idFiliere]);
        return $stmt->fetchAll();
    }

    // Ajouter une filière
    public function addFiliere($nomFiliere) {
        $stmt = $this->pdo->prepare('INSERT INTO Filière (Nom_filière) VALUES (?)');
        return $stmt->execute([$nomFiliere]);
    }

    // Supprimer une filière
    public function deleteFiliere($id) {
        $stmt = $this->pdo->prepare('DELETE FROM Filière WHERE Id_Filière = ?');
        return $stmt->execute([$id]);
    }

    // Modifier une filière
    public function updateFiliere($id, $nouveauNom) {
        $stmt = $this->pdo->prepare('UPDATE Filière SET Nom_filière = ? WHERE Id_Filière = ?');
        return $stmt->execute([$nouveauNom, $id]);
    }

    // Ajouter une matière à une filière
    public function addMatiereToFiliere($idMatiere, $idFiliere) {
        $stmt = $this->pdo->prepare('INSERT INTO Appartenir (Id_Matiere, Id_Filière) VALUES (?, ?)');
        return $stmt->execute([$idMatiere, $idFiliere]);
    }

    // Supprimer une matière d'une filière
    public function removeMatiereFromFiliere($idMatiere, $idFiliere) {
        $stmt = $this->pdo->prepare('DELETE FROM Appartenir WHERE Id_Matiere = ? AND Id_Filière = ?');
        return $stmt->execute([$idMatiere, $idFiliere]);
    }

    //Initialiser une filière RESPONSABLE pour les Professeur responsable
    public function initFiliereResponsable() {
        $stmt = $this->pdo->prepare('INSERT INTO Filière (Nom_filière) VALUES (?)');
        return $stmt->execute(['Responsable']);
    }
}