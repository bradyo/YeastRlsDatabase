<?php

class Service_SubmissionService 
{
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function getSubmissionData($name) {
        $stmt = $this->db->prepare('
            SELECT * FROM submission s WHERE s.name = ?
            ');
        $stmt->execute(array($name));
        if (($row = $stmt->fetch(PDO::FETCH_ASSOC)) !== false) {
            return $row;
        } else {
            return null;
        }
    }

}
