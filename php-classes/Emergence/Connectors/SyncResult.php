<?php

namespace Emergence\Connectors;

class SyncResult
{
    const STATUS_CREATED = 'created';
    const STATUS_UPDATED = 'updated';
    const STATUS_SKIPPED = 'skipped';
    const STATUS_VERIFIED = 'verified';
    const STATUS_DELETED = 'deleted';

    protected $status;
    protected $message = '';
    protected $context = [];

    public function __construct($status, $message, array $context = [])
    {
        $this->status = $status;
        $this->message = $message;
        $this->context = $context;
    }

    public function __toString()
    {
        return $this->getStatus();
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getMessage()
    {
        return static::interpolate($this->message, $this->context);
    }

    public function getContext($key = null)
    {
        if (isset($key)) {
            return $this->context[$key];
        } else {
            return $this->context;
        }
    }

    public static function interpolate($message, array $context = [])
    {
        $replace = [];
        foreach ($context as $key => $value) {
            $replace['{' . $key . '}'] = (string)$value;
        }

        return strtr($message, $replace);
    }

}