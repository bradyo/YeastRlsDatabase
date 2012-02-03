<?php

class Service_SampleService 
{
    private $db;
    private $cache;
    
    public function __construct(PDO $db) {
        $this->db = $db;
    }
    
    public function insertSampleData($namespace, $data) {
        // get (or create) prepared statemenet for insert
        $insertStmt = $this->cache['sampleInsertStmt'];
        if ($insertStmt == null) {
            $columns = array(
                'namespace',
                'pooled_by',
                'pooling_key',
                'label',
                'strain',
                'background',
                'mating_type',
                'genotype',
                'media',
                'temperature',
                'lifespans_count',
                'lifespans_omitted_count',
                'lifespans_mean',
                'lifespans_stdev',
                'cells_data',
            );
            $columnsString = join(',', $columns);
            $valuesString = join(',', array_fill(0, count($columns), '?'));
            $sql = "INSERT INTO sample ({$columnsString}) VALUES ({$valuesString})";
            $insertStmt = $this->db->prepare($sql);
            $this->cache['sampleInsertStmt'] = $insertStmt;
        }
        // execute insert statement
        $params = array(
            $namespace,
            $data['pooledBy'],
            $data['poolingKey'],
            $data['label'],
            $data['strain'],
            $data['background'],
            $data['matingType'],
            $data['genotype'],
            $data['media'],
            $data['temperature'],
            $data['lifespansCount'],
            $data['lifespansOmittedCount'],
            $data['lifespansMean'],
            $data['lifespansStdev'],
            $data['cellsData'],
        );
        $insertStmt->execute($params);
        return $this->db->lastInsertId();
    }
    
    public function insertSampleCell($sampleId, $cellId) {
        $stmt = $this->cache['sampleCellInsertStmt'];
        if ($stmt === null) {
            $stmt = $this->db->prepare('
                INSERT INTO sample_cell (sample_id, cell_id)  VALUES (?, ?)
            ');
            $this->cache['sampleCellInsertStmt'] = $stmt;
        }
        $stmt->execute(array($sampleId, $cellId));
    }
    
    public function insertSampleCitation($sampleId, $citationId) {
        $stmt = $this->cache['sampleCitationInsertStmt'];
        if ($stmt === null) {
            $stmt = $this->db->prepare('
                INSERT INTO sample_citation (sample_id, citation_id)  VALUES (?, ?)
            ');
            $this->cache['sampleCitationInsertStmt'] = $stmt;
        }
        $stmt->execute(array($sampleId, $citationId));
    }
}
