# DBi
Класс для удобной работы с Mysql
небольшая обертка к модулю [mysqli](https://www.php.net/manual/ru/book.mysqli.php)
реализующая:
* placeholder-ы
* авто соединение при первом запросе
* удобные выборки в массив

### Установка
```bash
composer require kea255/dbi
```

Добавте в конфигурационный файл вашего проекта константы с вашими данными соединения
```php
const dbhost = 'localhost';
const dbuser = 'username';
const dbpass = 'password';
const dbname = 'databaseName';
```

## Использование placeholder-ов
#### Простые, скалярные данные, плейсхолдер: ?
```php
$rows = DBi::select('SELECT * FROM tbl WHERE a=? AND b=? AND c=?', 1, 'test', null);
```
будет выполнен запрос
```sql
SELECT * FROM tbl WHERE a=1 AND b='test' AND c=NULL
```
<br/>

#### Массив, плейсхолдер: ?a
```php
$rows = DBi::select('SELECT * FROM tbl WHERE date IN(?a)', ['2006-03-02', '2012-01-02', '2022-05-01']);
```
будет выполнен запрос
```sql
SELECT * FROM tbl WHERE date IN('2006-03-02', '2012-01-02', '2022-05-01')
```
<br/>

#### Ассоциативный массив для запросов типа UPDATE
```php
DBi::query('UPDATE tbl SET ?a', ['id'=>10, 'date'=>"2006-03-02"]);
```
будет выполнен запрос
```sql
UPDATE tbl SET `id`='10', `date`='2006-03-02'
```
<br/>

#### Пример для запроса INSERT
```php
$data = ['id' => 101, 'name' => 'Rabbit', 'age' => 30];
DBi::query('INSERT INTO table(?#) VALUES(?a)', array_keys($data), array_values($data));
```
будет выполнен запрос
```sql
INSERT INTO table(`id`, `name`, `age`) VALUES(101, 'Rabbit', 30)
```
<br/>

#### Пример для запроса INSERT ON DUPLICATE KEY UPDATE
```php
$data = ['id' => 101, 'name' => 'Rabbit', 'age' => 30];
DBi::query('INSERT INTO table(?#) VALUES(?a) ON DUPLICATE KEY UPDATE ?a', array_keys($data), array_values($data), $data);
```
будет выполнен запрос
```sql
INSERT INTO table(`id`, `name`, `age`) VALUES(101, 'Rabbit', 30) ON DUPLICATE KEY UPDATE `id`='101', `name`='Rabbit', `age`='30'
```
<br/>

## Использование выборок
Выборка всего результата: select()
```php
$rows = DBi::select('SELECT Name, CountryCode FROM City');
foreach($rows as $row){
    printf("%s (%s)\n", $row["Name"], $row["CountryCode"]);
}
```
<br/>

Выборка строки: selectRow()
```php
$row = DBi::selectRow('SELECT Name, CountryCode FROM City LIMIT 1');
printf("%s (%s)\n", $row["Name"], $row["CountryCode"]);
```
<br/>

Выборка столбца: selectCol()
```php
$names = DBi::selectCol('SELECT Name FROM City');
foreach($names as $name){
    printf("%s\n", $name);
}
```
<br/>

Выборка ячейки: selectCell()
```php
$name = DBi::selectCell('SELECT Name FROM City WHERE CountryCode=?', 'RU');
printf("%s\n", $name);
```
<br/>

### Можно использовать временные таблицы
```php
DBi::query('CREATE TEMPORARY TABLE t1 SELECT Name, CountryCode FROM City');
$rows = DBi::select('SELECT * FROM t1');
foreach($rows as $row){
    printf("%s (%s)\n", $row["Name"], $row["CountryCode"]);
}
```

### Экономия памяти для больших выборок
Можно получать результат по одной строке, не загружая всю выборку в память
```php
$result = DBi::query('SELECT Name, CountryCode FROM City');
while($row = $result->fetch_assoc()){
	printf("%s (%s)\n", $row["Name"], $row["CountryCode"]);
}
$result->free();
```