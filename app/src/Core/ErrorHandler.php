<?php

namespace Morrison\Core;

use Morrison\Core\Environment;
use Swift_Mailer;

class ErrorHandler
{
    /**
     * @var Environment
     */
    protected $env;

    /**
     * @var \Swift_Mailer
     */
    protected $mailer;

    /**
     * @var bool
     */
    protected $debug;

    public function __construct(Environment $env, Swift_Mailer $mailer, $debug = false)
    {
        $this->env = $env;
        $this->mailer = $mailer;
        $this->debug = $debug;
    }

    public static function register(Environment $env, Swift_Mailer $mailer, $debug = false)
    {
        $handler = new static($env, $mailer, $debug);

        if ($debug) {
            // Debug on, usually local dev
            ini_set('display_errors', 1);
            error_reporting((E_ALL ^ E_DEPRECATED) & ~E_STRICT);
        } else {
            // Production and dev environments
            ini_set('display_errors', 0);
            error_reporting((E_ALL ^ E_DEPRECATED) & ~E_STRICT);
        }

        set_error_handler(array($handler, 'handleErrors'));
        set_exception_handler(array($handler, 'handleExceptions'));
        register_shutdown_function(array($handler, 'handleFatalErrors'));
    }

    /**
     * Errors should be turned into exceptions and handled thusly
     *
     * @param int $num One of the E_* constants
     * @param string $str
     * @param string $file
     * @param int $line
     * @param null|array $context
     * @return bool
     */
    public function handleErrors($num, $str, $file, $line, $context = null)
    {
        if (!(error_reporting() & $num)) {
            // This error code is not included in error_reporting
            return true;
        }

        if ($this->debug) {
            // In debug mode let the default error handlers run
            return false;
        }

        switch ($num) {
            case E_NOTICE:
            case E_USER_NOTICE:
            case E_WARNING:
            case E_USER_WARNING:
                // report on warning/notice and move on
                // build something to print debug backtrace later
                $this->mailError('PHP Error ('.self::friendlyErrorType($num).')', $str, $file, $line, '');
                return true;
                break;
            case E_STRICT:
                // do nothing
                return true;
                break;
            default:
                // handle any other error as an exception
                $this->handleExceptions(new \ErrorException($str, 0, $num, $file, $line));
                return false;
                break;
        }
    }

    /**
     * Exception handling
     */
    public function handleExceptions(\Exception $e)
    {
        $type = get_class($e);
        if ($e instanceof \ErrorException) {
            $type .= ' ('.self::friendlyErrorType($e->getSeverity()).')';
        }

        $message = $e->getMessage();
        $file = $e->getFile();
        $line = $e->getLine();
        $trace = $e->getTraceAsString();

        if (!$this->debug) {
            // Only mail error when not in debug
            $this->mailError($type, $message, $file, $line, $trace);
        }

        $this->printError($type, $message, $file, $line, $trace);
    }

    /**
     * Catch fatal errors, which aren't caught by the regular error handler
     */
    public function handleFatalErrors()
    {
        if (null === $error = error_get_last()) {
            return;
        }

        if ($error["type"] == E_ERROR) {
            $this->printError(
                'PHP Error ('.self::friendlyErrorType($error['type']).')',
                $error['message'],
                $error['file'],
                $error['line'],
                ''
            );
        }
    }

    protected function mailError($type, $message, $file, $line, $trace)
    {
        if ($this->throttleMessage($message)) {
            // This message should be throttled, skip sending the email
            return;
        }

        $host = gethostname();
        $envName = $this->env->getEnv();

        // Never email a user's plain text password
        unset($_POST['password']);
        unset($_GET['password']);
        unset($_REQUEST['password']);

        // send mail using SwiftMail
        $body = "An error or exception was encountered on the tracking site.  Details follow\n\n".
                "Host: $host\n".
                "Environment: $envName\n".
                "Type: $type\n".
                "Message: $message\n".
                "File: $file\n".
                "Line: $line\n\n".
                "Stack Trace:\n$trace\n\n".
                "\$_SERVER: ".print_r($_SERVER, true)."\n\n".
                "\$_REQUEST: ".print_r($_REQUEST, true)."\n\n".
                "\$_GET: ".print_r($_GET, true)."\n\n".
                "\$_POST: ".print_r($_POST, true)."\n\n".
                "\$_COOKIE: ".print_r($_COOKIE, true)."\n\n";

        // Session may not have been started
        if (isset($_SESSION)) {
            $body .= "\$_SESSION: ".print_r($_SESSION, true)."\n\n";
        }

        $mailMessage = \Swift_Message::newInstance()
            ->setSubject("Error/Exception on Morrsion Site - $envName ($host)")
            ->setFrom('bugs@mmyyabb.com')
            ->setTo('sunce.apples@gmail.com')
            ->setBody($body);

        $this->mailer->send($mailMessage);
    }

    protected function printError($type, $message, $file, $line, $trace)
    {
        $host = gethostname();
        $envName = $this->env->getEnv();

        $out = '<!doctype html><head><title> Error</title></head><body>';
        $out .= '<h1>Error</h1>';
        $out .= '<p>An error was encountered during the execution of this page. Administrators have been notified of the problem.</p>';

        if ($this->debug) {
            // if in debug mode, print out a summary of the error
            $out .= '<h2>Details</h2>';
            $out .= "<p><strong>Host:</strong> $host</p>";
            $out .= "<p><strong>Environment:</strong> $envName</p>";
            $out .= "<p><strong>Type:</strong> $type</p>";
            $out .= "<p><strong>Message:</strong> $message</p>";
            $out .= "<p><strong>File:</strong> $file</p>";
            $out .= "<p><strong>Line:</strong> $line</p>";
            $out .= "<pre>$trace</p>";
        }

        $out .= '</body></html>';

        if (PHP_SAPI == 'cli') {
            echo sprintf("file:%s\nline:%s\nmessage:%s\ntrace:%s\n", $file, $line, $message, $trace);
        } else {
            echo $out;
        }
    }

    protected function throttleMessage($message)
    {
        $throttleStrings = array(
            'must be an instance of DB_common, instance of DB_Error given',
            'remaining connection slots are reserved for non-replication superuser connections',
            'preg_replace_callback(): Compilation failed: unrecognized character after', // SYSADMIN-528
        );

        foreach ($throttleStrings as $throttleString) {
            if (strpos($message, $throttleString) !== false) {
                // Message matches, we should throttle it
                if (rand(0, 9) !== 0) {
                    // Randomly throttle 9 out of 10, only send 1/10th of these
                    return true;
                }
            }
        }

        return false;
    }

    protected static function friendlyErrorType($type)
    {
        switch ($type) {
            case E_ERROR: // 1 //
                return 'E_ERROR';
            case E_WARNING: // 2 //
                return 'E_WARNING';
            case E_PARSE: // 4 //
                return 'E_PARSE';
            case E_NOTICE: // 8 //
                return 'E_NOTICE';
            case E_CORE_ERROR: // 16 //
                return 'E_CORE_ERROR';
            case E_CORE_WARNING: // 32 //
                return 'E_CORE_WARNING';
            case E_COMPILE_ERROR: // 64 //
                return 'E_COMPILE_ERROR';
            case E_COMPILE_WARNING: // 128 //
                return 'E_COMPILE_WARNING';
            case E_USER_ERROR: // 256 //
                return 'E_USER_ERROR';
            case E_USER_WARNING: // 512 //
                return 'E_USER_WARNING';
            case E_USER_NOTICE: // 1024 //
                return 'E_USER_NOTICE';
            case E_STRICT: // 2048 //
                return 'E_STRICT';
            case E_RECOVERABLE_ERROR: // 4096 //
                return 'E_RECOVERABLE_ERROR';
            case E_DEPRECATED: // 8192 //
                return 'E_DEPRECATED';
            case E_USER_DEPRECATED: // 16384 //
                return 'E_USER_DEPRECATED';
        }

        return "Unrecognized error type";
    }
}
