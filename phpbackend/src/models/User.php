<?php

class User {
    public $id;
    public $active = false;

    public function __construct($id) {
        $this->id = trim($id); // Remove any extra spaces
        $this->loadUserData();
    }

    private function loadUserData() {
        $pdo = Database::getConnection();
        
        echo "🔍 Looking for user_id: " . $this->id . "<br>";

        $stmt = $pdo->prepare("SELECT is_active FROM users WHERE user_id = ? LIMIT 1");
        $stmt->execute([$this->id]);
        $data = $stmt->fetch();

        if ($data) {
            $this->active = (bool)$data['is_active'];
            echo "✅ User found! Active = " . ($this->active ? 'YES' : 'NO') . "<br>";
        } else {
            echo "❌ User NOT found in database<br>";
            
            // Extra debug
            $stmt2 = $pdo->prepare("SELECT user_id, first_name FROM users WHERE user_id LIKE ? LIMIT 3");
            $stmt2->execute(['%' . substr($this->id, 0, 8) . '%']);
            $similar = $stmt2->fetchAll();
            echo "Similar users found: " . count($similar) . "<br>";
        }
    }
}