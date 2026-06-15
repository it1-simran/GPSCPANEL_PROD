<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Calls the GrowSpace SIM details API to enrich ICCID with SIM operator info.
 *
 * Endpoint: https://app.growspace.in/grow-space-services/api/get-all-details-by-iccid-v2
 * Auth:     "Auth_token" header
 * Request:  { "iccid": ["<iccid>", ...] }
 * Response: { data: [ { iccid, profileDetails: [ { operator, msisdn, ... } ] } ] }
 */
class GrowSpaceSimService
{
    protected string $endpoint;
    protected string $authToken;

    public function __construct()
    {
        $this->endpoint  = env('GROWSPACE_API_URL', 'https://app.growspace.in/grow-space-services/api/get-all-details-by-iccid-v2');
        $this->authToken = env('GROWSPACE_AUTH_TOKEN', 'atk2dpl7qx4tz1bva');
    }

    /**
     * Look up SIM profile details for a given ICCID.
     *
     * @return array {
     *     iccid: string|null,
     *     plan_status: string|null,
     *     organization: string|null,
     *     sims: array<int, ['operator' => string, 'msisdn' => string, 'imsi' => string, 'profile_slot' => int, 'status' => string]>,
     * }
     */
    public function lookupByIccid(string $iccid): array
    {
        $iccid = trim($iccid);
        if ($iccid === '') {
            return $this->emptyResult();
        }

        try {
            $http = Http::withHeaders([
                'Auth_token'   => $this->authToken,
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ])
            ->timeout(15)
            ->connectTimeout(10);

            // Allow disabling SSL verification for local Windows/WAMP dev environments
            // (set GROWSPACE_VERIFY_SSL=false in .env). NEVER set this to false in production.
            if (env('GROWSPACE_VERIFY_SSL', true) === false) {
                $http = $http->withoutVerifying();
            }

            $response = $http->post($this->endpoint, [
                'iccid' => [$iccid],
            ]);

            if (!$response->successful()) {
                Log::warning('GrowSpace API non-2xx', [
                    'iccid'  => $iccid,
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return $this->emptyResult($iccid);
            }

            $body = $response->json();
            if (empty($body['data']) || !is_array($body['data'])) {
                return $this->emptyResult($iccid);
            }

            $record = $body['data'][0];
            $sims = [];

            foreach (($record['profileDetails'] ?? []) as $sim) {
                $sims[] = [
                    'operator'     => $sim['operator']      ?? null,
                    'msisdn'       => $sim['msisdn']        ?? null,
                    'imsi'         => $sim['imsi']          ?? null,
                    'profile_slot' => $sim['profileSlot']   ?? null,
                    'status'       => $sim['profileStatus'] ?? null,
                ];
            }

            // Sort by profile slot if available
            usort($sims, fn($a, $b) => ($a['profile_slot'] ?? 99) <=> ($b['profile_slot'] ?? 99));

            return [
                'iccid'           => $record['iccid']            ?? $iccid,
                'plan_status'     => $record['planStatus']       ?? null,
                'organization'    => $record['organizationName'] ?? null,
                'activation_date' => $record['activationDate']   ?? null,
                'expiry_date'     => $record['expiryDate']       ?? null,
                'sims'            => $sims,
            ];

        } catch (Exception $e) {
            Log::error('GrowSpace API call failed: ' . $e->getMessage(), [
                'iccid' => $iccid,
            ]);
            return $this->emptyResult($iccid);
        }
    }

    /**
     * Result shape when lookup fails or returns no data
     */
    protected function emptyResult(?string $iccid = null): array
    {
        return [
            'iccid'           => $iccid,
            'plan_status'     => null,
            'organization'    => null,
            'activation_date' => null,
            'expiry_date'     => null,
            'sims'            => [],
        ];
    }
}
