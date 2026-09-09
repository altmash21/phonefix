<?php

namespace App\Traits\Transactions;

use Illuminate\Support\Str;

trait HasTransactionLineActions
{
    /**
     * Get the line actions.
     *
     * @return array
     */
    public function getLineActionsAttribute()
    {
        $actions = [];

        $prefix = 'transactions';

        if (Str::contains($this->type, 'recurring')) {
            $prefix = 'recurring-transactions';
        }

        try {
            $actions[] = [
                'title' => trans('general.show'),
                'icon' => 'visibility',
                'url' => route($prefix . '.show', $this->id),
                'permission' => 'read-banking-transactions',
                'attributes' => [
                    'id' => 'index-line-actions-show-' . $this->type . '-'  . $this->id,
                ],
            ];
        } catch (\Exception $e) {}

        try {
            if (! $this->reconciled && empty($this->document_id) && $this->isNotTransferTransaction()) {
                $actions[] = [
                    'title' => trans('general.edit'),
                    'icon' => 'edit',
                    'url' => route($prefix . '.edit', $this->id),
                    'permission' => 'update-banking-transactions',
                    'attributes' => [
                        'id' => 'index-line-actions-edit-' . $this->type . '-'  . $this->id,
                    ],
                ];
            }
        } catch (\Exception $e) {}

        try {
            if (empty($this->document_id) 
                && $this->isNotTransferTransaction()
                && $this->isNotSplitTransaction()
            ) {
                $actions[] = [
                    'title' => trans('general.duplicate'),
                    'icon' => 'file_copy',
                    'url' => route($prefix . '.duplicate', $this->id),
                    'permission' => 'create-banking-transactions',
                    'attributes' => [
                        'id' => 'index-line-actions-duplicate-' . $this->type . '-'  . $this->id,
                    ],
                ];
            }
        } catch (\Exception $e) {}

        try {
            if (
                $this->is_splittable
                && empty($this->document_id)
                && empty($this->recurring)
                && $this->isNotTransferTransaction()
            ) {
                $connect = [
                    'type' => 'button',
                    'title' => trans('general.connect'),
                    'icon' => 'sensors',
                    'permission' => 'create-banking-transactions',
                    'attributes' => [
                        'id' => 'index-line-actions-connect-' . $this->type . '-'  . $this->id,
                        '@click' => 'onConnectTransactions(\'' . route('transactions.dial', $this->id) . '\')',
                    ],
                ];

                $actions[] = $connect;

                $actions[] = [
                    'type' => 'divider',
                ];
            }
        } catch (\Exception $e) {}

        try {
            $actions[] = [
                'title' => trans('general.print'),
                'icon' => 'print',
                'url' => route($prefix . '.print', $this->id),
                'permission' => 'read-banking-transactions',
                'attributes' => [
                    'id' => 'index-line-actions-print-' . $this->type . '-'  . $this->id,
                    'target' => '_blank',
                ],
            ];
        } catch (\Exception $e) {}

        try {
            $actions[] = [
                'title' => trans('general.download_pdf'),
                'icon' => 'picture_as_pdf',
                'url' => route($prefix . '.pdf', $this->id),
                'permission' => 'read-banking-transactions',
                'attributes' => [
                    'id' => 'index-line-actions-pdf-' . $this->type . '-'  . $this->id,
                    'target' => '_blank',
                ],
            ];
        } catch (\Exception $e) {}

        if ($prefix != 'recurring-transactions') {
            if ($this->isNotTransferTransaction()) {
                $actions[] = [
                    'type' => 'divider',
                ];

                try {
                    $actions[] = [
                        'type' => 'button',
                        'title' => trans('general.share_link'),
                        'icon' => 'share',
                        'url' => route('modals.transactions.share.create', $this->id),
                        'permission' => 'read-banking-transactions',
                        'attributes' => [
                            'id' => 'index-line-actions-share-' . $this->type . '-'  . $this->id,
                            '@click' => 'onShareLink("' . route('modals.transactions.share.create', $this->id) . '")',
                        ],
                    ];
                } catch (\Exception $e) {}

                try {
                    if (! empty($this->contact) && $this->contact->email) {
                        $actions[] = [
                            'type' => 'button',
                            'title' => trans('invoices.send_mail'),
                            'icon' => 'email',
                            'url' => route('modals.transactions.emails.create', $this->id),
                            'permission' => 'read-banking-transactions',
                            'attributes' => [
                                'id' => 'index-line-actions-send-email-' . $this->type . '-'  . $this->id,
                                '@click' => 'onSendEmail("' . route('modals.transactions.emails.create', $this->id) . '")',
                            ],
                        ];
                    }
                } catch (\Exception $e) {}

                $actions[] = [
                    'type' => 'divider',
                ];

                try {
                    if (! $this->reconciled) {
                        $actions[] = [
                            'type' => 'delete',
                            'icon' => 'delete',
                            'title' => ! empty($this->recurring) ? 'transactions' : 'recurring_template',
                            'route' => $prefix . '.destroy',
                            'permission' => 'delete-banking-transactions',
                            'model-name' => 'number',
                            'attributes' => [
                                'id' => 'index-line-actions-delete-' . $this->type . '-'  . $this->id,
                            ],
                            'model' => $this,
                        ];
                    }
                } catch (\Exception $e) {}
            }
        } else {
            if ($this->recurring && $this->recurring->status != 'ended') {
                try {
                    $actions[] = [
                        'title' => trans('general.end'),
                        'icon' => 'block',
                        'url' => route($prefix . '.end', $this->id),
                        'permission' => 'update-banking-transactions',
                        'attributes' => [
                            'id' => 'index-line-actions-end-' . $this->type . '-'  . $this->id,
                        ],
                    ];
                } catch (\Exception $e) {}
            }
        }

        return $actions;
    }
}
