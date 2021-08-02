# music_bar

Данный проект эмулирует работу музыкального бара с произвольным количеством посетителей и жанров. 
(Задание: https://docs.google.com/document/d/1Vy0dtaBDdXSeQSYR70SsSv-K67dYWFcV5w23oloPfxo/edit)

Для создания программы использовался следующий инструментарий:
- фреймворк Symfony 5.3.5;
- язык программирования PHP 7.4.3;
- набор библиотек для работы с БД Doctrine.

Для удобства работы с запросами была использована платформа "Postman".

Логика программы состоит в следующем:

1.  Открываем (создаем) бар.
2.  Приводим (создаем) посетителей.
2.  Показываем что произашло в баре.

___
## Открытие бара 

/music/bar/open

Этот запрос создаетновый бар в базе данных. Если бар уже был создан, то он не создается заново, а получает статус "открыт".
Адрес для отправки запроса:
http://127.0.0.1:8000/music/bar/open

Метод запроса: POST.
Запрос и отет в формате JSON.

Параметры запроса:

- Название бара                  - title      (string),  обязательный параметр, на данный момент, не может повторяться у разных баров.
- Вместительность бара           - capacyty   (int),     обязательный параметр.
- Репертуар бара                 - repertoire (array),   обязательный параметр.
- Количество барменов            - amountOfBartenders (int), необязательный параметр, на данный момент с ним никакой логики нет.
- Количество посетителей         - amountOfVisitors   (int), необязательный параметр, имеет дефолтное значение при открытие бара (0).
- Текущий жанр                   - currentGenre   (string), необязательный параметр, значение устанавливается по ходу работы программы.
- Посетители                     - visitors     (array), необязательный параметр, значение задается, когда приходит запрос о создании посетителей.
- Статус бара                    - status   (string), необязательный параметр, значение задается програмой.

Параметры ответа:

- Уникальный идентификатор бара  - bar_id      (int),    необязательный параметр.
- Название бара                  - bar_title    (string),    необязательный параметр.
- Статус бара                    - status      (string), необязательный параметр.
- Код ошибки                     - code    (int),    необязательный параметр.
- Сообщение ошибки               - message (string), необязательный параметр.


Пример запроса:

```json
{
    "title": "Pretty Bar",
    "capacity": 7,
    "repertoire":[
        "rock",
        "country",
        "electronic music", 
        "folk", 
        "hip hop", 
        "jazz", 
        "pop", 
        "classical music"
    ]
}
```

Пример успешного ответа:
```json
{
    "bar_id": 6,
    "bar_title": "Pretty Bar",
    "status": "open"
}
```
Пример неуспешного ответа:

```json
{
    "code": 6,
    "message": "Bar `Pretty Bar` is already opened"
}
```

___
## Создание посетителей

/music/bar/{barTitle}/visitors

Этот запрос создает новых посетителей в базе данных.
Адрес для отправки запроса:
http://127.0.0.1:8000/music/bar/{barTitle}/visitors

Метод запроса: POST.
Запрос JSON, ответ в формате тест и JSON.

Параметры запроса:

- Посетители               - visitors  (array),  обязательный параметр, содержит в себе пришетших гостей.

Параметры элементов массива visitors:

- Имя посетителя                  - name   (string),    необязательный параметр, есть дефолтное значение.
- Фамилия посетителя              - surname (string),   необязательный параметр, есть дефолтное значение.
- Количество денег у посетителя   - money (int),        обязательный параметр.
- Любимый жанр                    - genre   (string),   обязательный параметр, на данный момент, у посетителя только один любимый жанр.
- Статус посетителя               - status   (string),  необязательный параметр, значение задается програмой.


Параметры ответа:

- Текстовая информация о том, кто из посетиелей вошел в бар, а кого не пустили из за отсутствия свободного места.
- Массив уникальных идентификаторов всех посетителей, которые подошли к бару - visitor(s)_id (array), необязательный параметр.
- Статус посетителей - status (string), необязательный параметр.
- Код ошибки                     - code    (int),    необязательный параметр.
- Сообщение ошибки               - message (string), необязательный параметр.

Пример запроса:

```json
{
   "visitors": [
        {   "name": "Tom",
            "surname": "Yam",
            "money": 250,
            "genre":"rock"
        },
        {   "name": "Fo",
            "surname": "Bo",
            "money": 300,
            "genre":"jazz"
        },
        {   "name": "Ra",
            "surname": "Men",
            "money": 150,
            "genre":"rock"
        },
        {   "name": "test",
            "surname": "5",
            "money": 100,
            "genre":"country"
        },
        {   "name": "test",
            "surname": "6",
            "money": 200,
            "genre":"jazz"
        },
        {   "name": "Po",
            "surname": "Ke",
            "money": 350,
            "genre":"hip hop"
        },
        {   "name": "Po",
            "surname": "Ke",
            "money": 350,
            "genre":"hip hop"
        },
        {   "name": "Po",
            "surname": "Ke",
            "money": 0,
            "genre":"folk"
        }
    ]
}
```

Пример успешного ответа:
```json
Visitor with id `170` entered the `Pretty Bar`. 
Visitor with id `171` entered the `Pretty Bar`. 
Visitor with id `172` entered the `Pretty Bar`. 
Visitor with id `173` entered the `Pretty Bar`. 
Visitor with id `174` entered the `Pretty Bar`. 
Visitor with id `175` entered the `Pretty Bar`. 
Visitor with id `176` entered the `Pretty Bar`. 
Visitor with id `177` gone because there are no more places at the `Pretty Bar`. 
{
    "visitor(s)_id": [
        170,
        171,
        172,
        173,
        174,
        175,
        176,
        177
    ],
    "status": "came to the `Pretty Bar`"
}
```
Пример неуспешного ответа:

```json
{
    "code": 1,
    "message": "Bar `Pretty Bar` is closed"
}
```

___
## Просмотр действий, которые происходили в баре

/music/bar/{title}/WhatIshappening

Этот запрос говорит программе о том, что нужно выполнить "обработку" зашедших в бар гостей.
Адрес для отправки запроса:
http://127.0.0.1:8000/music/bar/{title}/WhatIshappening

Метод запроса: POST.
Ответ в формате тест и JSON.

Параметры ответа:

- Текстовая информация о том, что происходит в баре.
- Сообщение о прекращении "обработки" посетителей - message (string) - необязательный параметр.
- Код ошибки                     - code    (int),    необязательный параметр.
- Сообщение ошибки               - message (string), необязательный параметр.

Пример успешного ответа:
```json
Visitor with id `170` got in line to order the music. 
Visitor with id `171` got in line to order the music. 
Visitor with id `172` got in line to order the music. 
Visitor with id `173` got in line to order the music. 
Visitor with id `174` got in line to order the music. 
Visitor with id `175` got in line to order the music. 
Visitor with id `176` got in line to order the music. 
Visitor with id `170` get paid for music or drink. 
Now playing the songs in genre `rock`. And Visitor with id `170` is going to dance. 
Visitor with id `171` get paid for music or drink. 
Now playing the songs in genre `jazz`. And Visitor with id `171` is going to dance. 
Genre of music changed from `rock` to `jazz`. 
Visitor with id `170` stopped dancing and went to order some drink. 
Visitor with id `171` continues to dance. 
Visitor with id `172` continues to be in line to order drink or music. 
Visitor with id `173` continues to be in line to order drink or music. 
Visitor with id `174` get out of line to order something and going to dance. 
Visitor with id `175` continues to be in line to order drink or music. 
Visitor with id `176` continues to be in line to order drink or music. 
Visitor with id `170` get paid for music or drink. 
Visitor with id `172` get paid for music or drink. 
Now playing the songs in genre `rock`. And Visitor with id `172` is going to dance. 
Genre of music changed from `jazz` to `rock`. 
Visitor with id `170` stopped drinking and went to dance. 
Visitor with id `171` stopped dancing and went to order some drink. 
Visitor with id `172` continues to dance. 
Visitor with id `173` continues to be in line to order drink or music. 
Visitor with id `174` stopped dancing and went to order some drink. 
Visitor with id `175` continues to be in line to order drink or music. 
Visitor with id `176` continues to be in line to order drink or music. 
Visitor with id `171` get paid for music or drink. 
Visitor with id `174` get paid for music or drink. 
Visitor with id `173` get paid for music or drink. 
Now playing the songs in genre `country`. And Visitor with id `173` is going to dance. 
Genre of music changed from `rock` to `country`. 
Visitor with id `170` stopped dancing and went to order some drink. 
Visitor with id `171` continues to drink. 
Visitor with id `172` stopped dancing and went to order some drink. 
Visitor with id `173` continues to dance. 
Visitor with id `174` continues to drink. 
Visitor with id `175` continues to be in line to order drink or music. 
Visitor with id `176` continues to be in line to order drink or music. 
Visitor with id `170` get paid for music or drink. 
Visitor with id `171` get paid for music or drink. 
Visitor with id `172` get paid for music or drink. 
Visitor with id `174` get paid for music or drink. 
Visitor with id `175` get paid for music or drink. 
Now playing the songs in genre `hip hop`. And Visitor with id `175` is going to dance. 
Genre of music changed from `country` to `hip hop`. 
Visitor with id `170` continues to drink. 
Visitor with id `171` continues to drink. 
Visitor with id `172` continues to drink. 
Visitor with id `173` stopped dancing and went to order some drink. 
Visitor with id `174` continues to drink. 
Visitor with id `175` continues to dance. 
Visitor with id `176` get out of line to order something and going to dance. 
Visitor with id `170` gone because he hasn't money. 
Visitor with id `171` gone because he hasn't enough money to order the music or drink. 
Visitor with id `172` gone because he hasn't money. 
Visitor with id `173` gone because he hasn't enough money to order the music or drink. 
Visitor with id `174` gone because he hasn't money. 
Now playing the songs in genre `hip hop`. And Visitor with id `176` is going to dance. 
{
    "message": "Visitors processing has just finished"
}
```


Пример неуспешного ответа:

```json
{
    "code": 5,
    "message": "There are no visitors at the `Pretty Bar`"
}
```


___
## Получение информации о баре

/music/bar/id/{id}

/music/bar/title/{title}

Этот запрос возвращает информацию о баре по его идентификатору или названию.

Адрес для отправки запроса о выводе информации по id:
http://127.0.0.1:8000/music/bar/id/{id}

Адрес для отправки запроса о выводе информации по названию бара:
http://127.0.0.1:8000/music/bar/title/{title}

Метод запроса: GET.
Запрос и отет в формате JSON.

Параметры ответа:

- Название бара                  - title      (string),  необязательный параметр.
- Вместительность бара           - capacyty   (int),     необязательный параметр.
- Репертуар бара                 - repertoire (array),   необязательный параметр.
- Количество барменов            - amountOfBartenders (int), необязательный параметр.
- Количество посетителей         - amountOfVisitors   (int), необязательный параметр.
- Текущий жанр                   - currentGenre   (string), необязательный параметр.
- Посетители                     - visitors     (array), необязательный параметр.
- Статус бара                    - status   (string), необязательный параметр.
- Код ошибки                     - code    (int),    необязательный параметр.
- Сообщение ошибки               - message (string), необязательный параметр.

Пример успешного ответа:
```json
{
    "id": 6,
    "title": "Pretty Bar",
    "capacity": 7,
    "amountOfVisitors": 2,
    "amountOfBartenders": 1,
    "currentGenre": "hip hop",
    "status": "open",
    "repertoire": [
        "rock",
        "country",
        "electronic music",
        "folk",
        "hip hop",
        "jazz",
        "pop",
        "classical music"
    ],
    "visitors": {
        "175": {
            "id": 175,
            "name": "Po",
            "surname": "Ke",
            "money": 300,
            "status": "dancing",
            "genre": "hip hop"
        },
        "176": {
            "id": 176,
            "name": "Po",
            "surname": "Ke",
            "money": 350,
            "status": "dancing",
            "genre": "hip hop"
        }
    }
}
```
Пример неуспешного ответа:

```json
{
    "code": 5,
    "message": "Bar with title `Another Bar` not found"
}
```


___
## Получение информации о посетителях

/music/bar/visitors/all

Этот запрос возвращает информацию о всех посетителях (любых баров) из базы данных.

Адрес для отправки запроса:
http://127.0.0.1:8000/music/bar/visitors/all

Метод запроса: GET.
Запрос и отет в формате JSON.

Параметры ответа:

- Имя посетителя                  - name   (string),    необязательный параметр.
- Фамилия посетителя              - surname (string),   необязательный параметр.
- Количество денег у посетителя   - money (int),        необязательный параметр.
- Любимый жанр                    - genre   (string),   необязательный параметр.
- Статус посетителя               - status   (string),  необязательный параметр.
- Код ошибки                     - code    (int),    необязательный параметр.
- Сообщение ошибки               - message (string), необязательный параметр.


Пример успешного ответа:
```json
[
    {
        "id": 170,
        "name": "Tom",
        "surname": "Yam",
        "money": 0,
        "status": "left the bar",
        "genre": "rock"
    },
    {
        "id": 171,
        "name": "Fo",
        "surname": "Bo",
        "money": 50,
        "status": "left the bar",
        "genre": "jazz"
    },
    {
        "id": 172,
        "name": "Ra",
        "surname": "Men",
        "money": 0,
        "status": "left the bar",
        "genre": "rock"
    },
    {
        "id": 173,
        "name": "test",
        "surname": "5",
        "money": 50,
        "status": "left the bar",
        "genre": "country"
    },
    {
        "id": 174,
        "name": "test",
        "surname": "6",
        "money": 0,
        "status": "left the bar",
        "genre": "jazz"
    },
    {
        "id": 175,
        "name": "Po",
        "surname": "Ke",
        "money": 300,
        "status": "dancing",
        "genre": "hip hop"
    },
    {
        "id": 176,
        "name": "Po",
        "surname": "Ke",
        "money": 350,
        "status": "dancing",
        "genre": "hip hop"
    },
    {
        "id": 177,
        "name": "Po",
        "surname": "Ke",
        "money": 0,
        "status": "left the bar",
        "genre": "folk"
    }
]

```
Пример неуспешного ответа:

```json
{
    "code": 5,
    "message": "can`t find any visitor"
}
```


___
## Закрытие бара

/music/bar/close/{title}


Этот запрос устанавливает статусу бара значение "закрыто" и очищает массив посетителей бара.

Адрес для отправки запроса:
http://127.0.0.1:8000/music/bar/close/{title}


Метод запроса: GET.
Запрос и отет в формате JSON.

Параметры ответа:

- Уникальный идентификатор бара  - bar_id      (int),    необязательный параметр.
- Название бара                  - bar_title    (string),    необязательный параметр.
- Статус бара                    - status      (string), необязательный параметр.
- Код ошибки                     - code    (int),    необязательный параметр.
- Сообщение ошибки               - message (string), необязательный параметр.


Пример успешного ответа:
```json
{
    "bar_id": 6,
    "bar_title": "Pretty Bar",
    "status": "close",
    "amountOfVisitors": 0
}
```
Пример неуспешного ответа:

```json
{
    "code": 5,
    "message": "Bar with title `Another Bar` not found"
}
```

___
## Изменение свойств бара

/music/bar/id/{id}

/music/bar/title/{title}

Этот запрос изменяет свойства бара по идентификатору или названию бара.

Адрес для отправки запроса о изменении свойств бара по id:
http://127.0.0.1:8000/music/bar/id/{id}

Адрес для отправки запроса о изменении свойств бара по названию:
http://127.0.0.1:8000/music/bar/title/{title}

Метод запроса: PUT.
Запрос и отет в формате JSON.

Параметры запроса:

- Название бара                  - title      (string),  необязательный параметр.
- Вместительность бара           - capacyty   (int),     необязательный параметр.
- Репертуар бара                 - repertoire (array),   необязательный параметр.
- Количество барменов            - amountOfBartenders (int), необязательный параметр.
- Количество посетителей         - amountOfVisitors   (int), необязательный параметр.
- Текущий жанр                   - currentGenre   (string), необязательный параметр.
- Посетители                     - visitors     (array), необязательный параметр.
- Статус бара                    - status   (string), необязательный параметр.

Параметры ответа:

- Название бара                  - title      (string),  необязательный параметр.
- Вместительность бара           - capacyty   (int),     необязательный параметр.
- Репертуар бара                 - repertoire (array),   необязательный параметр.
- Количество барменов            - amountOfBartenders (int), необязательный параметр.
- Количество посетителей         - amountOfVisitors   (int), необязательный параметр.
- Текущий жанр                   - currentGenre   (string), необязательный параметр.
- Посетители                     - visitors     (array), необязательный параметр.
- Статус бара                    - status   (string), необязательный параметр.
- Код ошибки                     - code    (int),    необязательный параметр.
- Сообщение ошибки               - message (string), необязательный параметр.


Пример запроса:
```json
{
    "repertoire": [
        "rock",
        "country",
        "folk",
        "hip hop",
        "jazz",
        "pop"
    ]
}
```


Пример успешного ответа:
```json
{
    "id": 6,
    "title": "Pretty Bar",
    "capacity": 7,
    "amountOfVisitors": 0,
    "amountOfBartenders": 1,
    "currentGenre": "",
    "status": "close",
    "repertoire": [
        "rock",
        "country",
        "folk",
        "hip hop",
        "jazz",
        "pop"
    ],
    "visitors": []
}
```
Пример неуспешного ответа:

```json
{
    "code": 5,
    "message": "Bar with title `Another Bar` not found"
}
```
