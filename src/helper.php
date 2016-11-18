<?php
/**
 *
 * @since             1.0.0
 * @link              https://programming-review.com
 * @package           WW
 *
 */
namespace DK_20161118;

/**
 * Class providing smart helper methods.
 * @since         1.0.0
 */
class Helper {
    /**
     * Correct line endings function. Make all line endings the same, and Linux based.
     * @param  string $s Input string that has possible "bad" line endings.
     * @return string    Correct line endings (LF).
     */
     public static function fix_line_endings( $s ) {
       // Convert all line-endings to Linux format LF
       $s = str_replace( "\r\n", "\n", $s ); // Windows
       $s = str_replace( "\r", "\n", $s ); // Mac
       return $s;
    }
}
