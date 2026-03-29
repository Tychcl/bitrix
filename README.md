# ПОЛУЧИТЬ OAUTH
https://oauth.yandex.ru/authorize?response_type=token&client_id=<идентификатор приложения>

# затестить
После запуска перейти по `http://<домен>/swagger.html`
Должно быть 8 запросов, если не все, то в консоли открытой в папке `api` прописать `./vendor/bin/openapi --debug .\src\controllers\api\ -o public/openapi.json`
