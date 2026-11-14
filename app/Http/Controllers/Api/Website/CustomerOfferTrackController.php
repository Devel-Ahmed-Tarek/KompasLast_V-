<?php

namespace App\Http\Controllers\Api\Website;

use App\Exceptions\CustomerAccountRequiredException;
use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\ConfigApp;
use App\Models\Offer;
use App\Models\OfferExecution;
use App\Models\ReviewCompany;
use App\Models\Shopping_list;
use App\Models\User;
use App\Mail\SupervisorOfferConfirmed;
use App\Notifications\PaymentNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CustomerOfferTrackController extends Controller
{
    public function show(Request $request, string $token)
    {
        try {
            $offer = $this->findTrackable($token);
        } catch (CustomerAccountRequiredException $e) {
            return $this->loginRequiredResponse($e);
        }
        if (! $offer) {
            return HelperFunc::sendResponse(404, 'Anfrage nicht gefunden');
        }

        $lang = $this->resolveLang($request, $offer);

        $purchases = Shopping_list::where('offer_id', $offer->id)
            ->with(['company:id,name,email,phone,img,status,ban'])
            ->latest()
            ->get();

        $executions = OfferExecution::where('offer_id', $offer->id)->get()->keyBy('company_id');

        $companies = $purchases->map(function ($row) use ($executions) {
            $company = $row->company;
            if (! $company) {
                return null;
            }

            $execution = $executions->get($company->id);

            return [
                'id'           => $company->id,
                'name'         => $company->name,
                'img'          => $company->img ? url($company->img) : null,
                'purchased_at' => optional($row->created_at)->toIso8601String(),
                'is_executed'  => (bool) ($execution?->is_executed),
            ];
        })->filter()->values();

        $serviceName = null;
        if ($offer->type) {
            $serviceName = method_exists($offer->type, 'getTranslation')
                ? ($offer->type->getTranslation('name', $lang, false) ?: $offer->type->name)
                : $offer->type->name;
            if (is_array($serviceName)) {
                $serviceName = $serviceName[$lang] ?? $serviceName['de'] ?? reset($serviceName) ?: null;
            }
        }

        $steps = $this->buildSteps(
            $offer,
            $companies->count(),
            $companies->contains(fn ($c) => $c['is_executed']),
            $lang
        );

        return HelperFunc::sendResponse(200, 'OK', [
            'offer' => [
                'id'                => $offer->id,
                'name'              => $offer->name,
                'email'             => $this->maskEmail($offer->email),
                'phone'             => $this->maskPhone($offer->phone),
                'service'           => $serviceName,
                'type_id'           => $offer->type_id,
                'city'              => $offer->city,
                'ort'               => $offer->ort,
                'adresse'           => $offer->adresse,
                'zipcode'           => $offer->zipcode,
                'date'              => $offer->date,
                'lang'              => $offer->lang,
                'confirm_status'    => $offer->confirm_status,
                'confirmed_at'      => optional($offer->confirmed_at)->toIso8601String(),
                'status'            => (bool) $offer->status,
                'completion_status' => $offer->completion_status,
                'created_at'        => optional($offer->created_at)->toIso8601String(),
                'companies_count'   => $companies->count(),
                'can_confirm'       => $offer->confirm_status !== 'confirmed',
            ],
            'steps'       => $steps,
            'companies'   => $companies,
            'track_token' => $offer->track_token,
        ]);
    }

    public function confirm(string $token)
    {
        try {
            $offer = $this->findTrackable($token);
        } catch (CustomerAccountRequiredException $e) {
            return $this->loginRequiredResponse($e);
        }
        if (! $offer) {
            return HelperFunc::sendResponse(404, 'Anfrage nicht gefunden');
        }

        if ($offer->confirm_status === 'confirmed') {
            return HelperFunc::sendResponse(200, 'Bereits bestätigt', [
                'track_token' => $offer->track_token,
                'offer_id'    => $offer->id,
            ]);
        }

        $config = ConfigApp::first();

        DB::beginTransaction();
        try {
            $offer->confirm_status = 'confirmed';
            $offer->confirmed_at   = now();
            // Keep confirm_token cleared after use, but NEVER clear track_token.
            $offer->confirm_token  = null;
            if (! $offer->track_token) {
                $offer->track_token = \Illuminate\Support\Str::random(64);
            }
            $offer->save();

            $distributionRan = ! ($config && $config->offer_flow == 1);
            $offer->loadMissing('type');
            HelperFunc::notifySupervisor(new SupervisorOfferConfirmed($offer, $distributionRan));

            if (! ($config && $config->offer_flow == 1)) {
                app(HomePageController::class)->processOfferDistributionPublic($offer);
            }

            DB::commit();

            return HelperFunc::sendResponse(200, 'Bestätigt', [
                'track_token' => $offer->track_token,
                'offer_id'    => $offer->id,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return HelperFunc::sendResponse(500, 'Bestätigung fehlgeschlagen: ' . $e->getMessage());
        }
    }

    public function markExecuted(Request $request, string $token)
    {
        try {
            $offer = $this->findTrackable($token);
        } catch (CustomerAccountRequiredException $e) {
            return $this->loginRequiredResponse($e);
        }
        if (! $offer) {
            return HelperFunc::sendResponse(404, 'Anfrage nicht gefunden');
        }

        $validator = Validator::make($request->all(), [
            'company_id'  => 'nullable|exists:users,id',
            'is_executed' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation failed', $validator->errors());
        }

        if ($request->filled('company_id')) {
            $bought = Shopping_list::where('offer_id', $offer->id)
                ->where('user_id', $request->company_id)
                ->exists();
            if (! $bought) {
                return HelperFunc::sendResponse(403, 'Unternehmen gehört nicht zu dieser Anfrage');
            }
        }

        $ex = OfferExecution::updateOrCreate(
            [
                'company_id' => $request->input('company_id'),
                'offer_id'   => $offer->id,
            ],
            [
                'is_executed' => $request->boolean('is_executed'),
            ]
        );

        return HelperFunc::sendResponse(201, 'Gespeichert', $ex);
    }

    public function rate(Request $request, string $token)
    {
        try {
            $offer = $this->findTrackable($token);
        } catch (CustomerAccountRequiredException $e) {
            return $this->loginRequiredResponse($e);
        }
        if (! $offer) {
            return HelperFunc::sendResponse(404, 'Anfrage nicht gefunden');
        }

        $validator = Validator::make($request->all(), [
            'stars'   => 'required|integer|between:1,5',
            'comment' => 'nullable|string',
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation failed', $validator->errors());
        }

        $bought = Shopping_list::where('offer_id', $offer->id)
            ->where('user_id', $request->user_id)
            ->exists();

        if (! $bought) {
            return HelperFunc::sendResponse(403, 'Unternehmen gehört nicht zu dieser Anfrage');
        }

        $review = ReviewCompany::create([
            'name'    => $offer->name,
            'email'   => $offer->email,
            'stars'   => $request->stars,
            'comment' => $request->input('comment'),
            'user_id' => $request->user_id,
        ]);

        $company = User::find($request->user_id);
        if ($company) {
            $company->notify(new PaymentNotification([
                'type'      => 'review',
                'review_id' => $review->id,
                'mgs'       => [
                    'en' => 'You have received a new review.',
                    'de' => 'Sie haben eine neue Bewertung erhalten.',
                    'it' => 'Hai ricevuto una nuova recensione.',
                    'fr' => 'Vous avez reçu un nouvel avis.',
                ],
                'reviewer'  => $review->name,
                'stars'     => $review->stars,
            ]));
        }

        return HelperFunc::sendResponse(201, 'Bewertung gespeichert', $review);
    }

    public function announce(Request $request, string $token)
    {
        try {
            $offer = $this->findTrackable($token);
        } catch (CustomerAccountRequiredException $e) {
            return $this->loginRequiredResponse($e);
        }
        if (! $offer) {
            return HelperFunc::sendResponse(404, 'Anfrage nicht gefunden');
        }

        $validator = Validator::make($request->all(), [
            'comment' => 'nullable|string|max:5000',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation failed', $validator->errors());
        }

        $announcement = Announcement::create([
            'offer_id' => $offer->id,
            'comment'  => $request->input('comment'),
        ]);

        return HelperFunc::sendResponse(201, 'Nachricht gespeichert', $announcement);
    }

    /**
     * Accept track_token, legacy confirm_token, or customer email (latest offer).
     */
    private function findTrackable(string $token): ?Offer
    {
        return Offer::findByTrackIdentifier($token, ['type']);
    }

    private function loginRequiredResponse(CustomerAccountRequiredException $e)
    {
        $loginUrl = env('CUSTOMER_LOGIN_URL')
            ?: (rtrim(ConfigApp::query()->value('website') ?: 'https://auftragkompass.de', '/') . '/de/login');

        return HelperFunc::sendResponse(403, 'Login required to track this email', [
            'code'      => 'login_required',
            'email'     => $e->email,
            'login_url' => $loginUrl,
        ]);
    }

    private function resolveLang(Request $request, ?Offer $offer = null): string
    {
        $allowed = ['de', 'en', 'ar', 'fr', 'it'];
        $candidates = [
            $request->query('lang'),
            $request->header('Accept-Language'),
            $offer?->lang,
            'de',
        ];

        foreach ($candidates as $candidate) {
            if (! $candidate) {
                continue;
            }
            $short = strtolower(substr((string) $candidate, 0, 2));
            if (in_array($short, $allowed, true)) {
                return $short;
            }
        }

        return 'de';
    }

    private function stepLabels(string $lang): array
    {
        $labels = [
            'de' => [
                'received'  => 'Anfrage eingegangen',
                'confirmed' => 'E-Mail bestätigt',
                'forwarded' => 'An Unternehmen weitergeleitet',
                'interest'  => 'Unternehmen interessiert',
                'executed'  => 'Auftrag ausgeführt',
                'done'      => 'Abgeschlossen',
            ],
            'en' => [
                'received'  => 'Request received',
                'confirmed' => 'Email confirmed',
                'forwarded' => 'Forwarded to companies',
                'interest'  => 'Company interested',
                'executed'  => 'Job completed',
                'done'      => 'Finished',
            ],
            'ar' => [
                'received'  => 'تم استلام الطلب',
                'confirmed' => 'تم تأكيد البريد',
                'forwarded' => 'تم التحويل للشركات',
                'interest'  => 'شركة مهتمة',
                'executed'  => 'تم تنفيذ الطلب',
                'done'      => 'مكتمل',
            ],
            'fr' => [
                'received'  => 'Demande reçue',
                'confirmed' => 'E-mail confirmé',
                'forwarded' => 'Transmise aux entreprises',
                'interest'  => 'Entreprise intéressée',
                'executed'  => 'Travail réalisé',
                'done'      => 'Terminé',
            ],
            'it' => [
                'received'  => 'Richiesta ricevuta',
                'confirmed' => 'E-mail confermata',
                'forwarded' => 'Inoltrata alle aziende',
                'interest'  => 'Azienda interessata',
                'executed'  => 'Lavoro eseguito',
                'done'      => 'Completata',
            ],
        ];

        return $labels[$lang] ?? $labels['de'];
    }

    private function buildSteps(Offer $offer, int $companyCount, bool $executed, string $lang = 'de'): array
    {
        $confirmed   = $offer->confirm_status === 'confirmed';
        $distributed = $confirmed && ((bool) $offer->status || $companyCount > 0);
        $labels      = $this->stepLabels($lang);

        $defs = [
            ['key' => 'received',   'label' => $labels['received'],  'done' => true],
            ['key' => 'confirmed',  'label' => $labels['confirmed'], 'done' => $confirmed],
            ['key' => 'forwarded',  'label' => $labels['forwarded'], 'done' => $distributed],
            ['key' => 'interest',   'label' => $labels['interest'],  'done' => $companyCount > 0],
            ['key' => 'executed',   'label' => $labels['executed'],  'done' => $executed],
            ['key' => 'done',       'label' => $labels['done'],      'done' => $executed],
        ];

        $currentSet = false;
        foreach ($defs as &$step) {
            if ($step['done']) {
                $step['current'] = false;
                continue;
            }
            if (! $currentSet) {
                $step['current'] = true;
                $currentSet = true;
            } else {
                $step['current'] = false;
            }
        }
        unset($step);

        if (! $currentSet) {
            $defs[count($defs) - 1]['current'] = true;
        }

        return $defs;
    }

    private function maskEmail(?string $email): ?string
    {
        if (! $email || ! str_contains($email, '@')) {
            return $email;
        }
        [$local, $domain] = explode('@', $email, 2);
        $keep = max(1, min(2, strlen($local)));

        return substr($local, 0, $keep) . str_repeat('*', max(1, strlen($local) - $keep)) . '@' . $domain;
    }

    private function maskPhone(?string $phone): ?string
    {
        if (! $phone) {
            return $phone;
        }
        $digits = preg_replace('/\D+/', '', $phone) ?: '';
        if (strlen($digits) < 4) {
            return '****';
        }

        return str_repeat('*', max(0, strlen($digits) - 4)) . substr($digits, -4);
    }
}
