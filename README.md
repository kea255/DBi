# DBi
����� ��� ������� ������ � Mysql
��������� ������� � ������ [mysqli](https://www.php.net/manual/ru/book.mysqli.php)
�����������:
* placeholder-�
* ���� ���������� ��� ������ �������
* ������� ������� � ������

### ���������
������������ dbi.class.php � ��� ������
```php
require_once 'dbi.class.php';
```

������� � ���������������� ���� ������ ������� ��������� � ������ ������� ����������
```php
const dbhost = 'localhost';
const dbuser = 'username';
const dbpass = 'password';
const dbname = 'databaseName';
```

##������������� placeholder-��
�������, ��������� ������, �����������: ?
```php
$rows = DBi::select('SELECT * FROM tbl WHERE a=? AND b=? AND c=?', 1, 'test', null);
```
����� �������� ������
```sql
SELECT * FROM tbl WHERE a=1 AND b='test' AND c=NULL
```

������, �����������: ?a
```php
$rows = DBi::select('SELECT * FROM tbl WHERE date IN(?a)', ['2006-03-02', '2012-01-02', '2022-05-01']);
```
����� �������� ������
```sql
SELECT * FROM tbl WHERE date IN('2006-03-02', '2012-01-02', '2022-05-01')
```

������������� ������ ��� �������� ���� UPDATE
```php
DBi::query('UPDATE tbl SET ?a', ['id'=>10, 'date'=>"2006-03-02"]);
```
����� �������� ������
```sql
UPDATE tbl SET `id`='10', `date`='2006-03-02'
```

������ ��� ������� INSERT
```php
$data = ['id' => 101, 'name' => 'Rabbit', 'age' => 30];
DBi::query('INSERT INTO table(?#) VALUES(?a)', array_keys($data), array_values($data));
```
����� �������� ������
```sql
INSERT INTO table(`id`, `name`, `age`) VALUES(101, 'Rabbit', 30)
```

������ ��� ������� INSERT ON DUPLICATE KEY UPDATE
```php
$data = ['id' => 101, 'name' => 'Rabbit', 'age' => 30];
DBi::query('INSERT INTO table(?#) VALUES(?a) ON DUPLICATE KEY UPDATE ?a', array_keys($data), array_values($data), $data);
```
����� �������� ������
```sql
INSERT INTO table(`id`, `name`, `age`) VALUES(101, 'Rabbit', 30) ON DUPLICATE KEY UPDATE `id`='101', `name`='Rabbit', `age`='30'
```

##������������� �������
������� ����� ����������: select()
```php
$rows = DBi::select('SELECT Name, CountryCode FROM City');
foreach($rows as $row){
    printf("%s (%s)\n", $row["Name"], $row["CountryCode"]);
}
```

������� ������: selectRow()
```php
$row = DBi::selectRow('SELECT Name, CountryCode FROM City LIMIT 1');
printf("%s (%s)\n", $row["Name"], $row["CountryCode"]);
```

������� �������: selectCol()
```php
$names = DBi::selectCol('SELECT Name FROM City');
foreach($names as $name){
    printf("%s\n", $name);
}
```

������� ������: selectCell()
```php
$name = DBi::selectCell('SELECT Name FROM City WHERE CountryCode=?', 'RU');
printf("%s\n", $name);
```

###����� ������������ ��������� �������
```php
DBi::query('CREATE TEMPORARY TABLE t1 SELECT Name, CountryCode FROM City');
$rows = DBi::select('SELECT * FROM t1');
foreach($rows as $row){
    printf("%s (%s)\n", $row["Name"], $row["CountryCode"]);
}
```

###�������� ������ ��� ������� �������
����� �������� ��������� �� ����� ������, �� �������� ��� ������� � ������
```php
$result = DBi::query('SELECT Name, CountryCode FROM City');
while($row = $result->fetch_assoc()){
	printf("%s (%s)\n", $row["Name"], $row["CountryCode"]);
}
$result->free();
}
```