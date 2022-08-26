<?php

namespace SDamian\Tests\Larasort\Traits;

use Illuminate\Support\Facades\Request;

trait ForAllTestsTrait
{
    private function verifyInAllTests(): void
    {
        $this->assertSame('asc', config('larasort.default_order'));

        $this->assertFalse(Request::has('orderby')); // on vérifie qu'il n'existe pas
        $this->assertFalse(Request::has('order')); // on vérifie qu'il n'existe pas
    }
}
