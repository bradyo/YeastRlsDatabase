<?php

class Service_StrainService {

    private $db;
    private $findStmt;
    
    public function __construct(PDO $db) {
        $this->db = $db;
    }
    
    public function getStrainDataByName($name) {
        if ($this->findStmt == null) {
            $this->findStmt = $this->db->prepare('
                SELECT * FROM strain WHERE name = ? LIMIT 1
            ');
        }
        $this->findStmt->execute(array($name));
        $row = $this->findStmt->fetch(PDO::FETCH_ASSOC);
        if ($row !== false) {
            return $row;
        } else {
            return null;
        }
  }
}
