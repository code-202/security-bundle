<?php

namespace Code202\Security\Service\Session;

class TTLProvider
{
    public function __construct(
        protected array $config
    ) {}

    public function getSessionTTL(string $type): int
    {
        if (isset($this->config[$type])) {
            return intval($this->config[$type]);
        }

        return intval($this->config['default']);
    }
}
