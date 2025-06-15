<?php
class Document {
    private $pdo;
    private $path;

    public function __construct($pdo, $path) {
        $this->pdo = $pdo;
        $this->path = $path;
    }

    // Récupérer toutes les informations d'un document par son ID
    public function getDocumentById($id) {
        $stmt = $this->pdo->prepare('SELECT * FROM Document WHERE Id_Document = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Récupérer tous les documents d'une matière par ID matière
    public function getDocumentsByMatiere($idMatiere) {
        $stmt = $this->pdo->prepare('SELECT * FROM Document WHERE Id_Matiere = ?');
        $stmt->execute([$idMatiere]);
        return $stmt->fetchAll();
    }

    // Ajouter un document (en lui associant sa matière)
    public function addDocument($path, $idMatiere) {
        $stmt = $this->pdo->prepare('INSERT INTO Document (path, Id_Matiere) VALUES (?, ?)');
        return $stmt->execute([$path, $idMatiere]);
    }

    // Supprimer un document
    public function deleteDocument($id) {
        $stmt = $this->pdo->prepare('DELETE FROM Document WHERE Id_Document = ?');
        return $stmt->execute([$id]);
    }

    // Modifier le chemin d'un document (et éventuellement la matière)
    public function updateDocument($id, $path, $idMatiere) {
        $stmt = $this->pdo->prepare('UPDATE Document SET path = ?, Id_Matiere = ? WHERE Id_Document = ?');
        return $stmt->execute([$path, $idMatiere, $id]);
    }
}