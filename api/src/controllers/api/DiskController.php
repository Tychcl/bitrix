<?php
namespace Api;

use Arhitector\Yandex\Client\Exception\NotFoundException;
use Arhitector\Yandex\Disk;
use Classes\Validate;
use Core\Response;
use Core\Route;
use Exception;
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
#[OA\Tag(name: 'file', description: 'Управление файлами на диске')]
#[OA\Tag(name: 'disk', description: 'Управление диском')]
#[OA\Tag(name: 'folder', description: 'Управление папками на диске')]
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
        $disk = $this->auth();
        return new Response(200, ['result' => 'successful', 
        'disk' => $disk->toObject(['total_space', 'used_space', 'free_space'])]);
    }

    #[OA\Get(
        path: '/api/yadisk/folder',
        tags: ['folder'],
        summary: 'получить список файлов/папок в папке',
        parameters: [
            new OA\Parameter(
                name: 'path',
                description: 'Путь до папки для поиска',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string'),
                example: "/"
            )],
        responses: [
            new OA\Response(
                response: 200,
                description: 'список файлов',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'files', type: 'string', example: '{"0": {},"1": {}}'),
                        new OA\Property(property: 'disk', type: 'string', example: '{"total_space": 0,"used_space": 0,"free_space": 0}')
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
    #[Route("/folder", "GET")]
    public function filesList($params)
    {
        $path = $this->normalizeFolderPath($params['path'] ?? "");
        $disk = $this->auth();


        $folder_allowed = [
            "path",
            "type",
            "name",
            "offset"
        ];

        try 
        {
            $folder = $disk->getResource("app:$path");
        } catch (NotFoundException $ex) {
            return new Response(400, ["error" => "folder not exists"]);
        }
        
        $folder_data = [];
        foreach ($folder->items as $item) {
            $folder_data[$item->name] = $item->toObject($folder_allowed);
        }

        return new Response(200, [
            'folder' => $folder->toObject($folder_allowed),
            'folder_data' => $folder_data]);
    }

    #[OA\Post(
        path: '/api/yadisk/file',
        tags: ['file'],
        summary: 'Загрузить файл',
        parameters: [
            new OA\Parameter(
                name: 'path',
                description: 'Путь, по которому сохранить файл на Яндекс.Диске',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string'),
                example: "/"
            ),
            new OA\Parameter(
                name: 'create',
                description: 'Cоздать папку, если её нет',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'boolean', default: false)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(
                            property: 'file',
                            description: 'Загружаемый файл',
                            type: 'string',
                            format: 'binary'
                        )
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Результат',
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
    #[Route("/file", "Post")]
    public function uploadFile($params, $request)
    {
        try{
            $path = $this->normalizeFolderPath($params['path'] ?? "");
            $create = boolval($params['create'] ?? false);
            $file = $request->files['file'] ?? null;
            
            if(!$file){
                return new Response(400, ["error" => "file required"]);
            }

            $disk = $this->auth();

            try {
                $folder = $disk->getResource("app:$path");
                if(!$folder->has() & $create){
                    $folder->create();
                }
            } catch (NotFoundException $ex) {
                return new Response(400, ["error" => "folder not exists"]);
            }
            
            try {
                $dfile = $disk->getResource("app:$path".$file['name']);
                if (!$dfile->has()) {
                    $dfile->upload($file['tmp_name']);
                }
                else{
                    return new Response(400, ["error" => "file already exists"]);
                }
            } catch (NotFoundException $ex) {
                return new Response(400, ["error" => "file not exists"]);
            }
            
            return new Response(200, ['result' => 'successful']);
        }catch(Exception $ex){
            return Validate::Ex($ex);
        }
    }

    #[OA\Put(
        path: '/api/yadisk/file',
        tags: ['file'],
        summary: 'Перезаписать файл',
        parameters: [
            new OA\Parameter(
                name: 'path',
                description: 'Путь, по которому перезапсать файл на Яндекс.Диске',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string'),
                example: "/"
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(
                            property: 'file',
                            description: 'Загружаемый файл',
                            type: 'string',
                            format: 'binary'
                        )
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Результат',
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
    #[Route("/file", "Put")]
    public function rewriteFile($params, $request)
    {
        try{
            $path = $this->normalizeFolderPath($params['path'] ?? "");
            $file = $request->files['file'] ?? null;
            
            if(!$file){
                return new Response(400, ["error" => "file required"]);
            }

            $disk = $this->auth();
            
            try {
                $dfile = $disk->getResource("app:$path".$file['name']);
                if ($dfile->has()) {
                    $dfile->upload($file['tmp_name'], true);
                }
            } catch (NotFoundException $ex) {
                return new Response(400, ["error" => "file not exists"]);
            }
            
            return new Response(200, ['result' => 'successful']);
        }catch(Exception $ex){
            return Validate::Ex($ex);
        }
    }

    #[OA\Post(
        path: '/api/yadisk/folder',
        tags: ['folder'],
        summary: 'Создать папку',
        parameters: [
            new OA\Parameter(
                name: 'path',
                description: 'Путь, по которому создать папку на Яндекс.Диске',
                in: 'query',
                required: true,
                schema: new OA\Schema(type: 'string'),
                example: "/"
            ),
            new OA\Parameter(
                name: 'name',
                description: 'Название',
                in: 'query',
                required: true,
                schema: new OA\Schema(type: 'string'),
                example: "example"
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Результат',
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
    #[Route("/folder", "Post")]
    public function createFolder($params)
    {
        try{
            $path = $this->normalizeFolderPath($params['path'] ?? "");
            $name = $params['name'] ?? null;

            if(!$name){
                return new Response(400, ["error" => "folder name required"]);
            }
            
            $disk = $this->auth();

            try {
                $folder = $disk->getResource("app:$path".$name);
                if($folder->has()){
                    return new Response(400, ["error" => "folder already exists"]);
                }
            } catch (NotFoundException $ex) {
                $folder->create();
            }
            
            return new Response(200, ['result' => $folder->toObject()]);
        }catch(Exception $ex){
            return Validate::Ex($ex);
        }
    }

    #[OA\Get(
        path: '/api/yadisk/download',
        tags: ['disk'],
        summary: 'Скачать файл/папку',
        parameters: [
            new OA\Parameter(
                name: 'path',
                description: 'Путь до папки',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string'),
                example: "/"
            ),new OA\Parameter(
                name: 'name',
                description: 'Название файла',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string'),
                example: "example.txt"
            )],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Ссылка на скачивание',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'result', type: 'string', example: 'link')
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
    #[Route("/download", "GET")]
    public function fileDownload($params)
    {
        $path = $this->normalizeFolderPath($params['path'] ?? "");
        $name = $params['name'] ?? null;

        $disk = $this->auth();
        try 
        {
            $resource = $disk->getResource("app:$path".$name);
        } catch (NotFoundException $ex) {
            return new Response(400, ["error" => "resource not exists"]);
        }

        //$tmp_name =  $resource->isDir() ?  $resource->name.'.zip' : $resource->name;
        //$path_save = __DIR__.'/'.$tmp_name;
        
        try{
            $link = $resource->getLink();
            //if($resource->download($path_save, true)) {
            //    header('Content-Type: application/octet-stream');
            //    header('Content-Disposition: attachment; filename="' . $tmp_name . '"');
            //    header('Content-Length: ' . filesize($path_save));
            //    header('Pragma: no-cache');
            //    header('Expires: 0');
            //    readfile($path_save);
            //    unlink($path_save);
            //    exit;
            //} else {
            //    return new Response(500, ['error' => 'Ошибка загрузки файла']);
            //}
        }catch(\Throwable $ex){
            return new Response(500, ['error' => get_class($ex)]);
        }
        return new Response(200, ['result' => $link]);
    }

    #[OA\delete(
        path: '/api/yadisk/delete',
        tags: ['disk'],
        summary: 'Удалить файл / папку',
        parameters: [
            new OA\Parameter(
                name: 'path',
                description: 'Путь до файла',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string'),
                example: "/"
            ),new OA\Parameter(
                name: 'name',
                description: 'Название файла',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string'),
                example: "example.txt"
            ),new OA\Parameter(
                name: 'permamently',
                description: 'Удалить без переноса в корзину',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'bool'),
                example: "false"
            )],
        responses: [
            new OA\Response(
                response: 200,
                description: 'результат',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'result', type: 'string')
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
    #[Route("/delete", "delete")]
    public function fileDelete($params)
    {
        $path = $this->normalizeFolderPath($params['path'] ?? "");
        $name = $params['name'] ?? null;
        $perm = boolval($params['permamently'] ?? false);

        $disk = $this->auth();
        try 
        {
            $resource = $disk->getResource("app:$path".$name);
        } catch (NotFoundException $ex) {
            return new Response(400, ["error" => "resource not exists"]);
        }
        
        try{
            if($resource->delete($perm)){
                return new Response(200, ['result' => 'successful']);
            }
        }catch(\Throwable $ex){
            return new Response(500, ['error' => get_class($ex)]);
        }
    }

    private function auth(){
        $disk = new Disk();
        $disk->setAccessToken($_ENV['OAUTH']);
        return $disk;
    }

    private function normalizeFolderPath($path)
    {
        $path = trim($path);
        if ($path === '' || $path === '/') {
            return '/';
        }
        $path = trim($path, '/');
        return '/' . $path . '/';
    }
}
