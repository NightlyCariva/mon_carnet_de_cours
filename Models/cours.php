<?php
class Cours {
    private $pdo;
    private $rdv;

    public function __construct($pdo, $rdv) {
        $this->pdo = $pdo;
        $this->rdv = $rdv;
    }

    // Récupérer toutes les informations d'un cours par son ID
    public function getCoursById($id) {
        $stmt = $this->pdo->prepare('SELECT * FROM Cours WHERE Id_Cours = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Récupérer tous les cours d'une matière par ID matière
    public function getCoursByMatiere($idMatiere) {
        $stmt = $this->pdo->prepare('SELECT * FROM Cours WHERE Id_Matiere = ?');
        $stmt->execute([$idMatiere]);
        return $stmt->fetchAll();
    }

    // Ajouter un cours (en lui associant sa matière et le prof créateur)
    public function addCours($rdv, $idMatiere, $idUser) {
        $stmt = $this->pdo->prepare('INSERT INTO Cours (rdv, Id_Matiere, Id_user) VALUES (?, ?, ?)');
        return $stmt->execute([$rdv, $idMatiere, $idUser]);
    }

    // Supprimer un cours
    public function deleteCours($id) {
        $stmt = $this->pdo->prepare('DELETE FROM Cours WHERE Id_Cours = ?');
        return $stmt->execute([$id]);
    }

    // Modifier un cours (rdv et matière, pas le prof créateur)
    public function updateCours($id, $rdv, $idMatiere) {
        $stmt = $this->pdo->prepare('UPDATE Cours SET rdv = ?, Id_Matiere = ? WHERE Id_Cours = ?');
        return $stmt->execute([$rdv, $idMatiere, $id]);
    }


}