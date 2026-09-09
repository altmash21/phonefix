<?php

namespace App\Abstracts\View\Components\Transactions\Concerns;

trait HasTransactionIndexRoutes
{
    protected function getTotalTransactions($totalTransactions)
    {
        if (! is_null($totalTransactions)) {
            return $totalTransactions;
        }

        return $this->transactions->count();
    }

    protected function getRouteIncomeCreate ($type, $routeIncomeCreate)
    {
        if (! empty($routeIncomeCreate)) {
            return $routeIncomeCreate;
        }

        $route = $this->getRouteFromConfig($type, 'income_create');

        if (! empty($route)) {
            return $route;
        }

        if (Str::contains($type, 'recurring')) {
            return ['recurring-transactions.create', ['type' => 'income-recurring']];
        }

        return ['transactions.create', ['type' => 'income']];
    }

    protected function getTextIncomeCreate($type, $textIncomeCreate)
    {
        if (! empty($textIncomeCreate)) {
            return $textIncomeCreate;
        }

        $translation = $this->getTextFromConfig($type, 'income_create', 'income_create');

        if (! empty($translation)) {
            return $translation;
        }

        if (Str::contains($type, 'recurring')) {
            return 'general.recurring_incomes';
        }

        return 'general.incomes';
    }

    protected function getRouteExpenseCreate ($type, $routeExpenseCreate)
    {
        if (! empty($routeExpenseCreate)) {
            return $routeExpenseCreate;
        }

        $route = $this->getRouteFromConfig($type, 'expense_create');

        if (! empty($route)) {
            return $route;
        }

        if (Str::contains($type, 'recurring')) {
            return ['recurring-transactions.create', ['type' => 'expense-recurring']];
        }

        return ['transactions.create', ['type' => 'expense']];
    }

    protected function getTextExpenseCreate($type, $textExpenseCreate)
    {
        if (! empty($textExpenseCreate)) {
            return $textExpenseCreate;
        }

        $translation = $this->getTextFromConfig($type, 'expense_create', 'expense_create');

        if (! empty($translation)) {
            return $translation;
        }

        if (Str::contains($type, 'recurring')) {
            return 'general.recurring_expenses';
        }

        return 'general.expenses';
    }

    protected function getRouteImport($type, $routeImport)
    {
        if (! empty($routeImport)) {
            return $routeImport;
        }

        $route = $this->getRouteFromConfig($type, 'import');

        if (! empty($route)) {
            //return $route;
        }

        if (Str::contains($type, 'recurring')) {
            return ['import.create', ['banking', 'recurring-transactions']];
        }

        return ['import.create', ['banking', 'transactions']];
    }

    protected function getRouteExport($type, $routeExport)
    {
        if (! empty($routeExport)) {
            return $routeExport;
        }

        $route = $this->getRouteFromConfig($type, 'export');

        if (! empty($route)) {
            return $route;
        }

        if (Str::contains($type, 'recurring')) {
            return 'recurring-transactions.export';
        }

        return 'transactions.export';
    }

    protected function getHideEmptyPage($hideEmptyPage): bool
    {
        if ($hideEmptyPage) {
            return $hideEmptyPage;
        }

        if ($this->totalTransactions > 0) {
            return true;
        }

        if (request()->has('search') && ($this->totalTransactions > 0)) {
            return true;
        }

        return false;
    }

    public function getSummaryItems($type, $summaryItems)
    {
        if (! empty($summaryItems)) {
            return $summaryItems;
        }

        #todo this lines
        return [];
    }

    public function getTabActive($type, $tabActive)
    {
        if (! empty($tabActive)) {
            return $tabActive;
        }

        $search_type = $type == 'income-recurring' ? 'recurring-transactions' : search_string_value('type');

        return ($this->tabSuffix) ? 'transactions-' . $this->tabSuffix : $search_type;
    }

    public function getTabSuffix($type, $tabSuffix)
    {
        if (! empty($tabSuffix)) {
            return $tabSuffix;
        }

        $search_type = $type == 'income-recurring' ? 'recurring-transactions' : search_string_value('type');

        if ($search_type == 'income') {
            return 'income';
        }

        if ($search_type == 'expense') {
            return 'expense';
        }

        $suffix = $this->getTabActiveFromSetting($type);

        if (! empty($suffix)) {
            return $suffix;
        }

        return 'all';
    }

}
