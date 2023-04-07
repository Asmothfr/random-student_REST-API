<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class VersioningService
{
    private RequestStack $_requestStack;
    private string $_defaultVersion;

    public function __construct(RequestStack $_requestStack, ParameterBagInterface $params)
    {
        $this->_requestStack = $_requestStack;
        $this->_defaultVersion = $params->get('default_api_version');
    }

    public function getversion(): string
    {
        $version = $this->_defaultVersion;

        $request = $this->_requestStack->getCurrentRequest();
        $accept = $request->headers->get('Accept');

        $header = explode(";", $accept);

        foreach($header as $value)
        {
            if(strpos($value,'version') !== false)
            {
                $version = explode('=', $value);
                $version = $version[1];
                break;
            }
        }
        return $version;
    }

}