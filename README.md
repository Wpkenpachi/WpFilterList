# FilterList

# Instalando

## Composer
```shell
$ composer require wpkenpachi/wpfilterlist
```

## Configurando config/app.php
```php
'providers' => [
    ...,
    Wpkenpachi\Wpfilterlist\WpFilterListProvider::class,
    ...
]
```
# Usando

## Metodos

- boot( array $array ) ;
```php
    // - Recebe uma lista de arrays, ex:

    $array = 
    [
        [
                'id'        => 1,
                'isPaid'    => 1,
                'isNew'     => 0,
                'status'    => 0
            ],
            [
                'id'        => 2,
                'isPaid'    => 1,
                'isNew'     => 0,
                'status'    => 1
            ],
            [
                'id'        => 3,
                'isPaid'    => 1,
                'isNew'     => 1,
                'status'    => 1
            ]
    ];

    $filtrados = Wpkenpachi\Wpfilterlist\FilterList::boot( $array );

```


- agrupamentos ( array $array ) ;
```php
// Recebe como parâmetro um array de arrays, ex:

//    - Aqui estamos agrupando todos os arrays que contenham os resultados isPaid = 1 e isNew = 1

    $grupos =
    [
        ['isPaid' => 1, 'isNew' => 1]
    ];

// >>> OU <<<

// Caso o parametro de agrupamento seja um só, é possível também fazer

//     - Aqui temos 2 grupos, o primeiro agrupando os resultados
    $grupos =
    [
        'isNew' => 1,
        'isPaid' => 1
    ];   


    $filtrados = Wpkenpachi\Wpfilterlist\FilterLis::boot( $array )->agrupamentos( $grupos ) ;

```

- ordenamentos ( array $array ) ;
```php
// Recebe um array como parâmetro, ex:
//    - A 'ordem' definida para cada grupo segue a mesma ordenação em que foram declarados
//    os grupos.  Então...

    Se $grupo = [
        ['isNew' => 1, 'isPaid' => 1], // grupo 1
        ['isNew' => 0, 'isPaid' => 1] // grupo 2
    ];

// Valores suportados para ordens [ 'asc', 'desc' ] do menor para o maior, do maior para o menor respectivamente

    $ordens = [
        'status' => 'asc', // a ordenação do grupo 1 é crescente
        'status' => 'desc' // a ordenação do grupo 2 é decrescente
    ];

    $filtrados = Wpkenpachi\Wpfilterlist\FilterLis::boot( $array )->agrupamentos( $grupos )->ordenamentos( $ordens ) ;

```

- get ( ) ;
```php
// Método usado para retornar os resultados
    $filtrados = Wpkenpachi\Wpfilterlist\FilterLis::boot( $array )->agrupamentos( $grupos )->ordenamentos( $ordens )->get();
```
