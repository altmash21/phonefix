<?php

namespace App\Models\Banking;

use App\Abstracts\Model;
use App\Models\Common\Media as MediaModel;
use App\Scopes\Transaction as Scope;
use App\Traits\Currencies;
use App\Traits\DateTime;
use App\Traits\Media;
use App\Traits\Recurring;
use App\Traits\Transactions;
use App\Traits\Transactions\HasTransactionLineActions;
use App\Traits\Transactions\HasTransactionScopes;
use Bkwld\Cloner\Cloneable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use Cloneable, Currencies, DateTime, HasFactory, Media, Recurring, Transactions, HasTransactionScopes, HasTransactionLineActions;

    public const INCOME_TYPE = 'income';
    public const INCOME_TRANSFER_TYPE = 'income-transfer';
    public const INCOME_SPLIT_TYPE = 'income-split';
    public const INCOME_RECURRING_TYPE = 'income-recurring';
    public const EXPENSE_TYPE = 'expense';
    public const EXPENSE_TRANSFER_TYPE = 'expense-transfer';
    public const EXPENSE_SPLIT_TYPE = 'expense-split';
    public const EXPENSE_RECURRING_TYPE = 'expense-recurring';

    protected $table = 'transactions';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'type',
        'number',
        'account_id',
        'paid_at',
        'amount',
        'currency_code',
        'currency_rate',
        'document_id',
        'contact_id',
        'description',
        'category_id',
        'payment_method',
        'reference',
        'parent_id',
        'split_id',
        'created_from',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'paid_at'           => 'datetime',
        'amount'            => 'double',
        'currency_rate'     => 'double',
        'deleted_at'        => 'datetime',
    ];

    /**
     * Sortable columns.
     *
     * @var array
     */
    public $sortable = [
        'paid_at',
        'number',
        'type',
        'account.name',
        'contact.name',
        'category.name',
        'document.document_number',
        'amount',
        'recurring.started_at',
        'recurring.status',
    ];

    /**
     * Clonable relationships.
     *
     * @var array
     */
    public $cloneable_relations = ['recurring', 'taxes'];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope(new Scope);
    }

    public function account()
    {
        return $this->belongsTo('App\Models\Banking\Account')->withDefault(['name' => trans('general.na')]);
    }

    public function category()
    {
        return $this->belongsTo('App\Models\Setting\Category')->withoutGlobalScope('App\Scopes\Category')->withDefault(['name' => trans('general.na')]);
    }

    public function children()
    {
        return $this->hasMany('App\Models\Banking\Transaction', 'parent_id');
    }

    public function contact()
    {
        return $this->belongsTo('App\Models\Common\Contact')->withDefault(['name' => trans('general.na')]);
    }

    public function currency()
    {
        return $this->belongsTo('App\Models\Setting\Currency', 'currency_code', 'code');
    }

    public function parent()
    {
        return $this->belongsTo('App\Models\Banking\Transaction', 'parent_id')->isRecurring();
    }

    public function splits()
    {
        return $this->hasMany('App\Models\Banking\Transaction', 'split_id');
    }

    public function user()
    {
        return $this->belongsTo(user_model_class(), 'contact_id', 'id');
    }

    public function onCloning($src, $child = null)
    {
        if (app()->has(\App\Console\Commands\RecurringCheck::class)) {
            $suffix = '';
        } else {
            $suffix = $src->isRecurringTransaction() ? '-recurring' : '';
        }

        $this->number       = $this->getNextTransactionNumber($this->type, $suffix);
        $this->document_id  = null;
        $this->split_id     = null;

        unset($this->reconciled);
    }

    /**
     * Get the payment method title.
     *
     * @return string
     */
    public function getPaymentMethodTitleAttribute()
    {
        $payment_method = $this->payment_method;

        $payment_methods = \App\Utilities\Modules::getPaymentMethods('all');

        return $payment_methods[$payment_method] ?? $payment_method;
    }

    /**
     * Convert amount to double.
     *
     * @return float
     */
    public function getAmountForAccountAttribute()
    {
        $amount = $this->amount;

        // Convert amount if not same currency
        if ($this->account->currency_code != $this->currency_code) {
            $to_code = $this->account->currency_code;
            $to_rate = currency($this->account->currency_code)->getRate();

            $amount = $this->convertBetween($amount, $this->currency_code, $this->currency_rate, $to_code, $to_rate);
        }

        return $amount;
    }

    /**
     * Convert amount to double.
     *
     * @return float
     */
    public function getAmountForDocumentAttribute()
    {
        $amount = $this->amount;

        // Convert amount if not same currency
        if ($this->document->currency_code != $this->currency_code) {
            $to_code = $this->document->currency_code;
            $to_rate = $this->document->currency_rate;
            //$to_rate = currency($this->document->currency_code)->getRate();

            $amount = $this->convertBetween($amount, $this->currency_code, $this->currency_rate, $to_code, $to_rate);
        }

        return $amount;
    }

    /**
     * Get the current balance.
     *
     * @return string
     */
    public function getAttachmentAttribute($value)
    {
        if (!empty($value) && !$this->hasMedia('attachment')) {
            return $value;
        } elseif (!$this->hasMedia('attachment')) {
            return false;
        }

        return $this->getMedia('attachment')->all();
    }

    /**
     * Get the splittable status.
     *
     * @return bool
     */
    public function getIsSplittableAttribute()
    {
        return is_null($this->split_id);
    }

    public function delete_attachment()
    {
        if ($attachments = $this->attachment) {
            foreach ($attachments as $file) {
                MediaModel::where('id', $file->id)->delete();
            }
        }
    }

    /**
     * Get the title of type.
     *
     * @return string
     */
    public function getTypeTitleAttribute($value)
    {
        if ($value) {
            return $value;
        }

        $translation = config('type.transaction.' . $this->type . '.translation.transactions');

        if (! empty($translation)) {
            return trans_choice($translation, 1);
        }

        $type = $this->getRealTypeOfRecurringTransaction($this->type);
        $type = $this->getRealTypeOfTransferTransaction($type);
        $type = $this->getRealTypeOfSplitTransaction($type);

        $type = str_replace('-', '_', $type);

        return trans_choice('general.' . Str::plural($type), 1);
    }

    /**
     * Get the item id.
     *
     * @return string
     */
    public function getTaxIdsAttribute()
    {
        return $this->taxes()->pluck('tax_id');
    }

    /**
     * Get the amount before tax.
     *
     * @return string
     */
    public function getTotalTaxAttribute()
    {
        $precision = currency($this->currency_code)->getPrecision();

        $amount = 0;

        if ($this->taxes->count()) {
            foreach ($this->taxes as $tax) {
                $amount += $tax->amount;
            }
        }

        return round($amount, $precision);
    }

    /**
     * Get the amount before tax.
     *
     * @return string
     */
    public function getAmountBeforeTaxAttribute()
    {
        if (empty($this->amount)) {
            return 0;
        }

        $precision = currency($this->currency_code)->getPrecision();

        return round($this->amount - $this->total_tax, $precision);
    }

    /**
     * Get the route name.
     *
     * @return string
     */
    public function getRouteNameAttribute($value)
    {
        if ($value) {
            return $value;
        }

        if ($this->isIncome()) {
            if (! empty($this->document_id) && $this->document->type != 'invoice') {
                return $this->getRouteFromConfig();
            } else {
                return !empty($this->document_id) ? 'invoices.show' : 'transactions.show';
            }
        }

        if ($this->isExpense()) {
            if (! empty($this->document_id) && $this->document->type != 'bill') {
                return $this->getRouteFromConfig();
            } else {
                return !empty($this->document_id) ? 'bills.show' : 'transactions.show';
            }
        }

        return 'transactions.index';
    }

    public function getRouteFromConfig()
    {
        $route = '';

        $alias = config('type.document.' . $this->document->type . '.alias');
        $prefix = config('type.document.' . $this->document->type . '.route.prefix');

        // if use module set module alias
        if (!empty($alias)) {
            $route .= $alias . '.';
        }

        if (!empty($prefix)) {
            $route .= $prefix . '.';
        }

        if ($route) {
            return $route . 'show';
        }

        return 'transactions.index';
    }

    /**
     * Get the route id.
     *
     * @return string
     */
    public function getRouteIdAttribute()
    {
        return $this->id;
    }

    /**
     * Get the recurring status label.
     *
     * @return string
     */
    public function getRecurringStatusLabelAttribute()
    {
        return match($this->recurring->status) {
            'active'    => 'status-partial',
            'ended'     => 'status-success',
            default     => 'status-success',
        };
    }

    /**
     * Retrieve the model for a bound value.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $query = $this->where('id', $value);

        if (request()->route()->hasParameter('recurring_transaction')) {
            $query->isRecurring();
        }

        return $query->firstOrFail();
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return \Database\Factories\Transaction::new();
    }
}
