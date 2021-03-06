<?php
/**
 * PHPCompatibility_Sniffs_PHP_ConstantArraysUsingDefineSniff.
 *
 * PHP version 7.0
 *
 * @category  PHP
 * @package   PHPCompatibility
 * @author    Wim Godden <wim@cu.be>
 */

/**
 * PHPCompatibility_Sniffs_PHP_ConstantArraysUsingDefineSniff.
 *
 * Constant arrays using define in PHP 7.0
 *
 * PHP version 7.0
 *
 * @category  PHP
 * @package   PHPCompatibility
 * @author    Wim Godden <wim@cu.be>
 */
class PHPCompatibility_Sniffs_PHP_ConstantArraysUsingDefineSniff extends PHPCompatibility_Sniff
{

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(T_STRING);
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token in the
     *                                        stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        
        $ignore = array(
            T_DOUBLE_COLON,
            T_OBJECT_OPERATOR,
            T_FUNCTION,
            T_CONST,
        );
        
        $prevToken = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
        if (in_array($tokens[$prevToken]['code'], $ignore) === true) {
            // Not a call to a PHP function.
            return;
        }
        
        $function = strtolower($tokens[$stackPtr]['content']);
        
        if ($function === 'define') {
            $openParenthesis = $phpcsFile->findNext(T_OPEN_PARENTHESIS, $stackPtr, null, null, null, true);
            if ($openParenthesis === false) {
                return false;
            }
            
            $comma = $phpcsFile->findNext(T_COMMA, $openParenthesis, $tokens[$openParenthesis]['parenthesis_closer']);
            
            $array = $phpcsFile->findNext(array(T_ARRAY, T_OPEN_SHORT_ARRAY), $comma, $tokens[$openParenthesis]['parenthesis_closer']);
            
            if ($array !== false) {
                if ($this->supportsAbove('7.0')) {
                    return false;
                } else {
                    $phpcsFile->addError('Constant arrays using define are not allowed in PHP 5.6 or earlier', $array);
                }
            }
        }
    }
}
