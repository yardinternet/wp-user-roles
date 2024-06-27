<?php

declare(strict_types=1);

use Yard\SkeletonPackage\Facades\Example;

it('can retrieve a random inspirational quote', function () {
    $quote = Example::getQuote();

    expect($quote)->tobe('For every Sage there is an Acorn.');
});
