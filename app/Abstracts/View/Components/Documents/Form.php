<?php

namespace App\Abstracts\View\Components\Documents;

use App\Abstracts\View\Component;
use App\Interfaces\Utility\DocumentNumber;
use App\Models\Common\Contact;
use App\Models\Document\Document;
use App\Models\Setting\Currency;
use App\Models\Setting\Tax;
use App\Traits\Documents;
use App\Traits\Tailwind;
use App\Traits\ViewComponents;
use App\Utilities\Date;
use Illuminate\Support\Str;

use App\Abstracts\View\Components\Documents\Concerns\HasFormContactsAndCurrencies;
use App\Abstracts\View\Components\Documents\Concerns\HasFormDatesAndNumbers;
use App\Abstracts\View\Components\Documents\Concerns\HasFormItemsAndTemplates;

abstract class Form extends Component
{
    use HasFormContactsAndCurrencies;
    use HasFormDatesAndNumbers;
    use HasFormItemsAndTemplates;


    use Documents, Tailwind, ViewComponents;

    public const OBJECT_TYPE = 'document';
    public const DEFAULT_TYPE = 'invoice';
    public const DEFAULT_PLURAL_TYPE = 'invoices';

    /* -- Main Start -- */
    public $type;

    public $document;

    public $model;

    public $currencies;

    public $currency;

    public $currency_code;

    public $taxes;
    /* -- Main End -- */

    /* -- Content Start -- */
    /* -- Form Start -- */
    public $formId;

    public $formRoute;

    public $formMethod;
    /* -- Form End -- */

    /* -- Company Start -- */
    /** @var bool */
    public $hideCompany;

    /** @var string */
    public $textSectionCompaniesTitle;

    /** @var string */
    public $textSectionCompaniesDescription;

    /** @var bool */
    public $hideLogo;

    /** @var bool */
    public $hideCompanyEdit;
    /* -- Company End -- */

    /* -- Main Start -- */
    /** @var string */
    public $textSectionMainTitle;

    /** @var string */
    public $textSectionMainDescription;

    /* -- Metadata Start -- */
    /** @var string */
    public $typeContact;

    /** @var string */
    public $textContact;

    public $contact;

    public $contacts;

    /** @var string */
    public $searchContactRoute;

    /** @var string */
    public $createContactRoute;

    /** @var string */
    public $textAddContact;

    /** @var string */
    public $textCreateNewContact;

    /** @var string */
    public $textEditContact;

    /** @var string */
    public $textContactInfo;

    /** @var string */
    public $textChooseDifferentContact;

    /** @var bool */
    public $hideDocumentTitle;

    /** @var bool */
    public $hideDocumentSubheading;

    /** @var string */
    public $title;

    /** @var string */
    public $subheading;

    /** @var bool */
    public $hideIssuedAt;

    /** @var string */
    public $textIssuedAt;

    /** @var string */
    public $issuedAt;

    /** @var bool */
    public $hideDueAt;

    /** @var string */
    public $textDueAt;

    /** @var string */
    public $dueAt;

    /** @var string */
    public $periodDueAt;

    /** @var bool */
    public $hideDocumentNumber;

    /** @var string */
    public $textDocumentNumber;

    /** @var string */
    public $documentNumber;

    /** @var bool */
    public $hideOrderNumber;

    /** @var string */
    public $textOrderNumber;

    /** @var string */
    public $orderNumber;
    /* -- Metadata End -- */

    /* -- Items Start -- */
    /** @var bool */
    public $hideEditItemColumns;

    /** @var bool */
    public $hideItems;

    /** @var bool */
    public $hideItemName;

    /** @var bool */
    public $hideSettingItemName;

    /** @var string */
    public $textItemName;

    /** @var bool */
    public $hideItemDescription;

    /** @var bool */
    public $hideSettingItemDescription;

    /** @var string */
    public $textItemDescription;

    /** @var bool */
    public $hideItemQuantity;

    /** @var bool */
    public $hideSettingItemQuantity;

    /** @var string */
    public $textItemQuantity;

    /** @var bool */
    public $hideItemPrice;

    /** @var bool */
    public $hideSettingItemPrice;

    /** @var string */
    public $textItemPrice;

    /** @var bool */
    public $hideItemAmount;

    /** @var bool */
    public $hideSettingItemAmount;

    /** @var string */
    public $textItemAmount;

    /** @var bool */
    public $hideDiscount;

    /** @var bool */
    public $isSalePrice;

    /** @var bool */
    public $isPurchasePrice;

    /** @var int */
    public $searchCharLimit;
    /* -- Items End -- */

    /** @var string */
    public $notes;
    /* -- Main End -- */

    /* -- Recurring Start -- */
    /** @var bool */
    public $showRecurring;
    /* -- End Start -- */

    /* -- Advanced Start -- */
    /** @var bool */
    public $hideAdvanced;

    /** @var string */
    public $textSectionAdvancedTitle;

    /** @var string */
    public $textSectionAdvancedDescription;

    /** @var bool */
    public $hideFooter;

    /** @var string */
    public $classFooter;

    /** @var string */
    public $footer;

    /** @var bool */
    public $hideCategory;

    /** @var string */
    public $classCategory;

    /** @var string */
    public $typeCategory;

    public $categoryId;

    /** @var bool */
    public $hideAttachment;

    /** @var string */
    public $classAttachment;

    /** @var bool */
    public $hideTemplate;

    /** @var bool */
    public $hideBackgroundColor;

    /** @var array */
    public $templates;

    /** @var string */
    public $template;

    /** @var string */
    public $backgroundColor;
    /* -- Advanced End -- */

    /* -- Buttons End -- */
    /** @var bool */
    public $hideButtons;

    /** @var string */
    public $cancelRoute;

    /** @var bool */
    public $hideSendTo;
    /* -- Buttons End -- */

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        string $type, $model = false, $document = false, $currencies = false, $currency = false, $currency_code = false,
        string $formId = 'document', $formRoute = '', $formMethod = '',
        bool $hideCompany = false, string $textSectionCompaniesTitle = '', string $textSectionCompaniesDescription = '',
        bool $hideLogo = false, bool $hideCompanyEdit = false,
        string $textSectionMainTitle = '', string $textSectionMainDescription = '',
        string $typeContact = '', string $textContact = '', $contacts = [], $contact = false, string $searchContactRoute = '', string $createContactRoute = '',
        string $textAddContact = '', string $textCreateNewContact = '', string $textEditContact = '', string $textContactInfo = '', string $textChooseDifferentContact = '',
        bool $hideDocumentTitle = false, bool $hideDocumentSubheading = false, string $title = '', string $subheading = '',
        bool $hideIssuedAt = false, string $textIssuedAt = '', string $issuedAt = '', bool $hideDueAt = false, string $textDueAt = '', string $dueAt = '', $periodDueAt = '',
        bool $hideDocumentNumber = false, string $textDocumentNumber = '', string $documentNumber = '', bool $hideOrderNumber = false, string $textOrderNumber = '', string $orderNumber = '',
        bool $hideEditItemColumns = false, bool $hideItems = false, bool $hideItemName = false, bool $hideSettingItemName = false, string $textItemName = '', bool $hideItemDescription = false, bool $hideSettingItemDescription = false, string $textItemDescription = '',
        bool $hideItemQuantity = false, bool $hideSettingItemQuantity = false, string $textItemQuantity = '', bool $hideItemPrice = false, bool $hideSettingItemPrice = false, string $textItemPrice = '', bool $hideItemAmount = false, bool $hideSettingItemAmount = false, string $textItemAmount = '',
        bool $hideDiscount = false, bool $isSalePrice = false, bool $isPurchasePrice = false, int $searchCharLimit = 0, string $notes = '',
        bool $showRecurring = false,
        bool $hideAdvanced = false, string $textSectionAdvancedTitle = '', string $textSectionAdvancedDescription = '',
        bool $hideFooter = false, string $classFooter = '', string $footer = '',
        bool $hideCategory = false, string $classCategory = '', string $typeCategory = '', $categoryId = '',
        bool $hideAttachment = false, string $classAttachment = '',
        bool $hideTemplate = false, bool $hideBackgroundColor = false,
        array $templates = [], string $template = '', string $backgroundColor = '',
        bool $hideButtons = false, string $cancelRoute = '', $hideSendTo = false
    ) {
        $this->type = $type;

        $this->model = ! empty($model) ? $model : $document;
        $this->document = $this->model;

        $this->currency_code = ! empty($this->currency) ? $this->currency->code : default_currency();
        $this->currency = $this->getCurrency($document, $currency, $currency_code);
        $this->currencies = $this->getCurrencies($currencies);

        $this->taxes = Tax::enabled()->orderBy('name')->get()->pluck('title', 'id');

        /* -- Content Start -- */
        /* -- Form Start -- */
        $this->formId = $formId;
        $this->formRoute = $this->getFormRoute($type, $formRoute, $this->model);
        $this->formMethod = $this->getFormMethod($type, $formMethod, $this->model);
        /* -- Form End -- */

        /* -- Company Start -- */
        $this->hideCompany = $hideCompany;
        $this->textSectionCompaniesTitle = $this->getTextSectionCompaniesTitle($type, $textSectionCompaniesTitle);
        $this->textSectionCompaniesDescription = $this->getTextSectionCompaniesDescription($type, $textSectionCompaniesDescription);
        $this->hideLogo = $hideLogo;
        $this->hideCompanyEdit = $hideCompanyEdit;
        /** Company End */

        /* -- Main Start -- */
        $this->textSectionMainTitle = $this->getTextSectionMainTitle($type, $textSectionMainTitle);
        $this->textSectionMainDescription = $this->getTextSectionMainDescription($type, $textSectionMainDescription);

        /* -- Metadata Start -- */
        $this->typeContact = $this->getTypeContact($type, $typeContact);
        $this->contact = $this->getContact($contact, $document);
        $this->contacts = $this->getContacts($type, $document, $contacts);

        $this->searchContactRoute = $this->getSearchContactRoute($type, $searchContactRoute);
        $this->createContactRoute = $this->getCreateContactRoute($type, $createContactRoute);

        $this->textContact = $this->getTextContact($type, $textContact);
        $this->textAddContact = $this->getTextAddContact($type, $textAddContact);
        $this->textCreateNewContact = $this->getTextCreateNewContact($type, $textCreateNewContact);
        $this->textEditContact = $this->getTextEditContact($type, $textEditContact);
        $this->textContactInfo = $this->getTextContactInfo($type, $textContactInfo);
        $this->textChooseDifferentContact = $this->getTextChooseDifferentContact($type, $textChooseDifferentContact);

        $this->hideDocumentTitle = $hideDocumentTitle;
        $this->hideDocumentSubheading = $hideDocumentSubheading;
        $this->title = $this->getTitleValue($type, $title);
        $this->subheading = $this->getSubheadingValue($type, $subheading);

        $this->hideIssuedAt = $hideIssuedAt;
        $this->textIssuedAt = $this->getTextIssuedAt($type, $textIssuedAt);
        $this->issuedAt = $this->getIssuedAt($type, $document, $issuedAt);

        $this->hideDueAt = $hideDueAt;
        $this->textDueAt = $this->getTextDueAt($type, $textDueAt);
        $this->dueAt = $this->getDueAt($type, $document, $dueAt);
        $this->periodDueAt = $this->getPeriodDueAt($type, $periodDueAt);

        $this->hideDocumentNumber = $hideDocumentNumber;
        $this->textDocumentNumber = $this->getTextDocumentNumber($type, $textDocumentNumber);
        $this->documentNumber = $this->getDocumentNumber($type, $document, $documentNumber);

        $this->hideOrderNumber = $hideOrderNumber;
        $this->textOrderNumber = $this->getTextOrderNumber($type, $textOrderNumber);
        $this->orderNumber = $this->getOrderNumber($type, $document, $orderNumber);
        /* -- Metadata End -- */

        /** Items Start */
        $this->hideEditItemColumns = $hideEditItemColumns;

        $this->hideItems = $this->getHideItems($type, $hideItems, $hideItemName, $hideItemDescription);
        $this->hideItemName = $this->getHideItemName($type, $hideItemName);
        $this->hideSettingItemName = $this->getHideSettingItemName($type, $hideSettingItemName);
        $this->textItemName = $this->getTextItemName($type, $textItemName);

        $this->hideItemDescription = $this->getHideItemDescription($type, $hideItemDescription);
        $this->hideSettingItemDescription = $this->getHideSettingItemDescription($type, $hideSettingItemDescription);
        $this->textItemDescription = $this->getTextItemDescription($type, $textItemDescription);

        $this->hideItemQuantity = $this->getHideItemQuantity($type, $hideItemQuantity);
        $this->hideSettingItemQuantity = $this->getHideSettingItemQuantity($type, $hideSettingItemQuantity);
        $this->textItemQuantity = $this->getTextItemQuantity($type, $textItemQuantity);

        $this->hideItemPrice = $this->getHideItemPrice($type, $hideItemPrice);
        $this->hideSettingItemPrice = $this->getHideSettingItemPrice($type, $hideSettingItemPrice);
        $this->textItemPrice = $this->getTextItemPrice($type, $textItemPrice);

        $this->hideItemAmount = $this->getHideItemAmount($type, $hideItemAmount);
        $this->hideSettingItemAmount = $this->getHideSettingItemAmount($type, $hideSettingItemAmount);
        $this->textItemAmount = $this->getTextItemAmount($type, $textItemAmount);

        $this->hideDiscount = $this->getHideDiscount($type, $hideDiscount);

        $this->isSalePrice = $isSalePrice;
        $this->isPurchasePrice = $isPurchasePrice;
        $this->searchCharLimit = $this->getSearchCharLimit($type, $searchCharLimit);
        /** Items End */

        /** Notes Start */
        $this->notes = $this->getNotesValue($notes);
        /** Notes End */
        /** Main End */

        /* -- Recurring Start -- */
        $this->showRecurring = $showRecurring;
        /* -- Recurring End -- */

        /* -- Advanced Start -- */
        $this->hideAdvanced = $hideAdvanced;
        $this->textSectionAdvancedTitle = $this->getTextSectionAdvancedTitle($type, $textSectionAdvancedTitle);
        $this->textSectionAdvancedDescription = $this->getTextSectionAdvancedDescription($type, $textSectionAdvancedDescription);

        $this->hideFooter = $hideFooter;
        $this->classFooter = !empty($classFooter) ? $classFooter : 'sm:col-span-3';
        $this->footer = $this->getFooterValue($footer);

        $this->hideCategory = $hideCategory;
        $this->classCategory = !empty($classCategory) ? $classCategory : 'sm:col-span-4 grid gap-x-8 gap-y-3';
        $this->typeCategory = $this->getTypeCategory($type, $typeCategory);
        $this->categoryId = $this->getCategoryId($type, $categoryId);

        $this->hideAttachment = $hideAttachment;
        $this->classAttachment = !empty($classAttachment) ? $classAttachment : 'sm:col-span-4';

        $this->hideTemplate = $hideTemplate;
        $this->hideBackgroundColor = $hideBackgroundColor;

        $this->templates = $this->getTemplates($type, $templates);
        $this->template = $this->getDocumentTemplate($type, $template);
        $this->backgroundColor = $this->getBackgroundColor($type, $backgroundColor);
        /** Advanced End */

        /** Buttons Start */
        $this->hideButtons = $hideButtons;
        $this->cancelRoute = $this->getCancelRoute($type, $cancelRoute);
        $this->hideSendTo = $hideSendTo;
        /** Buttons End */
        /* -- Content End -- */

        // Set Parent data
        $this->setParentData();
    }

}
