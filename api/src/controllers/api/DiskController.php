<?php
namespace Api;

use Arhitector\Yandex\Client\OAuth;
use Arhitector\Yandex\Disk;
use Dotenv\Dotenv;
use Core\Response;
use Core\Route;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'yandex disk api',
    description: 'API для 8 задания стажировки only'
)]
#[OA\Server(
    url: 'http://localhost:8080',
    description: 'Локальный сервер'
)]
#[OA\Tag(name: 'disk', description: 'Управление диском')]
#[Route("/api/yadisk")]
class DiskController
{
    /**
     * Проверка сервера
     */
    #[OA\Get(
        path: '/api/yadisk/check',
        tags: ['disk'],
        summary: 'Проверка сервера',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Работает',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'result', type: 'string', example: 'successful')
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Ошибка сервера',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string')
                    ]
                )
            )
        ]
    )]
    #[Route("/check", "GET")]
    public function check($params)
    {
        return new Response(200, ['result' => 'successful']);
    }

    /**
     * Список файлов
     */
    #[OA\Get(
        path: '/api/yadisk',
        tags: ['disk'],
        summary: 'получить список файлов',
        responses: [
            new OA\Response(
                response: 200,
                description: 'список файлов',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'result', type: 'string', example: 'successful')
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Ошибка сервера',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string')
                    ]
                )
            )
        ]
    )]
    #[Route("", "GET")]
    public function filesList($params)
    {
        $disk = $this->auth();
        $resources = $disk->getResources();
        #return new Response(200, ['result' => $_ENV['OAUTH']]);
        return new Response(200, ['result' => $resources->toObject()]);
    }

    private function auth(){
        $disk = new Disk();
        $disk->setAccessToken($_ENV['OAUTH']);
        return $disk;
    }
}
