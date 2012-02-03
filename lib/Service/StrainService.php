<?php

class Service_StrainService {

    private $db;
    private $cache;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function getStrainDataByName($namespace, $name) {
        $stmt = null;
        if (isset($this->cache['findStmt'])) {
            $stmt = $this->db->prepare('
                SELECT * FROM strain 
                WHERE namespace = ? AND name = ? 
                LIMIT 1
            ');
            $this->cache['findStmt'] = $stmt;
        }
        $stmt->execute(array($namespace, $name));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($row !== false) ? $row : null;
    }

    public function getStrainsDataByNames($namespace, $strainNames) {
        if (count($strainNames) < 1) {
            return array();
        }
        $qString = join(',', array_fill(0, count($strainNames), '?'));
        $stmt = $this->db->prepare('
            SELECT * FROM strain 
            WHERE namespace = ? AND name IN (' . $qString . ')
            ');
        $params = array_merge(
            array($namespace),
            $strainNames
        );
        $stmt->execute($params);
        
        $data = array();
        while (($row = $stmt->fetch(PDO::FETCH_ASSOC)) !== false) {
            $name = $row['name'];
            $data[$name] = $row;
        }
        return $data;
    }
    
    public function insertStrainData($strainData) {
        // get (or create) prepared statemenet for insert
        $insertStmt = $this->strainInsertStmt;
        if ($insertStmt == null) {
            $columns = array(
                'namespace',
                'name',
                'contact_email',
                'background',
                'mating_type',
                'genotype',
                'short_genotype',
                'pooling_genotype',
                'freezer_code',
                'comment'
            );
            $columnsString = join(',', $columns);
            $valuesString = join(',', array_fill(0, count($columns), '?'));
            $sql = "INSERT INTO strain ({$columnsString}) VALUES ({$valuesString})";
            $insertStmt = $this->db->prepare($sql);
        }
        // execute insert statement
        $params = array(
            $strainData['namespace'],
            $strainData['name'],
            $strainData['contact_email'],
            $strainData['background'],
            $strainData['mating_type'],
            $strainData['genotype'],
            $strainData['short_genotype'],
            $strainData['pooling_genotype'],
            $strainData['freezer_code'], 
            $strainData['comment'],
        );
        $insertStmt->execute($params);
        return $this->db->lastInsertId();
    }
}
