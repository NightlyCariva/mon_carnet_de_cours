<?php
class Matiere {
    private $pdo;
    private $nom;
    private $coefficient;

    public function __construct($pdo, $nom, $coefficient) {
        $this->pdo = $pdo;
        $this->nom = $nom;
        $this->coefficient = $coefficient;
    }

    // Récupérer toutes les informations d'une matière par son ID
    public function getMatiereById($id) {
        $stmt = $this->pdo->prepare('SELECT * FROM Matiere WHERE Id_Matiere = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Récupérer tous les étudiants suivant une matière
    public function getEtudiantsByMatiere($idMatiere) {
        $stmt = $this->pdo->prepare('SELECT u.* FROM User u JOIN Appartenir a ON u.Id_Filière = a.Id_Filière WHERE a.Id_Matiere = ?');
        $stmt->execute([$idMatiere]);
        return $stmt->fetchAll();
    }

    // Récupérer le responsable de la matière
    public function getResponsableByMatiere($idMatiere) {
        $stmt = $this->pdo->prepare('SELECT u.* FROM User u JOIN gerer g ON u.Id_user = g.Id_user WHERE g.Id_Matiere = ?');
        $stmt->execute([$idMatiere]);
        return $stmt->fetch();
    }

    // Ajouter une matière
    public function addMatiere($nom, $coefficient) {
        $stmt = $this->pdo->prepare('INSERT INTO Matiere (nom, coefficient) VALUES (?, ?)');
        return $stmt->execute([$nom, $coefficient]);
    }

    // Supprimer une matière (delete on cascade)
    public function deleteMatiere($id) {
        $stmt = $this->pdo->prepare('DELETE FROM Matiere WHERE Id_Matiere = ?');
        return $stmt->execute([$id]);
    }

    // Modifier une matière
    public function updateMatiere($id, $nom, $coefficient) {
        $stmt = $this->pdo->prepare('UPDATE Matiere SET nom = ?, coefficient = ? WHERE Id_Matiere = ?');
        return $stmt->execute([$nom, $coefficient, $id]);
    }

    // Récupérer toutes les matières d'une filière
    public function getMatieresByFiliere($idFiliere) {
        $stmt = $this->pdo->prepare('SELECT * FROM Matiere WHERE Id_Filière = ?');
        $stmt->execute([$idFiliere]);
        return $stmt->fetchAll();
    }


    // Récupérer tous les documents d'une matière
    public function getDocumentsByMatiere($idMatiere) {
        $stmt = $this->pdo->prepare('SELECT * FROM Document WHERE Id_Matiere = ?');
        $stmt->execute([$idMatiere]);
        return $stmt->fetchAll();
    }

    // Récupérer tous les cours d'une matière
    public function getCoursByMatiere($idMatiere) {
        $stmt = $this->pdo->prepare('SELECT * FROM Cours WHERE Id_Matiere = ?');
        $stmt->execute([$idMatiere]);
        return $stmt->fetchAll();
    }
}