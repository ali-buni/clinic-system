<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use App\Services\ApiResponse;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(private NotificationService $notification_service) {}

    public function index(Request $request): JsonResponse
    {
        $result = $this->notification_service->listNotifications($request->user(), $request);
        $pagination = $result['pagination'];

        return response()->json([
            'success' => true,
            'message' => 'Notifications retrieved successfully.',
            'data' => $result['items'],
            'unread_count' => $result['unread_count'],
            'pagination' => [
                'total' => $pagination->total(),
                'count' => $pagination->count(),
                'per_page' => $pagination->perPage(),
                'current_page' => $pagination->currentPage(),
                'last_page' => $pagination->lastPage(),
                'next_page_url' => $pagination->nextPageUrl(),
                'prev_page_url' => $pagination->previousPageUrl(),
            ],
        ]);
    }

    public function markAsRead(Request $request, string $id): JsonResponse
    {
        if (! $this->notification_service->markAsRead($request->user(), $id)) {
            return ApiResponse::error('Notification not found.', 404);
        }

        return ApiResponse::success(null, 'Notification marked as read.');
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $count = $this->notification_service->markAllAsRead($request->user());

        return ApiResponse::success(['marked_read' => $count], 'All notifications marked as read.');
    }
}
