<?php

namespace Morrison\Core;

class Environment
{
    /** @var String $env */
    private $env;

    private $env_dev = array(
        'morrison_dev'
    );

    private $env_prod = array(
        'morrison_prod'
    );

    public function __construct($env)
    {
        if (!in_array($env, $this->env_dev) && !in_array($env, $this->env_prod)) {
            $env = 'morrison_dev';
        }
        $this->env = $env;
    }

    public function getEnv()
    {
        return $this->env;
    }

    public function isDev()
    {
        return in_array($this->env, $this->env_dev);
    }

    public function isProd()
    {
        return in_array($this->env, $this->env_prod);
    }
}
