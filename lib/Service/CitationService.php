<?php

class Service_CitationService 
{
    private $db;
    private $cache;
    
    public function __construct(PDO $db) {
        $this->db = $db;
    }
    
    public function getCitationsDataIndexedByPubmedId() {
        $stmt = $this->db->prepare('SELECT * FROM citation');
        $stmt->execute();
        
        $citations = array();
        while (($row = $stmt->fetch(PDO::FETCH_ASSOC)) !== false) {
            $pubmedId = $row['pubmed_id'];
            $citations[$pubmedId] = $row;
        }
        return $citations;
    }
    
    public function insertCitationData($data) {
        // get (or create) prepared statemenet for insert
        $insertStmt = $this->cache['citationInsertStmt'];
        if ($insertStmt === null) {
            $columns = array(
                'pubmed_id',
                'title',
                'first_author',
                'year',
                'summary',
            );
            $columnsString = join(',', $columns);
            $valuesString = join(',', array_fill(0, count($columns), '?'));
            $sql = "INSERT INTO cell ({$columnsString}) VALUES ({$valuesString})";
            $insertStmt = $this->db->prepare($sql);
            $this->cache['citationInsertStmt'] = $insertStmt;
        }
        // execute insert statement
        $params = array(
            $data['pubmed_id'],
            $data['title'],
            $data['first_author'],
            $data['year'],
            $data['summary'],
        );
        $insertStmt->execute($params);
        return $this->db->lastInsertId();
    }
}
