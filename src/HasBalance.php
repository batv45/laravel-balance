<?php

namespace Batv45\Balance;

use Cknow\Money\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

trait HasBalance
{
    /**
     * Get the model's balance amount.
     *
     * @return float|int
     */
    public function syncBalanceHistory()
    {
        $totalBalance = $this->balanceHistory()->sum('amount');
        $this->balance = $totalBalance;
        $this->save();
    }

    /**
     * Get the model's balance amount.
     *
     * @return int
     */
    public function getIntBalanceAttribute()
    {
        return (int) $this->balance;
    }

    /**
     * Increase the balance amount.
     *
     * @param  int $amount
     * @param  array $parameters
     * @return \Batv45\Balance\Balance
     */
    public function increaseBalance(float $amount, string $description = null, Model $referenceable = null)
    {
        $arr = [];

        if( $description != null)
            $arr = Arr::add( $arr, 'description', $description);
        if( $referenceable != null)
            $arr = Arr::add( $arr, 'reference', $referenceable);

        return $this->createBalanceHistory($amount, $arr);
    }

    /**
     * Decrease the balance amount
     *
     * @param  int $amount
     * @param  array $parameters
     * @return \Batv45\Balance\Balance
     */
    public function decreaseBalance(float $amount, string $description = null, Model $referenceable = null)
    {
        $arr = [];

        if( $description != null)
            $arr = Arr::add( $arr, 'description', $description);
        if( $referenceable != null)
            $arr = Arr::add( $arr, 'reference', $referenceable);

        return $this->createBalanceHistory(-1 * abs($amount), $arr);
    }

    /**
     * Modify the balance sheet with the given value.
     *
     * @param  int $amount
     * @param  array $parameters
     * @return \Batv45\Balance\Balance
     */
    public function modifyBalance(float $amount, array $parameters = [])
    {
        return $this->createBalanceHistory($amount, $parameters);
    }

    /**
     * Reset the balance to 0 or set a new value.
     *
     * @param  int|null $newAmount
     * @param  array $parameters
     * @return \Batv45\Balance\Balance
     */
    public function resetBalance(float $newAmount = null, $parameters = [])
    {
        $this->balanceHistory()->delete();

        if (is_null($newAmount)) {
            return true;
        }

        return $this->createBalanceHistory($newAmount, $parameters);
    }

    /**
     * Check if there is a positive balance.
     *
     * @param  int $amount
     * @return bool
     */
    public function hasBalance(int $amount = 1)
    {
        return $this->balance > 0 && $this->balanceHistory()->sum('amount') >= $amount;
    }

    /**
     * Check if there is no more balance.
     *
     * @return bool
     */
    public function hasNoBalance()
    {
        return $this->balance <= 0;
    }

    /**
     * Function to handle mutations (increase, decrease).
     *
     * @param  int $amount
     * @param  array  $parameters
     * @return \Batv45\Balance\Balance
     */
    protected function createBalanceHistory(float $amount, array $parameters = [])
    {
        $reference = Arr::get($parameters, 'reference');

        $createArguments = collect([
            'amount' => $amount,
            'description' => Arr::get($parameters, 'description'),
        ])->when($reference, function ($collection) use ($reference) {
            return $collection
                ->put('referenceable_type', $reference->getMorphClass())
                ->put('referenceable_id', $reference->getKey());
        })->toArray();

        $balanceHistory = $this->balanceHistory()->create($createArguments);
        $this->syncBalanceHistory();

        return $balanceHistory;

    }

    /**
     * Get all Balance History.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function balanceHistory()
    {
        return $this->morphMany(Balance::class, 'balanceable');
    }

    public function getBalanceTlAttribute()
    {
        $bal = intval($this->balance * 100);
        return Money::TRY($bal)->formatByCurrencySymbol(false,'tr_TR');
    }
}
