<?php
class Role {
    private $pdo;
    private $role;

    public function __construct($pdo, $role) {
        $this->pdo = $pdo;
        $this->role = $role;
    }

    // Récupérer un rôle par son ID
    public function getRoleById($id_role) {
        $stmt = $this->pdo->prepare("SELECT * FROM Role WHERE Id_Role = ?");
        $stmt->execute([$id_role]);
        return $stmt->fetch();
    }

    // Récupérer le rôle par son nom
    public function getRoleByName($name) {
        $stmt = $this->pdo->prepare("SELECT * FROM Role WHERE role = ?");
        $stmt->execute([$name]);
        return $stmt->fetch();
    }

    // Initialiser les deux rôles par défaut si la table est vide
    public function initDefaultRoles() {
        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM Role");
        $count = $stmt->fetch()['count'];
        if ($count == 0) {
            $this->pdo->prepare("INSERT INTO Role (role) VALUES (?), (?)")
                ->execute(['étudiant', 'Professeur responsable']);
        }
    }
}