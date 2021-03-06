<?php

use Uspdev\Replicado\Posgraduacao;

$ns = 'Uspdev\Replicado\Posgraduacao';
$metodo = 'orientadores';

$codare = empty($codare) ? null : $codare;

echo "Método Posgraduacao::$metodo(codare=$codare) => ";

testa_existe_metodo([$ns, $metodo]);

if (empty($codare)) {
    echo red('parâmetro obrigatório não fornecido') . PHP_EOL;
    return;
}

$res = Posgraduacao::$metodo($codare);
echo count($res);
echo green(' OK') . PHP_EOL;
