<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Client for the MES (production-mis-backend) Purchase Order APIs.
 *
 * The PO is owned by MES (MongoDB) — CPanel is a thin client. This service is
 * the only place that talks to MES.
 *
 * Auth:  x-api-key: <CPANEL_API_KEY>  (shared secret; MES side reads the same key)
 * Base:  MES_API_URL   e.g. http://localhost:4000/api
 *
 * When MES_API_URL is not configured, every method short-circuits with an
 * honest "not configured" result so the UI never appears to have saved a PO
 * that was never sent anywhere.
 */
class MesService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        // Trim a trailing slash so we can concatenate paths cleanly.
        $this->baseUrl = rtrim((string) config('services.mes.url', ''), '/');
        $this->apiKey  = (string) config('services.mes.token', '');
    }

    public function isConfigured(): bool
    {
        return $this->baseUrl !== '';
    }

    /**
     * Raise a Purchase Order in MES.
     *
     * @return array{success:bool, po_number:?string, id:?string, message:string}
     */
    public function createPurchaseOrder(array $payload): array
    {
        if (!$this->isConfigured()) {
            return [
                'success'   => false,
                'po_number' => null,
                'id'        => null,
                'message'   => 'MES integration is not configured yet. The PO was not submitted.',
            ];
        }

        try {
            $response = $this->client()->post($this->baseUrl . '/integrations/cpanel/purchase-orders', $payload);

            if (!$response->successful()) {
                Log::warning('MES createPurchaseOrder non-2xx', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return [
                    'success'   => false,
                    'po_number' => null,
                    'id'        => null,
                    'message'   => $response->json('message') ?? 'MES rejected the Purchase Order.',
                ];
            }

            $body = $response->json();
            return [
                'success'   => true,
                'po_number' => $body['po_number'] ?? ($body['data']['poNumber'] ?? null),
                'id'        => $body['id'] ?? ($body['data']['_id'] ?? null),
                'message'   => $body['message'] ?? 'Purchase Order raised successfully and sent for approval.',
            ];
        } catch (Exception $e) {
            Log::error('MES createPurchaseOrder failed: ' . $e->getMessage());
            return [
                'success'   => false,
                'po_number' => null,
                'id'        => null,
                'message'   => 'Could not reach MES. Please try again later.',
            ];
        }
    }

    /**
     * List Purchase Orders from MES for the current viewer.
     *
     * @return array{rows:array, total:int, error:?string}
     */
    public function listPurchaseOrders(array $filters): array
    {
        if (!$this->isConfigured()) {
            return ['rows' => [], 'total' => 0, 'error' => 'not_configured'];
        }

        try {
            $response = $this->client()->get($this->baseUrl . '/integrations/cpanel/purchase-orders', $filters);

            if (!$response->successful()) {
                Log::warning('MES listPurchaseOrders non-2xx', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return ['rows' => [], 'total' => 0, 'error' => 'mes_error'];
            }

            $body = $response->json();
            return [
                'rows'  => $body['data'] ?? [],
                'total' => (int) ($body['total'] ?? count($body['data'] ?? [])),
                'error' => null,
            ];
        } catch (Exception $e) {
            Log::error('MES listPurchaseOrders failed: ' . $e->getMessage());
            return ['rows' => [], 'total' => 0, 'error' => 'unreachable'];
        }
    }

    /**
     * eSIM makes + profiles for the PO form (fetched from MES).
     *
     * @return array{makes:array, profiles:array, error:?string}
     */
    public function getEsimOptions(): array
    {
        if (!$this->isConfigured()) {
            return ['makes' => [], 'profiles' => [], 'error' => 'not_configured'];
        }

        try {
            $response = $this->client()->get($this->baseUrl . '/integrations/cpanel/esim-options');
            if (!$response->successful()) {
                Log::warning('MES getEsimOptions non-2xx', ['status' => $response->status(), 'body' => $response->body()]);
                return ['makes' => [], 'profiles' => [], 'error' => 'mes_error'];
            }
            $body = $response->json();
            return [
                'makes' => $body['makes'] ?? [],
                'profiles' => $body['profiles'] ?? [],
                'error' => null,
            ];
        } catch (Exception $e) {
            Log::error('MES getEsimOptions failed: ' . $e->getMessage());
            return ['makes' => [], 'profiles' => [], 'error' => 'unreachable'];
        }
    }

    /**
     * Sticker format picklist for the SKU form's Carton/Sticker step
     * (fetched from MES — CPanel stores no sticker design data of its own).
     *
     * @return array{formats:array, error:?string}
     */
    public function getStickerFormats(): array
    {
        if (!$this->isConfigured()) {
            return ['formats' => [], 'error' => 'not_configured'];
        }
        try {
            $response = $this->client()->get($this->baseUrl . '/integrations/cpanel/sticker-formats');
            if (!$response->successful()) {
                Log::warning('MES getStickerFormats non-2xx', ['status' => $response->status(), 'body' => $response->body()]);
                return ['formats' => [], 'error' => 'mes_error'];
            }
            return ['formats' => $response->json('data') ?? [], 'error' => null];
        } catch (Exception $e) {
            Log::error('MES getStickerFormats failed: ' . $e->getMessage());
            return ['formats' => [], 'error' => 'unreachable'];
        }
    }

    /**
     * Accessories MES offers on a PO for a device category (its Product
     * Category's accessory mapping). categoryMapped=false when the device
     * category isn't linked to any MES Product Category.
     *
     * @return array{items:array, categoryMapped:bool, error:?string}
     */
    public function getCategoryAccessories(?int $deviceCategoryId, string $deviceCategoryName = ''): array
    {
        if (!$this->isConfigured()) {
            return ['items' => [], 'categoryMapped' => false, 'error' => 'not_configured'];
        }
        try {
            $response = $this->client()->get($this->baseUrl . '/integrations/cpanel/accessories', [
                'deviceCategoryId' => $deviceCategoryId,
                'deviceCategoryName' => $deviceCategoryName,
            ]);
            if (!$response->successful()) {
                Log::warning('MES getCategoryAccessories non-2xx', ['status' => $response->status(), 'body' => $response->body()]);
                return ['items' => [], 'categoryMapped' => false, 'error' => 'mes_error'];
            }
            return [
                'items' => $response->json('data') ?? [],
                'categoryMapped' => (bool) $response->json('categoryMapped'),
                'error' => null,
            ];
        } catch (Exception $e) {
            Log::error('MES getCategoryAccessories failed: ' . $e->getMessage());
            return ['items' => [], 'categoryMapped' => false, 'error' => 'unreachable'];
        }
    }

    /**
     * Full sticker format design (dimensions + fields) for the live preview.
     *
     * @return array{format:?array, error:?string}
     */
    public function getStickerFormat(string $id): array
    {
        if (!$this->isConfigured()) {
            return ['format' => null, 'error' => 'not_configured'];
        }
        try {
            $response = $this->client()->get($this->baseUrl . '/integrations/cpanel/sticker-formats/' . $id);
            if (!$response->successful()) {
                return ['format' => null, 'error' => $response->status() === 404 ? 'not_found' : 'mes_error'];
            }
            return ['format' => $response->json('data'), 'error' => null];
        } catch (Exception $e) {
            Log::error('MES getStickerFormat failed: ' . $e->getMessage());
            return ['format' => null, 'error' => 'unreachable'];
        }
    }

    /**
     * Submit (or resubmit) a Writer's KYC details to MES for the Accounts
     * team to review — MES is now the source of truth for the decision,
     * CPanel no longer approves/rejects locally.
     *
     * @return array{success:bool, message:string}
     */
    public function submitKyc(array $payload): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'MES is not configured.'];
        }
        try {
            $response = $this->client()->post($this->baseUrl . '/integrations/cpanel/kyc', $payload);
            $body = $response->json();
            if ($response->successful()) {
                return ['success' => true, 'message' => $body['message'] ?? 'KYC submitted for review.'];
            }
            return ['success' => false, 'message' => $body['message'] ?? 'MES rejected the KYC submission.'];
        } catch (Exception $e) {
            Log::error('MES submitKyc failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Could not reach MES. Please try again.'];
        }
    }

    /**
     * MES's KYC status strings mapped onto the legacy local column values
     * still read by PurchaseOrderController/SkuController/admin views.
     */
    public static function mapKycStatus(string $mesStatus): string
    {
        $map = [
            'NotSubmitted' => 'NotSubmitted',
            'Pending' => 'SupportReviewPending',
            'Approved' => 'Approved',
            'Rejected' => 'Rejected',
        ];
        return $map[$mesStatus] ?? 'NotSubmitted';
    }

    /**
     * Fetch the live KYC decision from MES and mirror it onto the given
     * Writer row (so every other place that reads $writer->kyc_status sees
     * a value that's at most as stale as this call). Returns the mapped
     * ('NotSubmitted'/'SupportReviewPending'/'Approved'/'Rejected') status —
     * falling back to the writer's already-cached value if MES is unreachable.
     */
    public function syncKycStatus(\App\Writer $writer): string
    {
        $res = $this->getKycStatus((int) $writer->id);
        if (!empty($res['error'])) {
            return $writer->kyc_status ?? 'NotSubmitted';
        }

        $mapped = self::mapKycStatus($res['status']);
        if ($writer->kyc_status !== $mapped || $writer->kyc_rejection_reason !== $res['remarks']) {
            $writer->update([
                'kyc_status' => $mapped,
                'kyc_rejection_reason' => $res['status'] === 'Rejected' ? $res['remarks'] : null,
            ]);
        }
        return $mapped;
    }

    /**
     * Current KYC decision for one account, from MES.
     *
     * @return array{status:string, remarks:?string, resubmissionAllowed:bool, error:?string}
     */
    public function getKycStatus(int $cpanelUserId): array
    {
        $default = ['status' => 'NotSubmitted', 'remarks' => null, 'resubmissionAllowed' => false];
        if (!$this->isConfigured()) {
            return $default + ['error' => 'not_configured'];
        }
        try {
            $response = $this->client()->get($this->baseUrl . '/integrations/cpanel/kyc/' . $cpanelUserId);
            if (!$response->successful()) {
                Log::warning('MES getKycStatus non-2xx', ['status' => $response->status(), 'body' => $response->body()]);
                return $default + ['error' => 'mes_error'];
            }
            $data = $response->json('data') ?? [];
            return [
                'status' => $data['status'] ?? 'NotSubmitted',
                'remarks' => $data['remarks'] ?? null,
                'resubmissionAllowed' => (bool) ($data['resubmissionAllowed'] ?? false),
                'error' => null,
            ];
        } catch (Exception $e) {
            Log::error('MES getKycStatus failed: ' . $e->getMessage());
            return $default + ['error' => 'unreachable'];
        }
    }

    /**
     * Fetch a single PO (to prefill the edit/resubmit form).
     *
     * @return array{po:?array, error:?string}
     */
    public function getPurchaseOrder(string $id): array
    {
        if (!$this->isConfigured()) {
            return ['po' => null, 'error' => 'not_configured'];
        }
        try {
            $response = $this->client()->get($this->baseUrl . '/integrations/cpanel/purchase-orders/' . $id);
            if (!$response->successful()) {
                return ['po' => null, 'error' => $response->status() === 404 ? 'not_found' : 'mes_error'];
            }
            return ['po' => $response->json('data'), 'error' => null];
        } catch (Exception $e) {
            Log::error('MES getPurchaseOrder failed: ' . $e->getMessage());
            return ['po' => null, 'error' => 'unreachable'];
        }
    }

    /**
     * Resubmit a rejected PO after the customer edits it.
     *
     * @return array{success:bool, po_number:?string, message:string}
     */
    public function resubmitPurchaseOrder(string $id, array $payload): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'po_number' => null, 'message' => 'MES integration is not configured yet.'];
        }
        try {
            $response = $this->client()->put($this->baseUrl . '/integrations/cpanel/purchase-orders/' . $id . '/resubmit', $payload);
            if (!$response->successful()) {
                return ['success' => false, 'po_number' => null, 'message' => $response->json('message') ?? 'MES rejected the resubmission.'];
            }
            $body = $response->json();
            return [
                'success' => true,
                'po_number' => $body['po_number'] ?? null,
                'message' => $body['message'] ?? 'Purchase Order resubmitted for approval.',
            ];
        } catch (Exception $e) {
            Log::error('MES resubmitPurchaseOrder failed: ' . $e->getMessage());
            return ['success' => false, 'po_number' => null, 'message' => 'Could not reach MES. Please try again later.'];
        }
    }

    /**
     * Submit a SKU request to MES (NPD reviews it there).
     *
     * @return array{success:bool, sku_code:?string, id:?string, message:string}
     */
    public function createSkuRequest(array $payload): array
    {
        if (!$this->isConfigured()) {
            return [
                'success'  => false,
                'sku_code' => null,
                'id'       => null,
                'message'  => 'MES integration is not configured yet. The SKU request was not submitted.',
            ];
        }

        try {
            $response = $this->client()->post($this->baseUrl . '/integrations/cpanel/skus', $payload);

            if (!$response->successful()) {
                Log::warning('MES createSkuRequest non-2xx', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return [
                    'success'  => false,
                    'sku_code' => null,
                    'id'       => null,
                    'message'  => $response->json('message') ?? 'MES rejected the SKU request.',
                ];
            }

            $body = $response->json();
            return [
                'success'  => true,
                'sku_code' => $body['sku_code'] ?? ($body['data']['skuCode'] ?? null),
                'id'       => $body['id'] ?? ($body['data']['_id'] ?? null),
                'message'  => $body['message'] ?? 'SKU request submitted and sent for NPD approval.',
            ];
        } catch (Exception $e) {
            Log::error('MES createSkuRequest failed: ' . $e->getMessage());
            return [
                'success'  => false,
                'sku_code' => null,
                'id'       => null,
                'message'  => 'Could not reach MES. Please try again later.',
            ];
        }
    }

    /**
     * List SKU requests from MES for the current viewer.
     *
     * @return array{rows:array, total:int, error:?string}
     */
    public function listSkuRequests(array $filters): array
    {
        if (!$this->isConfigured()) {
            return ['rows' => [], 'total' => 0, 'error' => 'not_configured'];
        }

        try {
            $response = $this->client()->get($this->baseUrl . '/integrations/cpanel/skus', $filters);

            if (!$response->successful()) {
                Log::warning('MES listSkuRequests non-2xx', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return ['rows' => [], 'total' => 0, 'error' => 'mes_error'];
            }

            $body = $response->json();
            return [
                'rows'  => $body['data'] ?? [],
                'total' => (int) ($body['total'] ?? count($body['data'] ?? [])),
                'error' => null,
            ];
        } catch (Exception $e) {
            Log::error('MES listSkuRequests failed: ' . $e->getMessage());
            return ['rows' => [], 'total' => 0, 'error' => 'unreachable'];
        }
    }

    /**
     * Fetch a single SKU request (to prefill the edit/resubmit form).
     *
     * @return array{sku:?array, error:?string}
     */
    public function getSkuRequest(string $id): array
    {
        if (!$this->isConfigured()) {
            return ['sku' => null, 'error' => 'not_configured'];
        }
        try {
            $response = $this->client()->get($this->baseUrl . '/integrations/cpanel/skus/' . $id);
            if (!$response->successful()) {
                return ['sku' => null, 'error' => $response->status() === 404 ? 'not_found' : 'mes_error'];
            }
            return ['sku' => $response->json('data'), 'error' => null];
        } catch (Exception $e) {
            Log::error('MES getSkuRequest failed: ' . $e->getMessage());
            return ['sku' => null, 'error' => 'unreachable'];
        }
    }

    /**
     * Resubmit a rejected SKU request after the customer edits it.
     *
     * @return array{success:bool, sku_code:?string, message:string}
     */
    public function resubmitSkuRequest(string $id, array $payload): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'sku_code' => null, 'message' => 'MES integration is not configured yet.'];
        }
        try {
            $response = $this->client()->put($this->baseUrl . '/integrations/cpanel/skus/' . $id . '/resubmit', $payload);
            if (!$response->successful()) {
                return ['success' => false, 'sku_code' => null, 'message' => $response->json('message') ?? 'MES rejected the resubmission.'];
            }
            $body = $response->json();
            return [
                'success'  => true,
                'sku_code' => $body['sku_code'] ?? null,
                'message'  => $body['message'] ?? 'SKU request resubmitted for approval.',
            ];
        } catch (Exception $e) {
            Log::error('MES resubmitSkuRequest failed: ' . $e->getMessage());
            return ['success' => false, 'sku_code' => null, 'message' => 'Could not reach MES. Please try again later.'];
        }
    }

    /**
     * Edit a SKU request that's still awaiting NPD review (status stays Pending).
     *
     * @return array{success:bool, sku_code:?string, message:string}
     */
    public function updateSkuRequest(string $id, array $payload): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'sku_code' => null, 'message' => 'MES integration is not configured yet.'];
        }
        try {
            $response = $this->client()->put($this->baseUrl . '/integrations/cpanel/skus/' . $id, $payload);
            if (!$response->successful()) {
                return ['success' => false, 'sku_code' => null, 'message' => $response->json('message') ?? 'MES rejected the update.'];
            }
            $body = $response->json();
            return [
                'success'  => true,
                'sku_code' => $body['sku_code'] ?? null,
                'message'  => $body['message'] ?? 'SKU request updated.',
            ];
        } catch (Exception $e) {
            Log::error('MES updateSkuRequest failed: ' . $e->getMessage());
            return ['success' => false, 'sku_code' => null, 'message' => 'Could not reach MES. Please try again later.'];
        }
    }

    /**
     * Withdraw a SKU request that's still awaiting NPD review.
     *
     * @return array{success:bool, message:string}
     */
    public function deleteSkuRequest(string $id): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'MES integration is not configured yet.'];
        }
        try {
            $response = $this->client()->delete($this->baseUrl . '/integrations/cpanel/skus/' . $id);
            if (!$response->successful()) {
                return ['success' => false, 'message' => $response->json('message') ?? 'MES rejected the deletion.'];
            }
            $body = $response->json();
            return ['success' => true, 'message' => $body['message'] ?? 'SKU request deleted.'];
        } catch (Exception $e) {
            Log::error('MES deleteSkuRequest failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Could not reach MES. Please try again later.'];
        }
    }

    protected function client()
    {
        $http = Http::withHeaders([
            'x-api-key'    => $this->apiKey,
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
        ])->timeout(15)->connectTimeout(10);

        // Local Windows/WAMP dev may hit an https MES with a self-signed cert.
        if (config('services.mes.verify_ssl', true) === false) {
            $http = $http->withoutVerifying();
        }

        return $http;
    }
}
