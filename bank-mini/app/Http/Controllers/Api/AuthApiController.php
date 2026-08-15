<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ApiLoginRequest;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\ChangePinRequest;
use App\Models\CustomerAccount;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthApiController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Mobile login endpoint.
     */
    public function login(ApiLoginRequest $request): JsonResponse
    {
        try {
            $data = $this->authService->loginApi($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil.',
                'data' => [
                    'access_token' => $data['token'],
                    'token_type' => 'Bearer',
                    'first_login' => $data['first_login'],
                    'account' => [
                        'id' => $data['customer_account']->id,
                        'username' => $data['customer_account']->username,
                        'status' => $data['customer_account']->status,
                        'customer' => $data['customer_account']->customer,
                    ],
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 401);
        }
    }

    /**
     * Get authenticated customer profile.
     */
    public function me(Request $request): JsonResponse
    {
        /** @var CustomerAccount $customerAccount */
        $customerAccount = $request->user();
        $customerAccount->load(['customer.bankAccount']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $customerAccount->id,
                'username' => $customerAccount->username,
                'first_login' => (bool) $customerAccount->first_login,
                'status' => $customerAccount->status,
                'customer' => $customerAccount->customer,
            ],
        ], 200);
    }

    /**
     * Mobile logout endpoint.
     */
    public function logout(Request $request): JsonResponse
    {
        /** @var CustomerAccount $customerAccount */
        $customerAccount = $request->user();
        $this->authService->logoutApi($customerAccount);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil keluar dari akun.',
        ], 200);
    }

    /**
     * Change customer password.
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        try {
            /** @var CustomerAccount $customerAccount */
            $customerAccount = $request->user();
            $this->authService->changePassword(
                $customerAccount,
                $request->input('current_password'),
                $request->input('new_password')
            );

            return response()->json([
                'success' => true,
                'message' => 'Password berhasil diperbarui.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Change customer PIN.
     */
    public function changePin(ChangePinRequest $request): JsonResponse
    {
        try {
            /** @var CustomerAccount $customerAccount */
            $customerAccount = $request->user();
            $this->authService->changePin(
                $customerAccount,
                $request->input('current_pin'),
                $request->input('new_pin')
            );

            return response()->json([
                'success' => true,
                'message' => 'PIN berhasil diperbarui.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
