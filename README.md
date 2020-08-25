# Laravel MyTaskManager
### Техническое задание
**Описание задачи:**

Реализовать микросервисное приложение трекера задач с управлением задачами через JSON API. Можно использовать любой из современных фреймворков.

**Приложение должно содержать:**
1) Задачи
2) Список пользователей

**Возможные действия через API:**
1) Создание/удаление/редактирование задачи
2) Создание/удаление/редактирование пользователя
3) Поиск задач по фильтру с постраничной навигацией

**Технические требования**
- Приложение должно быть написано на PHP 7 или выше
- API должно быть написано по спецификации https://jsonapi.org/
- Весь код должен быть прокомментирован в стиле PHPDocumentor'a.
- Использовать любую реляционную БД (MySQL, PostgreSQL)
- Результат задания должен быть выложен на github и должна быть инструкция по запуску проекта. Также необходимо пояснить, сколько на каждую часть проекта ушло времени
- Никакого фронта не должно быть. Приложение должно работать только через JSON API.

**Плюсом будут:**
- Использование фреймворка Symfony или Laravel
- Соответствие стиля кода PSR-12
- Покрытие кода unit, функциональными и интеграционными тестами
- Работа с БД через миграции
- Использование ElasticSearch
- Использовать docker-compose для сборки приложения


### Планирование проекта

| № п./п. | Задачи  | Время выполнения (мин.)|
| ------------- | ------------- | ------------- |
| 1 | Планирование проекта (задачи проекта, структура таблиц)  | 30 |
| 2 | Подготовка рабочей среды (конфигурация apache, создание бд, создание проекта, настройка id)  | 25 |
| 3 | Создание репозитория, экспорт на github   | 5 |
| 4 | Создание миграций, фабрик, сидеров | 15 |
| 5 | Авторизация (регистрация пользователя, получение токена) | 240 |
| 6 | Списки задач (проекты) (создание, изменение, редактирование) | 300 |
| 7 | Задачи (создание, изменение, редактирование) | 60 |
| 8 | Отношения (пользователи, проекты, задачи) | 15 |
| 9 | Фильрация и вывод | 180 |
| 10 | Поиск и ElasticSearch (индексация/поиск/удаление) | 480 |
| 11 | Описание проекта в Readme 


## Installation
#### 1. Git Clone
```sh
$ git clone https://github.com/evgeniizab/laravel.mytm.git
$ cd laravel.mytm
$ composer install
```
#### 2. Database

Copy .env.example to .env
```sh
$ cp .env.example .env
```
Edit .env
```sh
DB_CONNECTION=mysql
DB_HOST=XXXX
DB_PORT=3306
DB_DATABASE=XXXX
DB_USERNAME=XXXX
DB_PASSWORD=XXXX

ELASTICSEARCH_HOST=localhost
ELASTICSEARCH_PORT=9200
```
Create the database before run artisan command.
```sh
$ php artisan migrate
```
Generate your application encryption key:
```sh
$ php artisan key:generate
```
Run the commands necessary to prepare Passport for use:
```sh
$ php artisan passport:install
```


#### 3. Run tests (27 tests)
```sh
$ ./vendor/bin/phpunit 
```

#### 4. Работа с приложением через Postman
```sh
Для начала необходимо обнулить базу и выполнить migrate --seed
$ php artisan db:wipe
$ php artisan migrate --seed
$ php artisan passport:install
```
Use: a@a.ru 12345678


#### Регистрация
```
TEST$ ./vendor/bin/phpunit --filter test_user_can_signup ./tests/Feature/UserTest.php
```
Для регистрации пользователя необходимо выполнить POST запрос с параметрами: 
name, email, password, password_c по адресу /api/v1/signup

![регистрация](./public/img/signup.png)

Если ввели существующий email:

```
{
    "data": {
        "errors": {
            "code": 422,
            "title": "The user can't be created",
            "detail": "The user with this email is already exists"
        }
    }
}
```
#### Авторизация
Для авторизации необходимо выполнить POST запрос с параметрами:email, password по адресу /api/v1/signin
```
TEST$ ./vendor/bin/phpunit --filter test_user_can_signin ./tests/Feature/UserTest.php
```
![регистрация](./public/img/signin.png)

Если логин или пароль не верный то получим:
```
{
    "data": {
        "type": "user",
        "status": "error",
        "attributes": "Unauthorized Access"
    }
}
```

Информация о пользователе

```
TEST$ ./vendor/bin/phpunit --filter test_user_can_get_info ./tests/Feature/UserTest.php
```

#### Выход из системы
```
TEST$ ./vendor/bin/phpunit --filter test_user_can_signout ./tests/Feature/UserTest.php
```
![регистрация](./public/img/signout.png)

Если не передали верный токен

```
{
    "errors": {
        "code": 403,
        "title": "User not auth",
        "detail": "Route only for auth users"
    }
}
```
#### Проекты 
| Параметры | Описание |
| ------------- | ------------- | 
| title | Название проекта. От 5 до 300 символов (обязательное поле)
| body |    Описание проекта. От 10 до 800 символов.
| deadline | Планируемая дата завершения проекта. Пример: 2020-08-24 16:39:12
| status | Статус проекта. Доступен только при обновлении проекта. 1 - Проект создан, 2 - Проект выполняется, 3 - Проект остановлен, 4 - проект завершен


#### Добавление проекта (список задач)
(post) /api/v1/projects
```
TEST$ ./vendor/bin/phpunit --filter test_user_can_add_project ./tests/Feature/UserTest.php
```
| Параметры | 
| ------------- | 
| title | 
| body |
| deadline |
![добавить проект](./public/img/project-add2.png)

#### Обновление проекта
(patch) /api/v1/projects/{project_id}
```
TEST$ ./vendor/bin/phpunit --filter test_user_can_update_project ./tests/Feature/UserTest.php
```

![обновить проект](./public/img/projects-show.png)

#### Информация по проекту
(get) /api/v1/projects/{project_id}

```
TEST$ ./vendor/bin/phpunit --filter test_user_can_update_project ./tests/Feature/UserTest.php
```

![обновить проект](./public/img/projects-show.png)

#### Информация по проектам
(get) /api/v1/projects/

| Параметры | Описание |
| ------------- | ------------- | 
| user_id | Название проекта. От 5 до 300 символов (обязательное поле)
| body |    Описание проекта. От 10 до 800 символов.
| deadline | Планируемая дата завершения проекта. Пример: 2020-08-24 16:39:12
| status | Статус проекта. Доступен только при обновлении проекта. 1 - Проект создан, 2 - Проект выполняется, 3 - Проект остановлен, 4 - проект завершен

      
#### Удаление проекта
```
TEST$ ./vendor/bin/phpunit --filter test_user_can_destroy_project ./tests/Feature/UserTest.php
```
![удалить проект](./public/img/project-del.png)
Если в проекте есть хоть одна задача, то его невозможно удалить

#### Удаление проекта со всеми задачами
(delete) /api/v1/tasks/{id}/kill
```
TEST$ ./vendor/bin/phpunit --filter test_user_can_destroy_project_with_tasks ./tests/Feature/TaskTest.php
```
Сообщение при удалении проекта со всеми задачами
```
{
    "data": {
        "message": "Проект успешно удален вместо со всеми задачами!"
    },
    "links": {
        "self": "http://api.ez:8088/api/v1/projects"
    }
}
```



### Задачи
| Параметры | Описание |
| ------------- | ------------- | 
| project_id | Задачу можно создать только в рамках пректа (списка) (обязательное поле)
| title | Название проекта. От 5 до 300 символов (обязательное поле)
| body |    Описание проекта. От 10 до 800 символов.
| deadline | Планируемая дата завершения проекта. Пример: 2020-08-24 16:39:12
| status | Статус проекта. Доступен только при обновлении проекта. 1 - Проект создан, 2 - Проект выполняется, 3 - Проект остановлен, 4 - проект завершен

#### Добавление задачи
(post) /api/v1/tasks/
```
TEST$ ./vendor/bin/phpunit --filter test_user_can_add_task ./tests/Feature/TaskTest.php
```
![удалить проект](./public/img/task-add.png)

#### Обновление задачи
(patch) /api/v1/tasks/{id}
```
TEST$ ./vendor/bin/phpunit --filter test_user_can_update_task ./tests/Feature/TaskTest.php
```
![удалить проект](./public/img/tasks-update.png)

#### Удаление задачи
(delete) /api/v1/tasks/{id}
```
TEST$ ./vendor/bin/phpunit --filter test_user_can_destroy_task ./tests/Feature/TaskTest.php
```

### Фильтры
 Параметры | Описание |
| ------------- | ------------- | 
| id | 
| user_id | 

| title | 
| body | 
| status | 
| deadline | 
| created_at |   
| updated_at | обновленные от 

| projects | Вместе с проектами 
| tasks | Вместе с задачами 
| order_by | сортировать по колонке 
| order_dir | asc, desc
| paginate |  (по умолчанию 5)

```
TEST$ ./vendor/bin/phpunit --filter test_user_can_get_users_with_filters ./tests/Feature/UserTest.php
```
test_user_can_projects_with_filters
test_user_can_tasks_with_filters

Example
http://api.ez:8088/api/v1/users?order_dir=desc&paginate=10&projects=13&tasks=3&id=1011
```
{
    "data": [
        {
            "data": {
                "type": "users",
                "id": 1011,
                "attributes": {
                    "name": "Jessy Walter",
                    "email": "ykonopelski@example.com",
                    "created_at": "2020-08-25 00:40:13",
                    "updated_at": "2020-08-25 00:40:13",
                    "projects": [
                        {
                            "data": {
                                "type": "project",
                                "id": 439,
                                "attributes": {
                                    "status": 1,
                                    "title": "Blanditiis rerum excepturi ea.",
                                    "body": "Et minima deleniti placeat vitae. Error alias blanditiis exercitationem placeat omnis quidem ut.",
                                    "deadline": "2020-08-25 00:40:13",
                                    "created_at": "2020-08-25 00:40:13",
                                    "updated_at": "2020-08-25 00:40:13"
                                },
                                "relationships": {
                                    "user": {
                                        "data": {
                                            "type": "user",
                                            "user_id": 1011
                                        },
                                        "links": {
                                            "self": "http://api.ez:8088/api/v1/users/1011"
                                        }
                                    }
                                },
                                "links": {
                                    "self": "http://api.ez:8088/api/v1/projects/439"
                                }
                            }
                        }
                    ],
                    "tasks": [
                        {
                            "data": {
                                "type": "task",
                                "id": 95,
                                "attributes": {
                                    "status": 1,
                                    "title": "Suscipit esse vel est mollitia.",
                                    "body": "Id alias repudiandae harum nesciunt aliquam nulla ut. Iste aliquam recusandae voluptatem quas omnis. Aliquam occaecati impedit dicta libero iste dolores.",
                                    "deadline": "2020-08-25 00:40:13",
                                    "created_at": "2020-08-25 00:40:13",
                                    "updated_at": "2020-08-25 00:40:13"
                                },
                                "relationships": {
                                    "user": {
                                        "data": {
                                            "user_id": 1011
                                        },
                                        "links": {
                                            "self": "http://api.ez:8088/api/v1/users/1011"
                                        }
                                    },
                                    "project": {
                                        "data": {
                                            "project_id": 439
                                        },
                                        "links": {
                                            "self": "http://api.ez:8088/api/v1/projects/439"
                                        }
                                    }
                                }
                            },
                            "links": {
                                "self": "http://api.ez:8088/api/v1/tasks/95"
                            }
                        }
                    ]
                }
            },
            "links": {
                "self": "http://api.ez:8088/api/v1/users/1011"
            }
        }
    ],
    "links": {
        "first": "http://api.ez:8088/api/v1/users?page=1",
        "last": "http://api.ez:8088/api/v1/users?page=1",
        "prev": null,
        "next": null
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 1,
        "path": "http://api.ez:8088/api/v1/users",
        "per_page": "10",
        "to": 1,
        "total": 1
    }
}
```

####Elasticserch
Сначала ПОСТ

http://api.ez:8088/api/v1/search?ser=8765&paginate=4&page=1&order_by=_id&order_dir=asc
```
TEST$ ./vendor/bin/phpunit --filter test_user_can_search_with_filters ./tests/Feature/ElasticTest.php
```

![поиск](./public/img/ess.png)
