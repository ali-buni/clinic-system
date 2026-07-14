<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ai\PatientChatbotRequest;
use App\Http\Resources\Ai\ChatMessageResource;
use App\Services\Ai\PatientChatbotService;
use App\Services\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Throwable;

class PatientChatbotController extends Controller
{
    public function __construct(protected PatientChatbotService $service) {}

    public function chat(PatientChatbotRequest $request)
    {
        try {
            $validated = $request->validated();

            $result = $this->service->chat(
                (int) $validated['patient_id'],
                $validated['message'],
                $validated['session_id'] ?? null,
            );

            return ApiResponse::success($result, 'Response generated');
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Patient not found', 404);
        } catch (Throwable $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function history()
    {
        $sessions = $this->service->history();

        return ApiResponse::success($sessions, 'Chat sessions retrieved');
    }

    public function messages(Request $request)
    {
        $request->validate(['session_id' => 'required|string']);

        $messages = $this->service->messages($request->session_id);

        if ($messages->isEmpty()) {
            return ApiResponse::error('No messages found for this session', 404);
        }

        return ApiResponse::success(
            ChatMessageResource::collection($messages),
            'Chat history retrieved'
        );
    }

    public function clearHistory(Request $request)
    {
        $request->validate(['session_id' => 'nullable|string']);

        $deleted = $this->service->clearHistory($request->session_id);

        return ApiResponse::success(
            ['deleted' => $deleted],
            $deleted ? 'Chat history cleared' : 'No messages found'
        );
    }
}
