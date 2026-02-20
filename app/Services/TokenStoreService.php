<?php

namespace App\Services;

use Exception;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\TokenStoreRequest;
use App\Libraries\QueryExceptionLibrary;

class TokenStoreService
{

    /**
     * @throws Exception
     */
    public function webToken(TokenStoreRequest $request)
    {
        try {
            $user = User::find(auth()->user()->id);
            $newToken = $request->token;

            // Support multiple devices: append token to web_tokens so desktop + mobile both receive
            $tokens = is_array($user->web_tokens) ? $user->web_tokens : (array) json_decode($user->web_tokens ?? '[]', true);
            if (!empty($user->web_token) && !in_array($user->web_token, $tokens)) {
                $tokens[] = $user->web_token; // Migrate existing web_token
            }
            if (!in_array($newToken, $tokens)) {
                $tokens[] = $newToken;
                // Keep max 10 tokens per user to avoid bloat
                if (count($tokens) > 10) {
                    $tokens = array_slice($tokens, -10);
                }
            }
            $user->web_tokens = $tokens;
            $user->web_token = $newToken; // Keep for backward compat
            $user->save();

            return true;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }
    /**
     * @throws Exception
     */
    public function deviceToken(TokenStoreRequest $request)
    {
        try {

            $user = User::find(auth()->user()->id);
            $user->device_token = $request->token;
            $user->save();

            return true;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }
}