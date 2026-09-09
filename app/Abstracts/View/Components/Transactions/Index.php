<?php

namespace App\Abstracts\View\Components\Transactions;

use App\Abstracts\View\Component;
use App\Traits\Transactions;
use App\Traits\Modules;
use App\Traits\SearchString;
use App\Traits\ViewComponents;
use Illuminate\Support\Str;

use App\Abstracts\View\Components\Transactions\Concerns\HasTransactionIndexRoutes;
use App\Abstracts\View\Components\Transactions\Concerns\HasTransactionIndexColumns;

abstract class Index extends Component
{
    use SearchString, Transactions, Modules, ViewComponents;
    use HasTransactionIndexRoutes, HasTransactionIndexColumns;


    public const OBJECT_TYPE = 'transaction';
    public const DEFAULT_TYPE = 'transaction';
    public const DEFAULT_PLURAL_TYPE = 'transactions';

    public $type;

    public $alias;

    public $transactions;

    public $totalTransactions;

    public $permissionCreate;

    public $permissionUpdate;

    public $permissionDelete;

    public $routeTabDocument;

    public $routeParamsTabUnpaid;

    public $routeParamsTabDraft;

    public $routeTabRecurring;

    public $checkPermissionCreate;

    public $hideIncomeCreate;

    public $routeIncomeCreate;

    public $textIncomeCreate;

    public $hideExpenseCreate;

    public $routeExpenseCreate;

    public $textExpenseCreate;

    public $hideImport;

    public $routeImport;

    public $hideExport;

    public $routeExport;

    public $hideEmptyPage;

    public $hideSummary;

    public $summaryItems;

    public $hideSearchString;

    public $hideBulkAction;

    public $bulkActions;

    /** @var string */
    public $searchStringModel;

    /** @var string */
    public $bulkActionClass;

    /** @var array */
    public $bulkActionRouteParameters;

    /** @var string */
    public $searchRoute;

    /** @var string */
    public $classBulkAction;

    /** @var string */
    public $tabActive;

    /** @var string */
    public $tabSuffix;

    public $hidePaymentMethod;

    public $hidePaidAt;

    public $hideNumber;

    public $classPaidAtAndNumber;

    public $textPaidAt;

    public $textNumber;
    
    /** @var bool */
    public $hideStartedAt;

    /** @var bool */
    public $hideEndedAt;

    /** @var string */
    public $classStartedAtAndEndedAt;

    /** @var string */
    public $textStartedAt;

    /** @var string */
    public $textEndedAt;

    public $hideType;

    public $hideCategory;

    public $classTypeAndCategory;

    public $textType;

    public $textCategory;

    /** @var bool */
    public $hideStatus;

    /** @var string */
    public $classStatus;

    /** @var bool */
    public $hideFrequency;

    /** @var string */
    public $classFrequencyAndDuration;

    /** @var bool */
    public $hideDuration;

    public $hideAccount;

    public $classAccount;

    public $textAccount;

    public $hideContact;

    public $hideDocument;

    public $classContactAndDocument;

    public $textContact;

    public $textDocument;

    public $hideAmount;

    public $classAmount;

    public $textAmount;

    public function __construct(
        string $type = '', string $alias = '', $transactions = [], int $totalTransactions = null,
        string $permissionCreate = '', string $permissionUpdate = '', string $permissionDelete = '',
        bool $checkPermissionCreate = true,
        bool $hideIncomeCreate = false,  $routeIncomeCreate = '', string $textIncomeCreate = '',
        bool $hideExpenseCreate = false, $routeExpenseCreate = '', string $textExpenseCreate = '',
        bool $hideImport = false, $routeImport = '',
        bool $hideExport = false, $routeExport = '',
        bool $hideEmptyPage = false,
        bool $hideSummary = false, array $summaryItems = [],
        bool $hideSearchString = false, bool $hideBulkAction = false,
        string $searchStringModel = '', string $bulkActionClass = '', array $bulkActions = [], array $bulkActionRouteParameters = [], string $searchRoute = '', string $classBulkAction = '',
        string $tabActive = '', string $tabSuffix = '',
        bool $hidePaymentMethod = false,
        bool $hidePaidAt = false, bool $hideNumber = false, string $classPaidAtAndNumber = '', string $textPaidAt = '', string $textNumber = '',
        bool $hideStartedAt = false, bool $hideEndedAt = false, string $classStartedAtAndEndedAt = '', string $textStartedAt = '', string $textEndedAt = '',
        bool $hideType = false, bool $hideCategory = false, string $classTypeAndCategory = '', string $textType = '', string $textCategory = '',
        bool $hideStatus = false, string $classStatus = '',
        bool $hideAccount = false, string $classAccount = '', string $textAccount = '',
        bool $hideFrequency = false, bool $hideDuration = false, string $classFrequencyAndDuration = '',
        bool $hideContact = false, bool $hideDocument = false, string $classContactAndDocument = '', string $textContact = '', string $textDocument = '',
        bool $hideAmount = false, string $classAmount = '', string $textAmount = ''
    ) {
        $this->type = $type;
        $this->transactions = $transactions;
        /* -- Main Start -- */
        $this->type = $type;
        $this->alias = $this->getAlias($type, $alias);
        $this->transactions = ($transactions) ? $transactions : collect();
        $this->totalTransactions = $this->getTotalTransactions($totalTransactions);

        $this->permissionCreate = $this->getPermissionCreate($type, $permissionCreate);
        $this->permissionUpdate = $this->getPermissionUpdate($type, $permissionUpdate);
        $this->permissionDelete = $this->getPermissionDelete($type, $permissionDelete);

        /* -- Main End -- */

        /* -- Buttons Start -- */
        $this->checkPermissionCreate = $checkPermissionCreate;

        $this->hideIncomeCreate = $hideIncomeCreate;
        $this->routeIncomeCreate = $this->getRouteIncomeCreate($type, $routeIncomeCreate);
        $this->textIncomeCreate = $this->getTextIncomeCreate($type, $textIncomeCreate);

        $this->hideExpenseCreate = $hideExpenseCreate;
        $this->routeExpenseCreate = $this->getRouteExpenseCreate($type, $routeExpenseCreate);
        $this->textExpenseCreate = $this->getTextExpenseCreate($type, $textExpenseCreate);

        $this->hideImport = $hideImport;
        $this->routeImport = $this->getRouteImport($type, $routeImport);

        $this->hideExport = $hideExport;
        $this->routeExport = $this->getRouteExport($type, $routeExport);
        /* -- Buttons End -- */

        /* -- Content Start -- */

        /* -- Empty Page Start -- */
        $this->hideEmptyPage = $this->getHideEmptyPage($hideEmptyPage);
        /* -- Empty Page End -- */

        /* -- Summary Start -- */
        $this->hideSummary = $hideSummary;
        $this->summaryItems = $this->getSummaryItems($type, $summaryItems);
        /* -- Summary End -- */

        /* Container Start */
        $this->hideSearchString = $hideSearchString;
        $this->hideBulkAction = $hideBulkAction;

        $this->searchStringModel = $this->getSearchStringModel($type, $searchStringModel);

        $this->bulkActionClass = $this->getBulkActionClass($type, $bulkActionClass);
        $this->bulkActionRouteParameters = $this->getBulkActionRouteParameters($type, $bulkActionRouteParameters);

        $this->searchRoute = $this->getIndexRoute($type, $searchRoute);

        $this->classBulkAction = $this->getClassBulkAction($type, $classBulkAction);

        $this->tabSuffix = $this->getTabSuffix($type, $tabSuffix);
        $this->tabActive = $this->getTabActive($type, $tabActive);

        $this->hidePaymentMethod = $hidePaymentMethod;

        /* Document Start */
        $this->hidePaidAt = $hidePaidAt;
        $this->hideNumber = $hideNumber;
        $this->classPaidAtAndNumber = $this->getClassPaidAtAndNumber($type, $classPaidAtAndNumber);
        $this->textPaidAt = $this->getTextPaidAt($type, $textPaidAt);
        $this->textNumber = $this->getTextNumber($type, $textNumber);

        $this->hideStartedAt = $hideStartedAt;
        $this->hideEndedAt = $hideEndedAt;
        $this->classStartedAtAndEndedAt = $this->getClassStartedAndEndedAt($type, $classStartedAtAndEndedAt);
        $this->textStartedAt = $this->getTextStartedAt($type, $textStartedAt);
        $this->textEndedAt = $this->getTextEndedAt($type, $textEndedAt);

        $this->hideType = $hideType;
        $this->hideCategory = $hideCategory;
        $this->classTypeAndCategory = $this->getClassTypeAndCategory($type, $classTypeAndCategory);
        $this->textType = $this->getTextType($type, $textType);
        $this->textCategory = $this->getTextCategory($type, $textCategory);

        $this->hideStatus = $hideStatus;
        $this->classStatus = $this->getClassStatus($type, $classStatus);

        $this->hideFrequency = $hideFrequency;
        $this->hideDuration = $hideDuration;
        $this->classFrequencyAndDuration = $this->getClassFrequencyAndDuration($type, $classFrequencyAndDuration);

        $this->hideAccount = $hideAccount;
        $this->classAccount = $this->getClassAccount($type, $classAccount);
        $this->textAccount = $this->getTextAccount($type, $textAccount);

        $this->hideContact = $hideContact;
        $this->hideDocument = $hideDocument;
        $this->classContactAndDocument = $this->getClassContactAndDocument($type, $classContactAndDocument);
        $this->textContact = $this->getTextContact($type, $textContact);
        $this->textDocument = $this->getTextDocument($type, $textDocument);

        $this->hideAmount = $hideAmount;
        $this->classAmount = $this->getClassAmount($type, $classAmount);
        $this->textAmount = $this->getTextAmount($type, $textAmount);

        /* Document End */

        /* Container End */

        /* -- Content End -- */

        // Set Parent data
        $this->setParentData();
    }

}
