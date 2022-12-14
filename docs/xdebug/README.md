# Xdebug

��������: ������ ���������� ������� � ��� �� ������ �������, ����� get-�������� ��� ������ �������. 

�������: `xdebug.start_with_request=yes`

## ��������� PhpStorm

[�������� ������������](https://git.t-dir.com/support/techdir.stuff/-/tree/master/manuals/debug/xdebug#%D0%BD%D0%B0%D1%81%D1%82%D1%80%D0%BE%D0%B9%D0%BA%D0%B0), �� �����������:
1. ��������� ���� ��� ������� File -> Settings (�������� ����). � �������� Can accept external connections;
2. � php.ini `xdebug.start_with_request=yes`
3. ��� ��������� Debug Configurations � PhpStorm ����� �������� ����� ��������� ����� (index.php � bitrix/urlrewrite.php).

php.ini
```
xdebug.mode=debug;
xdebug.client_port=9XXX;
xdebug.start_with_request=yes
```

## ����� �������

![xdebug start](xdebug-start.png)

��� ������ ������� ������� �� Play � ����� � �������. ��� ������� �������� ���� �External connections�, �������� ��������� ���� � ������ � �������� ����������� Deployment.