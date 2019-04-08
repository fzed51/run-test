<?php
declare(strict_types=1);
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

try {
    $json = json_encode(['data' => 'coucou', 'other' => 4]);
    $schem = ['data' => 'int', 'other' => 'string'];
    Assert\schemaJsonTest($schem, $json);
    throw \Exception("Doit retourner une \Assert\Exception");
} catch (\Assert\Exception $e) {
    // echo $e->getMessage();
}
