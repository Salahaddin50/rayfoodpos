<?php

namespace App\Http\Controllers\Admin;

use App\Services\TokenService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TokenController extends AdminController
{
    private TokenService $tokenService;

    public function __construct(TokenService $tokenService)
    {
        parent::__construct();
        $this->tokenService = $tokenService;
    }

    /**
     * Generate next token for current branch
     */
    public function generate(Request $request): JsonResponse
    {
        try {
            $branchId = $request->input('branch_id');
            
            if (!$branchId) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Branch ID is required'
                ], 400);
            }

            $token = $this->tokenService->generateToken($branchId);
            
            return response()->json([
                'status' => true,
                'data'   => [
                    'token'   => $token,
                    'counter' => $this->tokenService->getCurrentCounter($branchId)
                ]
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'status'  => false,
                'message' => $exception->getMessage()
            ], 422);
        }
    }

    /**
     * Reset token counter for branch (sets to 0 for today)
     */
    public function reset(Request $request): JsonResponse
    {
        try {
            $branchId = $request->input('branch_id');
            
            if (!$branchId) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Branch ID is required'
                ], 400);
            }

            $result = $this->tokenService->resetBranchToken($branchId);
            
            return response()->json([
                'status' => true,
                'data'   => [
                    'message' => 'Token counter reset successfully',
                    'counter' => 0
                ]
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'status'  => false,
                'message' => $exception->getMessage()
            ], 422);
        }
    }

    /**
     * Get current counter for branch
     */
    public function currentCounter(Request $request): JsonResponse
    {
        try {
            $branchId = $request->input('branch_id');
            
            if (!$branchId) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Branch ID is required'
                ], 400);
            }

            $counter = $this->tokenService->getCurrentCounter($branchId);
            
            return response()->json([
                'status' => true,
                'data'   => [
                    'counter' => $counter
                ]
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'status'  => false,
                'message' => $exception->getMessage()
            ], 422);
        }
    }
}
