<?php

/**
 * MyCakePHP_Sniffs_PHP_TypeCastingSniff
 *
 * PHP version 5
 *
 * @category  PHP
 * @author    Mark Scherer <dereuromark@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @version   1.0
 */
class MyCakePHP_Sniffs_PHP_TypeCastingSniff implements PHP_CodeSniffer_Sniff {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * Note, that this sniff only checks the value and casing of a cast.
	 * It does not check for whitespace issues regarding casts, as
	 * - Squiz.WhiteSpace.CastSpacing.ContainsWhiteSpace checks for whitespace in the cast
	 * - Generic.Formatting.NoSpaceAfterCast.SpaceFound checks for whitespace after the cast
	 *
	 * @return array
	 */
	public function register() {
		return array_merge(PHP_CodeSniffer_Tokens::$castTokens, array(T_BOOLEAN_NOT));
	}

	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
	 * @param integer              $stackPtr  The position of the current token in the
	 *                                        stack passed in $tokens.
	 * @return void
	 */
	public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {
		$tokens = $phpcsFile->getTokens();

		// Process !! casts
		if ($tokens[$stackPtr]['code'] == T_BOOLEAN_NOT) {
			$nextToken = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
			if (!$nextToken) {
				return;
			}
			if ($tokens[$nextToken]['code'] != T_BOOLEAN_NOT) {
				return;
			}
			$error = 'Usage of !! cast is not allowed. Please use (bool) to cast.';
			$phpcsFile->addWarning($error, $stackPtr, 'Error');
			return;
		}

		// Only allow short forms if both short and long forms are possible
		$matching = array(
			'(boolean)' => '(bool)',
			'(integer)' => '(int)',
		);
		$content = $tokens[$stackPtr]['content'];
		$key = strtolower($content);
		if (isset($matching[$key])) {
			$error = 'Please use ' . $matching[$key] . ' instead of ' . $content . '.';
			$phpcsFile->addWarning($error, $stackPtr, 'Warning');
			return;
		}
		if ($content !== $key) {
			$error = 'Please use ' . $key . ' instead of ' . $content . '.';
			$phpcsFile->addWarning($error, $stackPtr, 'Warning');
			return;
		}
	}

}
