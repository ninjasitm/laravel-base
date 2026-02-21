<?php

namespace Nitm\Utils\Classes;

class DebugHelper
{
    /**
         * This is for use when you have the UBER-LAME...
         * "PHP Fatal error:  Maximum function nesting level of '100' reached,
         * aborting!  in Lame.php(1273)
         * ...which just craps out leaving you without a stack trace.
         * At the line in the file where it finally spazzes out add
         * something like...
         * DebugUtil::dumpStack('/tmp/lame');
         * It will write the stack into that file every time it passes that
         * point and when it eventually blows up (and probably long before) you
         * will be able to see where the problem really is.
         */
        public static function printBacktrace($fileName = true)
        {
            echo 'Debugging';
            $stack = '';
            foreach (debug_backtrace() as $trace) {
                if (isset($trace['file']) &&
                    isset($trace['line']) &&
                    isset($trace['class']) &&
                    isset($trace['function'])) {
                    $stack .= $trace['file'].'#'.
                              $trace['line'].':'.
                              $trace['class'].'.'.
                              $trace['function']."\n";
                }
            }
            if ($fileName === true) {
                file_put_contents($fileName, $stack);
            } else {
                echo $stack;
            }
        }
}
