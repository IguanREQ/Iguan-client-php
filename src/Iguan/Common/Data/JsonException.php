<?php


namespace Iguan\Common\Data;

use Throwable;

/**
 * Class JsonException
 * Automatically create an exception
 * with filled message and code for last json
 * encode/decode operation.
 *
 * @author Vishnevskiy Kirill
 */
class JsonException extends EncodeDecodeException
{
    public function __construct(Throwable $previous = null)
    {
        $message = $this->decodeErrorMessage();
        $code = json_last_error();
        parent::__construct($message, $code, $previous);
    }

    private function decodeErrorMessage()
    {
        $errorCode = json_last_error();
        $errorCodeMessage = [
            JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded.',
            JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON.',
            JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded.',
            JSON_ERROR_SYNTAX => 'Syntax error.',
            JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded.',
            JSON_ERROR_RECURSION => 'One or more recursive references in the value to be encoded.',
            JSON_ERROR_INF_OR_NAN => 'One or more NAN or INF values in the value to be encoded.',
            JSON_ERROR_UNSUPPORTED_TYPE => 'A value of a type that cannot be encoded was given.',
        ];

        if (PHP_VERSION_ID > 70000) {
            $errorCodeMessage[JSON_ERROR_INVALID_PROPERTY_NAME] = 'A property name that cannot be encoded was given.';
            $errorCodeMessage[JSON_ERROR_UTF16] = 'Malformed UTF-16 characters, possibly incorrectly encoded.';
        }
        $errorPrefix = 'JSON: ';
        if (isset($errorCodeMessage[$errorCode])) {
            $errorMsg = $errorPrefix . $errorCodeMessage[$errorCode];
        } else {
            $errorMsg = $errorPrefix . 'Unknown error.';
        }

        return $errorMsg;
    }

    /**
     * @return bool true if there is an error during
     *          last encode/decode operation.
     */
    public static function hasError()
    {
        $error_code = json_last_error();
        return $error_code !== JSON_ERROR_NONE;
    }
}