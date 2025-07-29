<?php

namespace App\Router;

use App\Controller\MainController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Router
{
    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function handle(): Response
    {
        $path = $this->request->getPathInfo();

        // Убираем trailing slash
        $path = rtrim($path, '/');
        if (empty($path)) {
            $path = '/';
        }

        return match ($path) {
            '/' => (new MainController($this->request))->index(),
            '/api/search' => (new MainController($this->request))->apiSearch(),
            default => new Response(
                '<h1>404 - Страница не найдена</h1><p>Запрашиваемая страница не существует.</p><a href="/">Вернуться на главную</a>',
                404
            )
        };
    }
}