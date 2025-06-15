<?php
class Message {
    private $pdo;
    private $contenu;
    private $created_at;

    public function __construct($pdo, $contenu, $created_at) {
        $this->pdo = $pdo;
        $this->contenu = $contenu;
        $this->created_at = $created_at;
    }

    // Créer un message
    public function createMessage($id_user, $id_matiere) {
        $stmt = $this->pdo->prepare("INSERT INTO Message (Contenu, Id_user, Id_Matiere) VALUES (?, ?, ?)");
        return $stmt->execute([$this->contenu, $id_user, $id_matiere]);
    }

    // Récupérer un message par son ID
    public function getMessageById($id_message) {
        $stmt = $this->pdo->prepare("SELECT * FROM Message WHERE Id_Message = ?");
        $stmt->execute([$id_message]);
        return $stmt->fetch();
    }

    // Récupérer tous les messages d'une matière
    public function getMessagesByMatiere($id_matiere) {
        $stmt = $this->pdo->prepare("SELECT * FROM Message WHERE Id_Matiere = ? ORDER BY Id_Message DESC");
        $stmt->execute([$id_matiere]);
        return $stmt->fetchAll();
    }

    // Récupérer tous les messages d'un utilisateur
    public function getMessagesByUser($id_user) {
        $stmt = $this->pdo->prepare("SELECT * FROM Message WHERE Id_user = ? ORDER BY Id_Message DESC");
        $stmt->execute([$id_user]);
        return $stmt->fetchAll();
    }

    // Supprimer un message
    public function deleteMessage($id_message) {
        $stmt = $this->pdo->prepare("DELETE FROM Message WHERE Id_Message = ?");
        return $stmt->execute([$id_message]);
    }

    // Modifier le contenu d'un message
    public function updateMessage($id_message, $newContenu) {
        $stmt = $this->pdo->prepare("UPDATE Message SET Contenu = ? WHERE Id_Message = ?");
        return $stmt->execute([$newContenu, $id_message]);
    }
}