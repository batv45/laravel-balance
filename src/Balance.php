<?php

namespace Batv45\Balance;

use DB;
use Illuminate\Database\Eloquent\Model;

class Balance extends Model
{
    protected $sumBalanceWithPaginate = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'balanceable_type',
        'balanceable_id',
        'amount',
        'referenceable_type',
        'referenceable_id',
        'description',
    ];

    /**
     * Balance constructor.
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('balance.table', 'balance_history'));
    }

    public function scopeSumBalance($query): void
    {
        DB::statement('SET @varBalance = 0');
        $query->select(DB::raw('*, @varBalance := @varBalance + (`amount`) `balance`'));
        if( $this->sumBalanceWithPaginate ){
            $query->offset(0)->limit(10); // for paginate
        }
    }

    /**
     * Get the balance amount transformed to currency.
     *
     * @return float|int
     */
    public function getAmountAttribute()
    {
        return $this->attributes['amount'];
    }

    /**
     * Get the parent of the balance record.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function balanceable()
    {
        return $this->morphTo();
    }

    /**
     * Obtain the model for which the balance sheet movement was made
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function referenceable()
    {
        return $this->morphTo();
    }

    protected $casts = [
        'amount' => 'integer'
    ];
}
