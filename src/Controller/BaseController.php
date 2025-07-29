<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BaseController
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

    protected function json(array $data, int $status = 200): Response
    {
        return new Response(
            json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            $status,
            ['Content-Type' => 'application/json; charset=UTF-8']
        );
    }

    protected function redirect(string $url, int $status = 302): Response
    {
        return new Response('', $status, ['Location' => $url]);
    }
} 