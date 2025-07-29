<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseController
{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    protected function render(string $content, int $status = 200): Response
    {
        return new Response($content, $status, [
            'Content-Type' => 'text/html; charset=UTF-8'
        ]);
    }
}