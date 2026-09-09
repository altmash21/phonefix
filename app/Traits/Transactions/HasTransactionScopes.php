<?php

namespace App\Traits\Transactions;

use Illuminate\Database\Eloquent\Builder;

trait HasTransactionScopes
{
    public function scopeNumber(Builder $query, string $number): Builder
    {
        return $query->where('number', '=', $number);
    }

    public function scopeType(Builder $query, $types): Builder
    {
        if (empty($types)) {
            return $query;
        }

        return $query->whereIn($this->qualifyColumn('type'), (array) $types);
    }

    public function scopeIncome(Builder $query): Builder
    {
        return $query->whereIn($this->qualifyColumn('type'), (array) $this->getIncomeTypes());
    }

    public function scopeIncomeTransfer(Builder $query): Builder
    {
        return $query->where($this->qualifyColumn('type'), '=', self::INCOME_TRANSFER_TYPE);
    }

    public function scopeIncomeRecurring(Builder $query): Builder
    {
        return $query->where($this->qualifyColumn('type'), '=', self::INCOME_RECURRING_TYPE)
                ->whereHas('recurring', function (Builder $query) {
                    $query->whereNull('deleted_at');
                });
    }

    public function scopeExpense(Builder $query): Builder
    {
        return $query->whereIn($this->qualifyColumn('type'), (array) $this->getExpenseTypes());
    }

    public function scopeExpenseTransfer(Builder $query): Builder
    {
        return $query->where($this->qualifyColumn('type'), '=', self::EXPENSE_TRANSFER_TYPE);
    }

    public function scopeExpenseRecurring(Builder $query): Builder
    {
        return $query->where($this->qualifyColumn('type'), '=', self::EXPENSE_RECURRING_TYPE)
                ->whereHas('recurring', function (Builder $query) {
                    $query->whereNull('deleted_at');
                });
    }

    public function scopeIsTransfer(Builder $query): Builder
    {
        return $query->where($this->qualifyColumn('type'), 'like', '%-transfer');
    }

    public function scopeIsNotTransfer(Builder $query): Builder
    {
        return $query->where($this->qualifyColumn('type'), 'not like', '%-transfer');
    }

    public function scopeIsRecurring(Builder $query): Builder
    {
        return $query->where($this->qualifyColumn('type'), 'like', '%-recurring');
    }

    public function scopeIsNotRecurring(Builder $query): Builder
    {
        return $query->where($this->qualifyColumn('type'), 'not like', '%-recurring');
    }

    public function scopeIsSplit(Builder $query): Builder
    {
        return $query->where($this->qualifyColumn('type'), 'like', '%-split');
    }

    public function scopeIsNotSplit(Builder $query): Builder
    {
        return $query->where($this->qualifyColumn('type'), 'not like', '%-split');
    }

    public function scopeIsDocument(Builder $query): Builder
    {
        return $query->whereNotNull('document_id');
    }

    public function scopeIsNotDocument(Builder $query): Builder
    {
        return $query->whereNull('document_id');
    }

    public function scopeDocumentId(Builder $query, int $document_id): Builder
    {
        return $query->where('document_id', '=', $document_id);
    }

    public function scopeAccountId(Builder $query, int $account_id): Builder
    {
        return $query->where('account_id', '=', $account_id);
    }

    public function scopeContactId(Builder $query, int $contact_id): Builder
    {
        return $query->where('contact_id', '=', $contact_id);
    }

    public function scopeCategoryId(Builder $query, int $category_id): Builder
    {
        return $query->where('category_id', '=', $category_id);
    }

    /**
     * Order by paid date.
     */
    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderBy('paid_at', 'desc');
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->sum('amount');
    }

    public function scopeIsReconciled(Builder $query): Builder
    {
        return $query->where('reconciled', 1);
    }

    public function scopeIsNotReconciled(Builder $query): Builder
    {
        return $query->where('reconciled', 0);
    }
}
