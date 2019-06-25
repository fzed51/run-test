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

echo 'STRING';
echo PHP_EOL;
$json = json_encode('coucou');
$schem = 'string';
Assert\schemaJsonTest($schem, $json);

echo 'STRING[]';
echo PHP_EOL;
$json = json_encode(['coucou']);
$schem = ['string'];
Assert\schemaJsonTest($schem, $json);

echo 'OBJECT';
echo PHP_EOL;
$json = json_encode(['data' => 'coucou', 'other' => 4]);
$schem = ['data' => 'string', 'other' => 'int'];
Assert\schemaJsonTest($schem, $json);

echo 'Test KO';
echo PHP_EOL;
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

echo 'Test KO, props null';
echo PHP_EOL;
$json = '['
    . '{"id":256,"lettre":"A","numero":1,'
    . '"individu": null}'
    . ']';
Assert\schemaJsonTest($schemOk, $json);
try {
    Assert\schemaJsonTest($schemKo, $json);
    throw new \Exception("Doit retourner une \Assert\Exception");
} catch (\Assert\Exception $e) { }

echo 'Test KO, type incorrect';
echo PHP_EOL;
try {
    $json = json_encode(['data' => 'coucou', 'other' => 4]);
    $schem = ['data' => 'int', 'other' => 'string'];
    Assert\schemaJsonTest($schem, $json);
    throw new \Exception("Doit retourner une \Assert\Exception");
} catch (\Assert\Exception $e) { }

echo 'OBJECT props avec valeur nullable';
echo PHP_EOL;
$json = json_encode(['data' => null]);
$schem = ['data' => 'string?'];
Assert\schemaJsonTest($schem, $json);
$schem = ['data?' => 'string'];
Assert\schemaJsonTest($schem, $json);

echo 'OBJECT vide';
echo PHP_EOL;
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
