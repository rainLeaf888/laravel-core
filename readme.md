pear install PHP_CodeSniffer

1, phpcs --standard=PSR2 --report=csv --report-file=phpcs.csv --tab-width=4 --encoding=utf-8 --extensions=inc,php app/*

解释： 解析标准是PSR2 输出格式为csv 输出到文件phpcs.csv 检测tab长度为4  只解析app下面，编码是utf-8的文件，后缀是inc和php文件

2, phpcbf --standard=PSR2 --tab-width=4 --encoding=utf-8 --extensions=inc,php app/*

解释：按照规则自动处理，只是简单的添加和删除空格。

3, 创建模块
php artisan make:module Business


