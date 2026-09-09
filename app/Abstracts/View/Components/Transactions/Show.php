<?php

namespace App\Abstracts\View\Components\Transactions;

use App\Abstracts\View\Component;
use App\Models\Common\Media;
use App\Traits\DateTime;
use App\Traits\Transactions;
use App\Utilities\Modules;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Intervention\Image\Exception\NotReadableException;
use App\Traits\ViewComponents;

use App\Abstracts\View\Components\Transactions\Concerns\HasTransactionShowRoutes;
use App\Abstracts\View\Components\Transactions\Concerns\HasTransactionShowLabels;

abstract class Show extends Component
{
    use DateTime, Transactions, ViewComponents;
    use HasTransactionShowRoutes, HasTransactionShowLabels;


    public const OBJECT_TYPE = 'transaction';
    public const DEFAULT_TYPE = 'transaction';
    public const DEFAULT_PLURAL_TYPE = 'transactions';

    public $type;

    public $transaction;

    /** @var string */
    public $transactionTemplate;

    /** @var string */
    public $logo;

    /** @var array */
    public $payment_methods;

    public $date_format;

    /** @var bool */
    public $hideButtonAddNew;

    /** @var bool */
    public $hideButtonMoreActions;

    /** @var bool */
    public $hideButtonEdit;

    /** @var bool */
    public $hideButtonDuplicate;

    /** @var bool */
    public $hideButtonConnect;

    /** @var bool */
    public $hideButtonPrint;

    /** @var bool */
    public $hideButtonShare;

    /** @var bool */
    public $hideButtonEmail;

    /** @var bool */
    public $hideButtonPdf;

    /** @var bool */
    public $hideButtonEnd;

    /** @var bool */
    public $hideButtonDelete;

    /** @var string */
    public $checkButtonReconciled;

    /** @var bool */
    public $hideDivider1;

    /** @var bool */
    public $hideDivider2;

    /** @var bool */
    public $hideDivider3;

    /** @var bool */
    public $hideDivider4;

    /** @var string */
    public $permissionCreate;

    /** @var string */
    public $permissionUpdate;

    /** @var string */
    public $permissionDelete;

    /** @var string */
    public $routeButtonAddNew;

    /** @var string */
    public $routeButtonEdit;

    /** @var string */
    public $routeButtonDuplicate;

    /** @var string */
    public $routeButtonPrint;

    /** @var string */
    public $routeContactShow;

    /** @var string */
    public $shareRoute;

    /** @var string */
    public $signedUrl;

    /** @var string */
    public $routeButtonEmail;

    /** @var string */
    public $routeButtonPdf;

    /** @var string */
    public $routeButtonEnd;

    /** @var string */
    public $routeButtonDelete;

    /** @var string */
    public $textDeleteModal;

    /** @var bool */
    public $hideCompany;

    /** @var bool */
    public $hideCompanyLogo;

    /** @var bool */
    public $hideCompanyDetails;

    /** @var bool */
    public $hideCompanyName;

    /** @var bool */
    public $hideCompanyAddress;

    /** @var bool */
    public $hideCompanyTaxNumber;

    /** @var bool */
    public $hideCompanyPhone;

    /** @var bool */
    public $hideCompanyEmail;

    /** @var bool */
    public $hideContentTitle;

    /** @var bool */
    public $hideNumber;

    /** @var bool */
    public $hidePaidAt;

    /** @var bool */
    public $hideAccount;

    /** @var bool */
    public $hideCategory;

    /** @var bool */
    public $hidePaymentMethods;

    /** @var bool */
    public $hideReference;

    /** @var bool */
    public $hideDescription;

    /** @var bool */
    public $hideAmount;

    /** @var string */
    public $textButtonAddNew;

    /** @var string */
    public $textContentTitle;

    /** @var string */
    public $textNumber;

    /** @var string */
    public $textPaidAt;

    /** @var string */
    public $textAccount;

    /** @var string */
    public $textCategory;

    /** @var string */
    public $textPaymentMethods;

    /** @var string */
    public $textReference;

    /** @var string */
    public $textDescription;

    /** @var string */
    public $textAmount;

    /** @var string */
    public $textPaidBy;

    /** @var bool */
    public $hideContact;

    /** @var bool */
    public $hideContactInfo;

    /** @var bool */
    public $hideContactName;

    /** @var bool */
    public $hideContactAddress;

    /** @var bool */
    public $hideContactTaxNumber;

    /** @var bool */
    public $hideContactPhone;

    /** @var bool */
    public $hideContactEmail;

    /** @var bool */
    public $hideRelated;

    /** @var bool */
    public $hideRelatedDocumentNumber;

    /** @var bool */
    public $hideRelatedContact;

    /** @var bool */
    public $hideRelatedDocumentDate;

    /** @var bool */
    public $hideRelatedDocumentAmount;

    /** @var bool */
    public $hideRelatedAmount;

    /** @var string */
    public $textRelatedTransansaction;

    /** @var string */
    public $textRelatedDocumentNumber;

    /** @var string */
    public $textRelatedContact;

    /** @var string */
    public $textRelatedDocumentDate;

    /** @var string */
    public $textRelatedDocumentAmount;

    /** @var string */
    public $textRelatedAmount;

    /** @var string */
    public $routeDocumentShow;

    /** @var string */
    public $routeTransactionShow;

    /** @var bool */
    public $hideSchedule;

    /** @var bool */
    public $hideChildren;

    /** @var bool */
    public $hideConnect;

    /** @var bool */
    public $hideTransfer;

    /** @var bool */
    public $hideAttachment;

    public $attachment;

    /** @var array */
    public $connectTranslations;

    /** @var string */
    public $textRecurringType;

    /** @var bool */
    public $hideRecurringMessage;

    /** @var bool */
    public $hideConnectMessage;

    /** @var bool */
    public $hideCreated;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        $type, $transaction, $transactionTemplate = '', $logo = '', array $payment_methods = [],
        bool $hideButtonAddNew = false, bool $hideButtonMoreActions = false, bool $hideButtonEdit = false, bool $hideButtonDuplicate = false, bool $hideButtonConnect = false, bool $hideButtonPrint = false, bool $hideButtonShare = false,
        bool $hideButtonEmail = false, bool $hideButtonPdf = false, bool $hideButtonEnd = false, bool $hideButtonDelete = false, bool $checkButtonReconciled = true,
        bool $hideDivider1 = false, bool $hideDivider2 = false, bool $hideDivider3 = false, bool $hideDivider4 = false,
        string $permissionCreate = '', string $permissionUpdate = '', string $permissionDelete = '',
        string $routeButtonAddNew = '', string $routeButtonEdit = '', string $routeButtonDuplicate = '', string $routeButtonPrint = '', string $shareRoute = '', string $signedUrl = '',
        string $routeButtonEmail = '', string $routeButtonPdf = '', string $routeButtonEnd = '', string $routeButtonDelete = '', string $routeContactShow = '',
        string $textDeleteModal = '',

        bool $hideCompany = false, bool $hideCompanyLogo = false, bool $hideCompanyDetails = false, bool $hideCompanyName = false, bool $hideCompanyAddress = false,
        bool $hideCompanyTaxNumber = false, bool $hideCompanyPhone = false, bool $hideCompanyEmail = false,
        bool $hideContentTitle = false, bool $hideNumber = false, bool $hidePaidAt = false, bool $hideAccount = false, bool $hideCategory = false, bool $hidePaymentMethods = false, bool $hideReference = false,
        bool $hideDescription = false, bool $hideAmount = false,
        string $textContentTitle = '', string $textNumber = '', string $textPaidAt = '', string $textAccount = '', string $textCategory = '', string $textPaymentMethods = '', string $textReference = '', string $textDescription = '',
        string $textAmount = '', string $textPaidBy = '',
        bool $hideContact = false, bool $hideContactInfo = false, bool $hideContactName = false, bool $hideContactAddress = false, bool $hideContactTaxNumber = false,
        bool $hideContactPhone = false, bool $hideContactEmail = false,
        bool $hideRelated = false, bool $hideRelatedDocumentNumber = false, bool $hideRelatedContact = false, bool $hideRelatedDocumentDate = false, bool $hideRelatedDocumentAmount = false, bool $hideRelatedAmount = false,
        string $textRelatedTransansaction = '', string $textRelatedDocumentNumber = '', string $textRelatedContact = '', string $textRelatedDocumentDate = '', string $textRelatedDocumentAmount = '', string $textRelatedAmount = '',
        string $routeDocumentShow = '', string $routeTransactionShow = '', string $textButtonAddNew = '',

        bool $hideSchedule = false, bool $hideChildren = false, bool $hideConnect = false, bool $hideTransfer = false, bool $hideAttachment = false, $attachment = [],
        array $connectTranslations = [], string $textRecurringType = '', bool $hideRecurringMessage = false, $hideConnectMessage = false, bool $hideCreated = false
    ) {
        $this->type = $type;
        $this->transaction = $transaction;
        $this->transactionTemplate = $this->getTransactionTemplate($type, $transactionTemplate);
        $this->logo = $this->getLogo($logo);
        $this->payment_methods = ($payment_methods) ?: Modules::getPaymentMethods('all');
        $this->date_format = $this->getCompanyDateFormat();

        // Navbar Hide
        $this->hideButtonAddNew = $hideButtonAddNew;
        $this->hideButtonMoreActions = $hideButtonMoreActions;
        $this->hideButtonEdit = $hideButtonEdit;
        $this->hideButtonDuplicate = $hideButtonDuplicate;
        $this->hideButtonConnect = $hideButtonConnect;
        $this->hideButtonPrint = $hideButtonPrint;
        $this->hideButtonShare = $hideButtonShare;
        $this->hideButtonEmail = $hideButtonEmail;
        $this->hideButtonPdf = $hideButtonPdf;
        $this->hideButtonEnd = $hideButtonEnd;
        $this->hideButtonDelete = $hideButtonDelete;
        $this->checkButtonReconciled = $checkButtonReconciled;

        $this->hideDivider1 = $hideDivider1;
        $this->hideDivider2 = $hideDivider2;
        $this->hideDivider3 = $hideDivider3;
        $this->hideDivider4 = $hideDivider4;

        // Navbar Permission
        $this->permissionCreate = $this->getPermissionCreate($type, $permissionCreate);
        $this->permissionUpdate = $this->getPermissionUpdate($type, $permissionUpdate);
        $this->permissionDelete = $this->getPermissionDelete($type, $permissionDelete);

        // Navbar route
        $this->routeButtonAddNew = $this->getRouteButtonAddNew($type, $routeButtonAddNew);
        $this->routeButtonEdit = $this->getRouteButtonEdit($type, $routeButtonEdit);
        $this->routeButtonDuplicate = $this->getRouteButtonDuplicate($type, $routeButtonDuplicate);
        $this->routeButtonPrint = $this->getRouteButtonPrint($type, $routeButtonPrint);
        $this->shareRoute = $this->getShareRoute($type, $shareRoute);
        $this->signedUrl = $this->getSignedUrl($type, $signedUrl);
        $this->routeButtonEmail = $this->getRouteButtonEmail($type, $routeButtonEmail);
        $this->routeButtonPdf = $this->getRouteButtonPdf($type, $routeButtonPdf);
        $this->routeButtonEnd = $this->getRouteButtonEnd($type, $routeButtonEnd);
        $this->routeButtonDelete = $this->getRouteButtonDelete($type, $routeButtonDelete);
        $this->routeContactShow = $this->getRouteContactShow($type, $routeContactShow);

        // Navbar Text
        $this->textButtonAddNew = $this->getTextButtonAddNew($type, $textButtonAddNew);
        $this->textDeleteModal = $textDeleteModal;

        // Hide Schedule
        $this->hideSchedule = $hideSchedule;

        // Hide Children
        $this->hideChildren = $hideChildren;

        // Hide Connect
        $this->hideConnect = $hideConnect;

        // Hide Transfer
        $this->hideTransfer = $hideTransfer;

        // Hide Attachment
        $this->hideAttachment = $hideAttachment;

        // Company Information Hide checker
        $this->hideCompany = $hideCompany;
        $this->hideCompanyLogo = $hideCompanyLogo;
        $this->hideCompanyDetails = $hideCompanyDetails;
        $this->hideCompanyName = $hideCompanyName;
        $this->hideCompanyAddress = $hideCompanyAddress;
        $this->hideCompanyTaxNumber = $hideCompanyTaxNumber;
        $this->hideCompanyPhone = $hideCompanyPhone;
        $this->hideCompanyEmail = $hideCompanyEmail;

        // Transaction Information Hide checker
        $this->hideContentTitle = $hideContentTitle;
        $this->hideNumber = $hideNumber;
        $this->hidePaidAt = $hidePaidAt;
        $this->hideAccount = $hideAccount;
        $this->hideCategory = $hideCategory;
        $this->hidePaymentMethods = $hidePaymentMethods;
        $this->hideReference = $hideReference;
        $this->hideDescription = $hideDescription;
        $this->hideAmount = $hideAmount;

        // Transaction Information Text
        $this->textContentTitle = $this->getTextContentTitle($type, $textContentTitle);
        $this->textNumber = $this->getTextNumber($type, $textNumber);
        $this->textPaidAt = $this->getTextPaidAt($type, $textPaidAt);
        $this->textAccount = $this->getTextAccount($type, $textAccount);
        $this->textCategory = $this->getTextCategory($type, $textCategory);
        $this->textPaymentMethods = $this->getTextPaymentMethods($type, $textPaymentMethods);
        $this->textReference = $this->getTextReference($type, $textReference);
        $this->textDescription = $this->getTextDescription($type, $textDescription);
        $this->textAmount = $this->getTextAmount($type, $textAmount);
        $this->textPaidBy = $this->getTextPaidBy($type, $textPaidBy);

        // Contact Information Hide checker
        $this->hideContact = $hideContact;
        $this->hideContactInfo = $hideContactInfo;
        $this->hideContactName = $hideContactName;
        $this->hideContactAddress = $hideContactAddress;
        $this->hideContactTaxNumber = $hideContactTaxNumber;
        $this->hideContactPhone = $hideContactPhone;
        $this->hideContactEmail = $hideContactEmail;

        // Related Information Hide checker
        $this->hideRelated = $hideRelated;
        $this->hideRelatedDocumentNumber = $hideRelatedDocumentNumber;
        $this->hideRelatedContact = $hideRelatedContact;
        $this->hideRelatedDocumentDate = $hideRelatedDocumentDate;
        $this->hideRelatedDocumentAmount = $hideRelatedDocumentAmount;
        $this->hideRelatedAmount = $hideRelatedAmount;

        // Related Information Text
        $this->textRelatedTransansaction = $this->getTextRelatedTransansaction($type, $textRelatedTransansaction);
        $this->textRelatedDocumentNumber = $this->getTextRelatedDocumentNumber($type, $textRelatedDocumentNumber);
        $this->textRelatedContact = $this->getTextRelatedContact($type, $textRelatedContact);
        $this->textRelatedDocumentDate = $this->getTextRelatedDocumentDate($type, $textRelatedDocumentDate);
        $this->textRelatedDocumentAmount = $this->getTextRelatedDocumentAmount($type, $textRelatedDocumentAmount);
        $this->textRelatedAmount = $this->getTextRelatedAmount($type, $textRelatedAmount);

        $this->routeDocumentShow = $this->routeDocumentShow($type, $routeDocumentShow);
        $this->routeTransactionShow = $this->routeTransactionShow($type, $routeTransactionShow);

        // Attachment data..
        $this->attachment = '';

        if (! empty($attachment)) {
            $this->attachment = $attachment;
        } else if (! empty($transaction)) {
            $this->attachment = $transaction->attachment;
        }

        // Connect translations
        $this->connectTranslations = $this->getTranslationsForConnect($type);
        $this->hideConnectMessage = $hideConnectMessage;

        $this->textRecurringType = $this->getTextRecurringType($type, $textRecurringType);
        $this->hideRecurringMessage = $hideRecurringMessage;
        $this->hideCreated = $hideCreated;
    }

}
