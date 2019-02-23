<?php

namespace Planck\Console;

class Console
{

    protected $verbose = true;
    protected $arguments = [];
    protected $parameters = [];

    protected $command;

    public function __construct()
    {

    }


    public function commitAll($path)
    {
        $this->foreachPath($path, function($path) {
            return $this->commit($path);
        });
    }


    public function resetAll($path)
    {
        $this->foreachPath($path, function($path) {
            return $this->reset($path);
        });
    }



    protected function commit($path)
    {

        if(!is_dir($path.'/.git')) {
            return true;
        }

        ob_start();
        system('git status', $status);
        $output = ob_get_clean();


        if($status == 0 ) {
            $this->output("synchronisation\n");
            $this->output(system('git add . && git commit -m "synchro" && git push'));
            $this->output("\n");
        }
        return true;
    }

    protected function reset($path)
    {

        if(!is_dir($path.'/.git')) {
            return true;
        }

        ob_start();
        system('git status', $status);
        $output = ob_get_clean();


        if($status == 0 ) {
            $this->output("Reset changes\n");
            $this->output(system('git reset --hard'));
            $this->output("\n");
        }
        return true;
    }






    protected function verbose($value = null)
    {
        if($value === null) {
            return $this->verbose;
        }
        $this->verbose = $value;
        return $this;
    }



    protected function foreachPath($path, $callback)
    {

        $currentDir = getcwd();

        $path = realpath($path);

        $this->output("Opening working folder \t".$path."\n");
        chdir($path);

        $dir = opendir($path);
        while($entry = readdir($dir)) {
            if($entry != '.' && $entry != '..' && is_dir($path.'/'.$entry)) {


                $this->output("============");
                $this->output($path.'/'.$entry);

                chdir($path.'/'.$entry);

                $returnValue = $callback($path.'/'.$entry);

                if(!$returnValue) {
                    return;
                }
            }
        }

        chdir($currentDir);
    }






    public function loadArguments($arguments)
    {
        $this->arguments = $arguments;
        $command = $arguments[1];
        if(method_exists($this, $command)) {
            $this->command = $command;
            $parameters = $arguments;
            array_shift($parameters);   //removing php filename
            array_shift($parameters);   //removing command

            if(!empty($parameters)) {
                $this->parameters = $parameters;
            }
        }
        else {
            $this->output('Command '.$command.' does not exists');
            exit();
        }
    }

    public function execute()
    {
        return call_user_func_array(
            array($this, $this->command),
            $this->parameters
        );
    }





    public function output($string, $lineEnd = "\n")
    {
        if($this->verbose) {
            echo $string.$lineEnd;
        }
    }
}




