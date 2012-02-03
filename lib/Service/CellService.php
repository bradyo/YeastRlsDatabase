<?php

class Service_CellService 
{
    private $db;
    private $cache;
    
    public function __construct(PDO $db) {
        $this->db = $db;
    }
    
    public function insertCellData($data) {
        // get (or create) prepared statemenet for insert
        $insertStmt = $this->cache['cellInsertStmt'];
        if ($insertStmt === null) {
            $columns = array(
                'experiment_id',
                'strain_id',
                'label',
                'media',
                'temperature',
                'lifespan',
                'end_state',
            );
            $columnsString = join(',', $columns);
            $valuesString = join(',', array_fill(0, count($columns), '?'));
            $sql = "INSERT INTO cell ({$columnsString}) VALUES ({$valuesString})";
            $insertStmt = $this->db->prepare($sql);
            $this->cache['cellInsertStmt'] = $insertStmt;
        }
        // execute insert statement
        $params = array(
            $data['experiment_id'],
            $data['strain_id'],
            $data['label'],
            $data['media'],
            $data['temperature'],
            $data['lifespan'],
            $data['end_state']
        );
        $insertStmt->execute($params);
        return $this->db->lastInsertId();
    }
}
