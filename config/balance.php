<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Table name
    |--------------------------------------------------------------------------
    |
    | Table name to use to store balance history transactions.
    |
    */

    'table' => 'balance_history',
    'model' => \Batv45\Balance\Balance::class,

    /**
     * ex: 1000 or 100
     * type: int
     */
    'multiplier' => 1000

];
