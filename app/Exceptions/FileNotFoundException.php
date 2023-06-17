<?php declare(strict_types=1);

namespace App\Exceptions;

class FileNotFoundException extends \Exception
{
    protected $message = 'File Does Not Found';
}