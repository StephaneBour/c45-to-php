# c45-to-php
Converted the C4.5 tree in php script


## How to use

First : Create your file.data and your file.names 

Second :

```
    include(__DIR__ . '/C45.class.php');

    $c45 = new C45('file');
    $c45->parse();

    // See the script
    echo $c45->php;
```

## Change my local binary of c4.5


```
    $c45->path = '/usr/local/bin/c4.5';
```