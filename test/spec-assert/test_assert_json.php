<?php
declare (strict_types = 1);
/**
 * User: Fabien Sanchez
 * Date: 12/03/2019
 * Time: 11:47
 */

echo 'execution de : ' . __FILE__;
echo PHP_EOL;

echo 'Test de Assert\schemaJsonTest';
echo PHP_EOL;
$json = json_encode('coucou');
$schem = 'string';
Assert\schemaJsonTest($schem, $json);

$json = json_encode(['coucou']);
$schem = ['string'];
Assert\schemaJsonTest($schem, $json);

$json = json_encode(['data' => 'coucou', 'other' => 4]);
$schem = ['data' => 'string', 'other' => 'int'];
Assert\schemaJsonTest($schem, $json);

$schemOk = [
    [
        'id' => 'int', 'lettre' => 'str', 'numero' => 'int',
        'individu?' => ['id' => 'int', 'nom?' => 'str', 'prenom' => 'str']
    ]
];
$schemKo = [
    [
        'id' => 'int', 'lettre' => 'str', 'numero' => 'int',
        'individu' => ['id' => 'int', 'nom?' => 'str', 'prenom' => 'str']
    ]
];

$json = '['
    . '{"id":256,"lettre":"A","numero":1}'
    . ']';
Assert\schemaJsonTest($schemOk, $json);
try {
    Assert\schemaJsonTest($schemKo, $json);
    throw new \Exception("Doit retourner une \Assert\Exception");
} catch (\Assert\Exception $e) { }


$json = '['
    . '{"id":256,"lettre":"A","numero":1,'
    . '"individu": null}'
    . ']';
Assert\schemaJsonTest($schemOk, $json);
try {
    Assert\schemaJsonTest($schemKo, $json);
    throw new \Exception("Doit retourner une \Assert\Exception");
} catch (\Assert\Exception $e) { }

try {
    $json = json_encode(['data' => 'coucou', 'other' => 4]);
    $schem = ['data' => 'int', 'other' => 'string'];
    Assert\schemaJsonTest($schem, $json);
    throw new \Exception("Doit retourner une \Assert\Exception");
} catch (\Assert\Exception $e) {
    // echo $e->getMessage();
}


try {
    $json = json_encode((object)[
        'a' => (object)[]
    ]);
    $schem = [
        'a' => [
            'id?' => 'int'
        ]
    ];
    $schem2 = ['a' => 'object'];
    $schem3 = ['b?' => 'object'];
    Assert\schemaJsonTest($schem, $json);
    Assert\schemaJsonTest($schem2, $json);
    Assert\schemaJsonTest($schem3, $json);
    throw new \Exception("Doit retourner une \Assert\Exception");
} catch (\Assert\Exception $e) {
    // echo $e->getMessage();
}
