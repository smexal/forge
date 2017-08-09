<?php

namespace Forge\Core\Classes;

class Logger {
    public static $levels = array("DEBUG", "INFO", "WARN", "ERROR");
    public static $log_level = "DEBUG";

    public static function timer() {
        return microtime(true);
    }

    public static function stop($start, $level="DEBUG") {
        $time_post = microtime(true);
        $exec_time = round(($time_post - $start) * 1000);
        $string = "Execution Time: ".$exec_time." ms";
        self::log($string, $level);
        return $string;
    }

    public static function log($text, $level=null, $trace=false) {
        if(is_null($level)) {
            $level = "INFO";
        }
        if(! in_array($level, self::$levels)) {
            self::warn("Logged on unknown Level '".$message."'");
        }
        $log_level = self::$log_level;
        if(! is_null(LOG_LEVEL)) {
            $log_level = LOG_LEVEL;
        }
        $log_level_key = array_search($log_level, self::$levels);
        $log_output_key = array_search($level, self::$levels);

        if($log_level_key <= $log_output_key) {
          if(! is_array($text))
            $text = array($text);
          foreach($text as $key => $value) {
            if(is_array($value)) {
              $value = implode(", ", $value);
            }
            $output = date("Y-m-d H:i:s")." - ".$level." - ".$key." => ".$value."\n";
            if ($trace) {
                $t = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
                $lines = LOG_TRACE_LINES;
                if (! defined(LOG_TRACE_LINES) || count($t) < LOG_TRACE_LINES) {
                    $lines = count($t)-1;
                }
                
                for ($i = 1; $i <= $lines; $i++) {
                    $output .= str_pad("#$i",4," ") ."> ".$t[$i]['file'].':'.$t[1]['line']."\n";
                }
            }
            $filename = "error-".date("Y-m-d").".log";

            $file = fopen(DOC_ROOT."logs/".$filename, "a+") or die("Unable to open Log File!");
            fwrite($file, $output);
            fclose($file);
          }
        }
    }

    public static function warn($message, $trace=false) {
        self::log($message, "WARN", $trace);
    }

    public static function error($message, $trace=false) {
        self::log($message, "ERROR", $trace);
    }

    public static function info($message, $trace=false) {
        self::log($message, "INFO", $trace);
    }

    public static function debug($message, $trace=false) {
        self::log($message, "DEBUG", $trace);
    }
}

