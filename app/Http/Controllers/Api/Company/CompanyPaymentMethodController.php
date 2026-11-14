<?php

namespace App\Http\Controllers\Api\Company;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\ConfigApp;

class CompanyPaymentMethodController extends Controller
{
    public function index()
    {
        $config  = ConfigApp::first();
        $methods = [];

        $bankLabel = $config?->bank_name ?: 'Banküberweisung';
        $ibanHint  = $config?->bank_number
            ? $this->maskIban((string) $config->bank_number)
            : null;

        $methods[] = [
            'id'         => 'bank_transfer',
            'type'       => 'bank_transfer',
            'label'      => $bankLabel,
            'iban_hint'  => $ibanHint,
            'bank_name'  => $config?->bank_name,
            'bank_ip'    => $config?->bank_ip,
            'is_default' => true,
            'status'     => 'active',
        ];

        return HelperFunc::sendResponse(200, 'OK', [
            'methods'                     => $methods,
            'can_add_card'                => false,
            'setup_intent_client_secret'  => null,
        ]);
    }

    private function maskIban(string $iban): string
    {
        $clean = preg_replace('/\s+/', '', $iban) ?: $iban;
        if (strlen($clean) <= 8) {
            return $clean;
        }

        return substr($clean, 0, 4) . ' **** ' . substr($clean, -4);
    }
}
