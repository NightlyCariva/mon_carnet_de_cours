<?php
class Note {
    private $pdo;
    private $note;
    private $type;

    public function __construct($pdo, $note, $type) {
        $this->pdo = $pdo;
        $this->note = $note;
        $this->type = $type;
    }

    // Créer une note (par un prof à un étudiant pour une matière donnée)
    public function createNote($id_etudiant, $id_matiere, $id_prof) {
        $stmt = $this->pdo->prepare("INSERT INTO Note (Type, Note, Id_user, Id_Matiere, Id_user_1) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$this->type, $this->note, $id_etudiant, $id_matiere, $id_prof]);
    }

    // Récupérer la note d'un étudiant pour une matière donnée (et le prof)
    public function getNote($id_etudiant, $id_matiere, $id_prof) {
        $stmt = $this->pdo->prepare("SELECT * FROM Note WHERE Id_user = ? AND Id_Matiere = ? AND Id_user_1 = ?");
        $stmt->execute([$id_etudiant, $id_matiere, $id_prof]);
        return $stmt->fetch();
    }

    // Modifier la note
    public function updateNote($id_note, $newNote, $newType = null) {
        if ($newType !== null) {
            $stmt = $this->pdo->prepare("UPDATE Note SET Note = ?, Type = ? WHERE Id_Note = ?");
            return $stmt->execute([$newNote, $newType, $id_note]);
        } else {
            $stmt = $this->pdo->prepare("UPDATE Note SET Note = ? WHERE Id_Note = ?");
            return $stmt->execute([$newNote, $id_note]);
        }
    }

    // Supprimer la note
    public function deleteNote($id_note) {
        $stmt = $this->pdo->prepare("DELETE FROM Note WHERE Id_Note = ?");
        return $stmt->execute([$id_note]);
    }
}