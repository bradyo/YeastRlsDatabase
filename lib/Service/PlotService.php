<?php

class Service_PlotService 
{    
    private $rExecPath;
    private $scriptsPath;
    private $plotsPath;
    
    public function __construct($rExecPath, $plotsPath) {
        $this->rExecPath = $rExecPath;
        $this->scriptsPath = BASE_PATH 
                . DIRECTORY_SEPARATOR . 'lib' 
                . DIRECTORY_SEPARATOR . 'R';
        $this->plotsPath = $plotsPath;
    }
    
    public function makeHistogram($filename, $lifespans) {
        $script = $this->scriptsPath . DIRECTORY_SEPARATOR . 'makeHistogram.R';
        $filePath = $this->plotsPath . DIRECTORY_SEPARATOR . basename($filename);
        $argString = $this->getArgString(array(
            $filePath,
            join(',', $lifespans)
        ));
        $command = "\"{$this->rExecPath}\" --vanilla --args {$argString} < \"{$script}\"";
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