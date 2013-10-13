<?php
/**
 * PHP Version 5
 *
 * MyCakePHP_Sniffs_PHP_IsNullSniff
 *
 * @category  PHP
 * @author    Mark Scherer <dereuromark@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @version   1.0
 */

/**
 * Ensures that strict check "=== null" is used instead of is_null().
 *
 */
class MyCakePHP_Sniffs_PHP_IsNullSniff implements PHP_CodeSniffer_Sniff {

/**
 * Returns an array of tokens this test wants to listen for.
 *
 * @return array
 */
	public function register() {
		return array(T_STRING);
	}

/**
 * Processes this test, when one of its tokens is encountered.
 *
 * Ensures that strict check "=== null" is used instead of is_null().
 * Only for `is(null) !== ...` and `... !== is(null)` it will be skipped.
 *
 * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
 * @param integer              $stackPtr  The position of the current token in the
 *                                        stack passed in $tokens.
 * @return void
 */
	public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {
		$tokens = $phpcsFile->getTokens();
		$content = strtolower($tokens[$stackPtr]['content']);
		if ($content !== 'is_null') {
			return;
		}

		// Open parenthesis should come next
		$openToken = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
		if ($tokens[$openToken]['code'] !== T_OPEN_PARENTHESIS) {
			return;
		}

		// Then closing one
		$closeToken = $phpcsFile->findNext(T_CLOSE_PARENTHESIS, ($openToken + 1));
		if (!$closeToken) {
			return;
		}

		// If comparison operator before or after, we need to skip
		$comparisonOperators = array(T_IS_NOT_IDENTICAL, T_IS_IDENTICAL);
		$previousToken = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
		if (in_array($tokens[$previousToken]['code'], $comparisonOperators)) {
			return;
		}

		$nextToken = $phpcsFile->findNext(T_WHITESPACE, ($closeToken + 1), null, true);
		if (in_array($tokens[$nextToken]['code'], $comparisonOperators)) {
			return;
		}

		$comparison = '===';
		$previousToken = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
		if ($previousToken && $tokens[$previousToken]['code'] === T_BOOLEAN_NOT) {
			$comparison = '!==';
		}

		$error = 'Usage of ' . $tokens[$stackPtr]['content'] . ' not allowed; use strict null check (' . $comparison . ' null) instead';
		$phpcsFile->addFixableError($error, $stackPtr, 'NotAllowed');

		// Fix the error
		if ($phpcsFile->fixer->enabled === true) {
			$phpcsFile->fixer->beginChangeset();
			for ($i = $stackPtr; $i <= $openToken; $i++) {
				$phpcsFile->fixer->replaceToken($i, '');
			}
			if ($comparison === '!==') {
				$phpcsFile->fixer->replaceToken($previousToken, '');
			}
			$phpcsFile->fixer->replaceToken($closeToken, ' ' . $comparison . ' null');
			$phpcsFile->fixer->endChangeset();
		}
	}

}
