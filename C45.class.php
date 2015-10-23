<?php

class C45 {

    public $path = '/usr/local/bin/c4.5';

    private $return, $functions;

    public $php = '';

    /**
     * @param string $name
     */
    public function __construct($name = '') {
        if(!empty($name))
            $this->getData($name);

        return $this;
    }

    /**
     * Retrieve data from C4.5
     * @param $name
     * @return $this
     */
    public function getData($name) {
        $this->return = shell_exec($this->path . ' -f ' . $name);
        return $this;
    }

    /**
     * Parsing a decision tree to return functions
     * @return $this|null
     */
    public function parse() {
        if(empty($this->return))
            return null;

        preg_match_all('|File stem \<(.*)\>|',$this->return,$this->fonctions);

        // Extract tree
        $start = strpos($this->return, 'Decision Tree:')+15;
        $end = strpos($this->return, 'Simplified Decision Tree:');
        $tree = trim(substr($this->return, $start, ($end-$start)));
        $tree = preg_replace('|(\(.*\))|','',$tree);

        // Generate functions
        $lines = explode("\n",$tree);
        $levelCurrent = -1;

        $this->php .= 'function ' . $this->functions[1][0] . '() { ' . "\n";

        foreach($lines as $line) {
            $level = substr_count($line, '|');
            $purge = trim(str_replace('|','',$line));
            $words = explode(' ',$purge);
            $last = count($words)-1;

            // New level of the tree
            if($level != $levelCurrent) {
                if($level < $levelCurrent) {
                    for($t = 1; $t < $level; $t++)
                        $this->php .= "\t";
                    $dif = $levelCurrent-$level;
                    for($t = 0; $t < $dif; $t++) {
                        for($c = 1; $c < $level-$t; $c++)
                            $this->php .= "\t";
                        $this->php .= ' } ';
                    }
                }
                $levelCurrent = $level;
            }

            // Detecting the type of condition
            if((string) intval($words[$last]) != $words[$last]) {
                foreach($words as $i => $word) {
                    if(strpos($word, '>') !== false || strpos($word, '=') !== false || strpos($word, '<') !== false) {
                        $var = $words[($i-1)];
                        $condition = $word;
                        $verif = str_replace(':','',$words[($i+1)]);
                        for($t = 1; $t < $levelCurrent; $t++)
                            $this->php .= "\t";
                        $this->php .= 'if($' . $var . ' ' . $condition . ' ' . $verif . ') { ' . "\n";

                    }
                }
            }
            else {
                foreach($words as $i => $word) {
                    if(strpos($word, '>') !== false || strpos($word, '=') !== false || strpos($word, '<') !== false) {
                        $var = $words[($i-1)];
                        $condition = $word;
                        $verif = str_replace(':','',$words[($i+1)]);
                        if(!isset($words[($i+3)])) {
                            $val = str_replace(':','',$words[($i+2)]);
                        } else {
                            $val = str_replace(':','',$words[($i+3)]);
                        }

                        for($t = 1; $t < $levelCurrent; $t++)
                            $this->php .= "\t";
                        $this->php .= 'if($' . $var . ' ' . $condition . ' ' . $verif . ') { return ' . $val . '; }' . "\n";

                    }
                }
            }
        }

        // Close conditions
        for($t = 0; $t < $levelCurrent; $t++) {
            for($c = 1; $c < $level-$t; $c++)
                $this->php .= "\t";
            $this->php .= ' } ';
        }
        $this->php .= '}';

        return $this;
    }
}
