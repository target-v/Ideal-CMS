Ideal CMS v. 2.0b3
=========

Система управления контентом с открытым исходным кодом, написанная на PHP.

Используемые технологии и продукты:

* PHP 5.3+,
* MySQL 4+, 
* MVC, 
* PSR-0, PSR-1, PSR-2
* Twig, 
* jQuery,
* Twitter Bootstrap 3,
* CKEditor,
* CKFinder, 
* FirePHP.

Все подробности на сайте [idealcms.ru](http://idealcms.ru/)

Версия 2.0b3
---
1. При обновлении CMS и модулей могут выполнятся php и sql скрипты
2. Внедрение нового класса доступа к БД, расширяющего mysqli и с кешированием через memcached
3. Завершение перевода работы с картой сайта через админку

Версия 2.0b2
---
1. Показ миниатюры картинки для поля Ideal_Image
2. Добавлена новая сущность Medium
3. Обновлён FirePHP
4. Добавлено поле Ideal_SelectMulti
5. Исправления в карте сайта (обработка ссылок tel, многострочных html-комментариев)
6. Исправлена страница установки CMS для работы под Twi Bootstrap 3 и сделана двухколоночная вёрстка
7. Регулярные выражения для исключения URL в html-карте сайта
8. Исправлена отправка писем с разными типами вложений
9. Работа с картой сайта через админку
10. Исправлена проблема с экранированием слэшей и кавычек в Ideal_Area
11. Обновление CKEditor до версии 4.4.2
12. Отображение на сайте скрытой страницы для авторизированных в админку пользователей

Версия 2.0c
---
1. Обновление jquery-плагина datetimepicker до версии 3.0
2. FIX: определение кол-ва элементов на странице
3. FIX: проверка наличия кастомных и модульных папок Template в виде таблиц в базе
4. FIX: размер модального окна в админке при изменении размера окна браузера
5. FIX: получение default значения
6. __ADD: Новый тип поля Ideal_Integer__
7. FIX: фильтр для toolbar в админке
8. Новая вёрстка шаблона front-end под Twitter Bootstrap 3

Версия 2.0b
---
1. FIX: листалка в админке в стиле Twi 3
2. FIX: доработка редактирования редиректов под Twi 3
3. FIX: доработка создания резервных копий БД под Twi 3
4. Обновление Twitter Bootstrap до версии 3.1.1
5. FIX: Исправлена проблема с автоматической генерацией url
6. ADD: вкладки в настройках в админке

Версия 2.0a
---
1. __Обновление Twitter Bootstrap до версии 3__
2. Изменения в админской части для перехода на Bootstrap 3

Переход на версию 1.0
---

1. Во всех структурах поле structure_path изменено на prev_structure и содержит
ID родительской структуры и ID родительского элемента в этой структуре.

2. Изменён принцип роутинга. Теперь для вложенных структур метод detectPageByUrl
вызывается не из роутера, а из родительской структуры. Что даёт возможность
правильно обрабатывать вложенный структуры с элементами is_skip.

3. Изменён корневой .htacces, теперь адрес страницы не передаётся в GET-переменной,
а берётся в роутере из `$_SERVER['REQUEST_URI']`.

4. Переменная модели object переименована в pageData и сделана protected, а также
переименованы соответствующие методы.

5. Определение 404-ошибки перенесено из роутера в методы detectPageBy* модели.
В этих методах должны инициализироваться свойства класса path и is404, а сами
методы возвращают либо свой объект (`$this`), либо объект вложенной модели. Для
404 ошибки добавлен специальный шаблон 404.twig и экшен error404Action в контроллерах.