<?php

class Logger {
    public static $levels = array("DEBUG", "INFO", "WARN", "ERROR");
    public static $log_level = "DEBUG";

    public static function log($text, $level=null) {
        if(is_null($level)) {
            $level = "INFO";
        }
        if(! in_array($level, self::$levels)) {
            self::log("Logged on uknown Level '".$message."'", "WARN");
        }
        $log_level = self::$log_level;
        if(! is_null(LOG_LEVEL)) {
            $log_level = LOG_LEVEL;
        }
        $log_level_key = array_search($log_level, self::$levels);
        $log_output_key = array_search($level, self::$levels);

        if($log_level_key <= $log_output_key) {
            $filename = "error-".date("Y-m-d").".log";

            $file = fopen(DOC_ROOT."logs/".$filename, "a+") or die("Unable to open Log File!");
            fwrite($file, date("Y-m-d")." - ".$level." - ".$text."\n");
            fclose($file);
        }
    }

    public static function warn($message) {
        self::log($message, "WARN");
    }

    public static function error($message) {
        self::log($message, "ERROR");
    }

    public static function info($message) {
        self::log($message, "INFO");
    }

    public static function debug($message) {
        self::log($message, "DEBUG");
    }
}

?>