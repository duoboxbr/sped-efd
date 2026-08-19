<?php

use NFePHP\EFD\Blocks\Contribuicoes\BlockC as ContribuicoesBlockC;
use NFePHP\EFD\Blocks\ICMSIPI\Block0;

require __DIR__ . '/../vendor/autoload.php';

/**
 * Testa o suporte a CNPJ alfanumérico (novo formato da Receita Federal, com
 * letras nos 12 primeiros caracteres) nos registros do SPED EFD
 */

$falhas = 0;

function testar(string $nome, callable $fn, bool $esperaOk): int
{
    try {
        $fn();
        $ok = $esperaOk;
        $detalhe = $ok ? '' : 'deveria ter falhado mas passou';
    } catch (\Throwable $e) {
        $ok = !$esperaOk;
        $detalhe = $ok ? '' : $e->getMessage();
    }

    printf("[%s] %s%s\n", $ok ? 'OK' : 'FALHOU', $nome, $detalhe ? " ($detalhe)" : '');

    return $ok ? 0 : 1;
}

$falhas += testar('C010 (Element puro): CNPJ numérico', function () {
    $std = (object)['cnpj' => '11222333000181', 'ind_escri' => '1'];
    $el = new \NFePHP\EFD\Elements\Contribuicoes\C010($std);
    if (!empty($el->errors)) {
        throw new \Exception(implode('; ', $el->errors));
    }
}, true);

$falhas += testar('C010 (Element puro): CNPJ alfanumérico', function () {
    $std = (object)['cnpj' => '12ABC34501DE35', 'ind_escri' => '1'];
    $el = new \NFePHP\EFD\Elements\Contribuicoes\C010($std);
    if (!empty($el->errors)) {
        throw new \Exception(implode('; ', $el->errors));
    }
}, true);

$falhas += testar('C010 (Element puro): CNPJ alfanumérico minúsculo (deve falhar)', function () {
    $std = (object)['cnpj' => '12abc34501de35', 'ind_escri' => '1'];
    $el = new \NFePHP\EFD\Elements\Contribuicoes\C010($std);
    if (!empty($el->errors)) {
        throw new \Exception(implode('; ', $el->errors));
    }
}, false);

$falhas += testar('BlockC->c010() (com vigência/JSON): CNPJ numérico', function () {
    $block = new ContribuicoesBlockC();
    $block->c010((object)['cnpj' => '11222333000181', 'ind_escri' => '1']);
    if (!empty($block->errors)) {
        throw new \Exception(implode('; ', $block->errors));
    }
}, true);

$falhas += testar('BlockC->c010() (com vigência/JSON): CNPJ alfanumérico', function () {
    $block = new ContribuicoesBlockC();
    $block->c010((object)['cnpj' => '12ABC34501DE35', 'ind_escri' => '1']);
    if (!empty($block->errors)) {
        throw new \Exception(implode('; ', $block->errors));
    }
}, true);

$falhas += testar('Block0->z0000() (com vigência/JSON): CNPJ alfanumérico', function () {
    $block = new Block0();
    $block->z0000((object)[
        'cod_ver'     => '017',
        'cod_fin'     => '0',
        'dt_ini'      => '01012026',
        'dt_fin'      => '31012026',
        'nome'        => 'EMPRESA TESTE',
        'cnpj'        => '12ABC34501DE35',
        'uf'          => 'PR',
        'ie'          => '1234567890',
        'cod_mun'     => '4104808',
        'im'          => '',
        'suframa'     => '',
        'ind_perfil'  => 'A',
        'ind_ativ'    => '0',
    ]);
    if (!empty($block->errors)) {
        throw new \Exception(implode('; ', $block->errors));
    }
}, true);

echo $falhas === 0
    ? "Todos os casos passaram." . PHP_EOL
    : "{$falhas} caso(s) falharam." . PHP_EOL;

exit($falhas === 0 ? 0 : 1);
