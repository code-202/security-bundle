<?php

namespace Code202\Security\Service\Session;

class TTLProvider
{
    protected array $config;

    public function __construct(
        array $config
    ) {
        $this->config = $config;
    }

    public function getSessionTTL(string $type): int
    {
        if (isset($this->config[$type])) {
            return intval($this->config[$type]);
        }

        return intval($this->config['default']);
    }
}
