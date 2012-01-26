<?php

class Build_Analyzer
{    
    private $rExecPath;
    private $scriptPath;
    private $dsn;
    private $username;
    private $password;
    
    public function __construct($rExecPath, $dsn, $username, $password) {
        $this->rExecPath = $rExecPath;
        $this->scriptPath = BASE_PATH 
                . DIRECTORY_SEPARATOR . 'lib' 
                . DIRECTORY_SEPARATOR . 'R'
                . DIRECTORY_SEPARATOR . 'analyze.R';
        $this->dsn = $dsn;
        $this->username = $username;
        $this->password = $password;
    }
    
    public function run() {
        $argString = $this->getArgString(array(
            $this->dsn,
            $this->username,
            $this->password
        ));
        $command = "\"{$this->rExecPath}\" --vanilla --args {$argString} < \"{$this->scriptPath}\"";
        exec($command);
    }

    private function getArgString($args) {
        $quotedArgs = array();
        foreach ($args as $arg) {
            $escapedArg = str_replace('"', '\"', $arg);
            $quotedArgs[] = '"' . $escapedArg . '"';
        }
        return join(' ', $quotedArgs);
    }
    
}