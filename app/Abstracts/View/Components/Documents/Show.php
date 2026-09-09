<?php

namespace App\Abstracts\View\Components\Documents;

use App\Abstracts\View\Components\Documents\Concerns\HasShowDocumentLabels;
use App\Abstracts\View\Components\Documents\Concerns\HasShowRoutesAndButtons;
use App\Abstracts\View\Components\Documents\Concerns\HasShowTemplateAndBranding;
use App\Traits\DateTime;
use App\Traits\Documents;
use App\Models\Common\Media;
use App\Traits\Tailwind;
use App\Traits\ViewComponents;
use App\Abstracts\View\Component;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intervention\Image\Exception\NotReadableException;
use Image;

abstract class Show extends Component
{
    use DateTime, Documents, Tailwind, ViewComponents, HasShowRoutesAndButtons, HasShowTemplateAndBranding, HasShowDocumentLabels;

    public const OBJECT_TYPE = 'document';
    public const DEFAULT_TYPE = 'invoice';
    public const DEFAULT_PLURAL_TYPE = 'invoices';

    /* -- Main Start -- */
    public $type;

    public $document;

    public $transactions;

    /** @var string */
    public $permissionCreate;

    /** @var string */
    public $permissionUpdate;

    /** @var string */
    public $permissionDelete;
    /* -- Main End -- */

    /* -- Buttons Start -- */
    /** @var string */
    public $textPage;

    /** @var bool */
    public $hideCreate;

    /** @var string */
    public $createRoute;

    /** @var string */
    public $textCreate;

    /** @var bool */
    public $hideButtonStatuses;

    /** @var bool */
    public $hideEdit;

    /** @var string */
    public $editRoute;

    /** @var string */
    public $showRoute;

    /** @var bool */
    public $hideMoreActions;

    /** @var bool */
    public $hideDuplicate;

    /** @var string */
    public $duplicateRoute;

    /** @var bool */
    public $hidePrint;

    /** @var string */
    public $printRoute;

    /** @var bool */
    public $hideShare;

    /** @var string */
    public $shareRoute;

    /** @var string */
    public $signedUrl;

    /** @var bool */
    public $hideEmail;

    /** @var string */
    public $emailRoute;

    /** @var string */
    public $textEmail;

    /** @var bool */
    public $hidePdf;

    /** @var string */
    public $pdfRoute;

    /** @var bool */
    public $hideCancel;

    /** @var string */
    public $cancelledRoute;

    /** @var string */
    public $restoreRoute;

    /** @var bool */
    public $hideCustomize;

    /** @var string */
    public $permissionCustomize;

    /** @var string */
    public $customizeRoute;

    /** @var bool */
    public $hideEnd;

    /** @var string */
    public $endRoute;

    /** @var bool */
    public $hideDelete;

    /** @var bool */
    public $checkReconciled;

    /** @var string */
    public $deleteRoute;

    /** @var string */
    public $textDeleteModal;

    /** @var bool */
    public $hideDivider1;

    /** @var bool */
    public $hideDivider2;

    /** @var bool */
    public $hideDivider3;

    /** @var bool */
    public $hideDivider4;
    /* -- Buttons End -- */

    /* -- Content Start -- */
    /** @var string */
    public $accordionActive;

    /** @var bool */
    public $hideRecurringMessage;

    /** @var string */
    public $textRecurringType;

    /** @var bool */
    public $hideStatusMessage;

    /** @var string */
    public $textStatusMessage;

    /** @var bool */
    public $hideCreated;

    /** @var bool */
    public $hideSend;

    /** @var bool */
    public $hideMarkSent;

    /** @var string */
    public $markSentRoute;

    /** @var string */
    public $textMarkSent;

    /** @var bool */
    public $hideReceive;

    /** @var bool */
    public $hideMarkReceived;

    /** @var string */
    public $markReceivedRoute;

    /** @var string */
    public $textMarkReceived;

    /** @var bool */
    public $hideGetPaid;

    /** @var bool */
    public $hideAddPayment;

    /** @var bool */
    public $hideAcceptPayment;

    /** @var bool */
    public $hideMakePayment;

    /** @var string */
    public $transactionEmailRoute;

    /** @var string */
    public $transactionEmailTemplate;

    /** @var bool */
    public $hideRestore;

    /** @var bool */
    public $hideSchedule;

    /** @var bool */
    public $hideChildren;

    /** @var bool */
    public $hideAttachment;

    public $attachment;
    /* -- Content End -- */

    /** @var string */
    public $documentTemplate;

    public $logo;

    public $backgroundColor;

    public $hideFooter;

    public $hideCompanyLogo;

    public $hideCompanyDetails;

    public $hideCompanyName;

    public $hideCompanyAddress;

    public $hideCompanyTaxNumber;

    public $hideCompanyPhone;

    public $hideCompanyEmail;

    public $hideContactInfo;

    public $hideContactName;

    public $hideContactAddress;

    public $hideContactTaxNumber;

    public $hideContactPhone;

    public $hideContactEmail;

    public $hideOrderNumber;

    public $hideDocumentNumber;

    public $hideIssuedAt;

    public $hideDueAt;

    /** @var string */
    public $textDocumentTitle;

    /** @var string */
    public $textDocumentSubheading;

    public $textContactInfo;

    /** @var string */
    public $textIssuedAt;

    /** @var string */
    public $textDueAt;

    /** @var string */
    public $textDocumentNumber;

    /** @var string */
    public $textOrderNumber;

    public $hideItems;

    public $hideName;

    public $hideDescription;

    public $hideQuantity;

    public $hidePrice;

    public $hideDiscount;

    public $hideAmount;

    /** @var string */
    public $textItems;

    /** @var string */
    public $textQuantity;

    /** @var string */
    public $textPrice;

    /** @var string */
    public $textAmount;

    public $hideNote;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        $type, $document, $transactions = [],
        string $permissionCreate = '', string $permissionUpdate = '', string $permissionDelete = '', string $textPage = '',
        bool $hideCreate = false, string $createRoute = '', string $textCreate = '', bool $hideButtonStatuses = false, bool $hideEdit = false, string $editRoute = '', string $showRoute = '',
        bool $hideMoreActions = false, bool $hideDuplicate = false, string $duplicateRoute = '', bool $hidePrint = false, string $printRoute = '',
        bool $hideShare = false, string $shareRoute = '', string $signedUrl = '', bool $hideEmail = false, string $emailRoute = '', string $textEmail = '', bool $hidePdf = false, string $pdfRoute = '',
        bool $hideCancel = false, string $cancelledRoute = '', string $restoreRoute = '', bool $hideCustomize = false, string $permissionCustomize = '', string $customizeRoute = '',
        bool $hideEnd = false, string $endRoute = '',
        bool $hideDelete = false, bool $checkReconciled = true, string $deleteRoute = '', string $textDeleteModal = '',
        bool $hideDivider1 = false, bool $hideDivider2 = false, bool $hideDivider3 = false, bool $hideDivider4 = false,
        string $accordionActive = '',
        bool $hideRecurringMessage = false, string $textRecurringType = '', bool $hideStatusMessage = false, string $textStatusMessage = '',
        bool $hideCreated = false, bool $hideSend = false, bool $hideMarkSent = false, string $markSentRoute = '', string $textMarkSent = '',
        bool $hideReceive = false, bool $hideMarkReceived = false, string $markReceivedRoute = '', string $textMarkReceived = '',
        bool $hideGetPaid = false,
        bool $hideRestore = false, bool $hideAddPayment = false, bool $hideAcceptPayment = false, string $transactionEmailRoute = '', string $transactionEmailTemplate = '',
        bool $hideMakePayment = false,
        bool $hideSchedule = false, bool $hideChildren = false,
        bool $hideAttachment = false, $attachment = [],
        $documentTemplate = '', $logo = '', $backgroundColor = '',
        bool $hideFooter = false, bool $hideCompanyLogo = false, bool $hideCompanyDetails = false,
        bool $hideCompanyName = false, bool $hideCompanyAddress = false, bool $hideCompanyTaxNumber = false, bool $hideCompanyPhone = false, bool $hideCompanyEmail = false, bool $hideContactInfo = false,
        bool $hideContactName = false, bool $hideContactAddress = false, bool $hideContactTaxNumber = false, bool $hideContactPhone = false, bool $hideContactEmail = false,
        bool $hideOrderNumber = false, bool $hideDocumentNumber = false, bool $hideIssuedAt = false, bool $hideDueAt = false,
        string $textDocumentTitle = '', string $textDocumentSubheading = '',
        string $textContactInfo = '', string $textDocumentNumber = '', string $textOrderNumber = '', string $textIssuedAt = '', string $textDueAt = '',
        bool $hideItems = false, bool $hideName = false, bool $hideDescription = false, bool $hideQuantity = false, bool $hidePrice = false, bool $hideDiscount = false, bool $hideAmount = false, bool $hideNote = false,
        string $textItems = '', string $textQuantity = '', string $textPrice = '', string $textAmount = ''
    ) {
        /* -- Main Start -- */
        $this->type = $type;
        $this->document = $document;
        $this->transactions = ($transactions) ? $transactions : $document->transactions;

        $this->permissionCreate = $this->getPermissionCreate($type, $permissionCreate);
        $this->permissionUpdate = $this->getPermissionUpdate($type, $permissionUpdate);
        $this->permissionDelete = $this->getPermissionDelete($type, $permissionDelete);
        /* -- Main End -- */

        /* -- Buttons Start -- */
        $this->textPage = $this->getTextPage($type, $textPage);
        $this->hideCreate = $hideCreate;
        $this->createRoute = $this->getCreateRoute($type, $createRoute);
        $this->textCreate = $this->getTextCreate($type, $textCreate);

        $this->hideButtonStatuses = $this->getHideButtonStatuses($type, $hideButtonStatuses);
        $this->hideEdit = $hideEdit;
        $this->editRoute = $this->getEditRoute($type, $editRoute);
        $this->showRoute = $this->getShowRoute($type, $showRoute);

        $this->hideMoreActions = $hideMoreActions;
        $this->hideDuplicate = $hideDuplicate;
        $this->duplicateRoute = $this->getDuplicateRoute($type, $duplicateRoute);

        $this->hidePrint = $hidePrint;
        $this->printRoute = $this->getPrintRoute($type, $printRoute);

        $this->hideShare = $hideShare;
        $this->shareRoute = $this->getShareRoute($type, $shareRoute);
        $this->signedUrl = $this->getSignedUrl($type, $signedUrl);

        $this->hideEmail = $hideEmail;
        $this->emailRoute = $this->getEmailRoute($type, $emailRoute);
        $this->textEmail = $this->getTextEmail($type, $textEmail);

        $this->hidePdf = $hidePdf;
        $this->pdfRoute = $this->getPdfRoute($type, $pdfRoute);

        $this->hideCancel = $hideCancel;
        $this->cancelledRoute = $this->getCancelledRoute($type, $cancelledRoute);
        $this->restoreRoute = $this->getRestoreRoute($type, $restoreRoute);

        $this->hideCustomize = $hideCustomize;
        $this->permissionCustomize = $this->getPermissionCustomize($type, $permissionCustomize);
        $this->customizeRoute = $this->getCustomizeRoute($type, $customizeRoute);

        $this->hideEnd = $hideEnd;
        $this->endRoute = $this->getEndRoute($type, $endRoute);

        $this->hideDelete = $hideDelete;
        $this->checkReconciled = $checkReconciled;
        $this->deleteRoute = $this->getDeleteRoute($type, $deleteRoute);
        $this->textDeleteModal = $textDeleteModal;

        $this->hideDivider1 = $hideDivider1;
        $this->hideDivider2 = $hideDivider2;
        $this->hideDivider3 = $hideDivider3;
        $this->hideDivider4 = $hideDivider4;
        /* -- Buttons End -- */

        /* -- Content Start -- */
        $this->accordionActive = $this->getAccordionActive($type, $accordionActive);
        $this->hideRecurringMessage = $hideRecurringMessage;
        $this->textRecurringType = $this->getTextRecurringType($type, $textRecurringType);

        $this->hideStatusMessage = $hideStatusMessage;
        $this->textStatusMessage = $this->getTextStatusMessage($type, $textStatusMessage);

        $this->hideCreated = $hideCreated;

        $this->hideSend = $hideSend;
        $this->hideMarkSent = $hideMarkSent;
        $this->textMarkSent = $this->getTextMarkSent($type, $textMarkSent);
        $this->markSentRoute = $this->getMarkSentRoute($type, $markSentRoute);

        $this->hideReceive = $hideReceive;
        $this->hideMarkReceived = $hideMarkReceived;
        $this->textMarkReceived = $this->getTextMarkReceived($type, $textMarkReceived);
        $this->markReceivedRoute = $this->getMarkReceivedRoute($type, $markReceivedRoute);

        $this->hideGetPaid = $hideGetPaid;

        $this->hideAddPayment = $hideAddPayment;
        $this->hideAcceptPayment = $hideAcceptPayment;

        $this->transactionEmailRoute = $this->getTransactionEmailRoute($type, $transactionEmailRoute);
        $this->transactionEmailTemplate = $this->getTransactionEmailTemplate($type, $transactionEmailTemplate);

        $this->hideRestore = $this->getHideRestore($hideRestore);

        $this->hideMakePayment = $hideMakePayment;

        $this->hideSchedule = $hideSchedule;
        $this->hideChildren = $hideChildren;

        $this->hideAttachment = $hideAttachment;
        $this->attachment = '';

        if (! empty($attachment)) {
            $this->attachment = $attachment;
        } else if (! empty($document)) {
            $this->attachment = $document->attachment;
        }
        /* -- Content End -- */

        /* -- Template Start -- */
        $this->documentTemplate = $this->getDocumentTemplate($type, $documentTemplate);
        $this->logo = $this->getLogo($logo);
        $this->backgroundColor = $this->getBackgroundColor($type, $backgroundColor);

        $this->hideFooter = $hideFooter;
        $this->hideCompanyLogo = $hideCompanyLogo;
        $this->hideCompanyDetails = $hideCompanyDetails;
        $this->hideCompanyName = $hideCompanyName;
        $this->hideCompanyAddress = $hideCompanyAddress;
        $this->hideCompanyTaxNumber = $hideCompanyTaxNumber;
        $this->hideCompanyPhone = $hideCompanyPhone;
        $this->hideCompanyEmail = $hideCompanyEmail;
        $this->hideContactInfo = $hideContactInfo;
        $this->hideContactName = $hideContactName;
        $this->hideContactAddress = $hideContactAddress;
        $this->hideContactTaxNumber = $hideContactTaxNumber;
        $this->hideContactPhone = $hideContactPhone;
        $this->hideContactEmail = $hideContactEmail;
        $this->hideOrderNumber = $hideOrderNumber;
        $this->hideDocumentNumber = $hideDocumentNumber;
        $this->hideIssuedAt = $hideIssuedAt;
        $this->hideDueAt = $hideDueAt;

        $this->textDocumentTitle = $this->getTextDocumentTitle($type, $textDocumentTitle);
        $this->textDocumentSubheading = $this->getTextDocumentSubheading($type, $textDocumentSubheading);
        $this->textContactInfo = $this->getTextContactInfo($type, $textContactInfo);
        $this->textIssuedAt = $this->getTextIssuedAt($type, $textIssuedAt);
        $this->textDocumentNumber = $this->getTextDocumentNumber($type, $textDocumentNumber);
        $this->textDueAt = $this->getTextDueAt($type, $textDueAt);
        $this->textOrderNumber = $this->getTextOrderNumber($type, $textOrderNumber);

        $this->hideItems = $this->getHideItems($type, $hideItems, $hideName, $hideDescription);
        $this->hideName = $this->getHideName($type, $hideName);
        $this->hideDescription = $this->getHideDescription($type, $hideDescription);
        $this->hideQuantity = $this->getHideQuantity($type, $hideQuantity);
        $this->hidePrice = $this->getHidePrice($type, $hidePrice);
        $this->hideDiscount = $this->getHideDiscount($type, $hideDiscount);
        $this->hideAmount = $this->getHideAmount($type, $hideAmount);
        $this->hideNote = $hideNote;

        $this->textItems = $this->getTextItems($type, $textItems);
        $this->textQuantity = $this->getTextQuantity($type, $textQuantity);
        $this->textPrice = $this->getTextPrice($type, $textPrice);
        $this->textAmount = $this->getTextAmount($type, $textAmount);
        /* -- Template End -- */

        // Set Parent data
        $this->setParentData();
    }
}
