<?php
namespace App\Controllers;

use App\Models\LeaveRequest;

class LeaveController {
    public function index() {
    $user = \App\Middleware\AuthMiddleware::handle(); // Dies if no valid JWT
    header('Content-Type: application/json');
        try {
            $leaves = LeaveRequest::all();
            echo json_encode(['success' => true, 'data' => $leaves, 'count' => count($leaves)]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function store() {
        $user = \App\Middleware\AuthMiddleware::handle();
        header('Content-Type: application/json');
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $id = LeaveRequest::create($input);
            http_response_code(201);
            echo json_encode(['success' => true, 'id' => $id, 'message' => 'Leave request created']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function update($id) {
        $user = \App\Middleware\AuthMiddleware::handle();
        header('Content-Type: application/json');
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            LeaveRequest::update($id, $input);
            echo json_encode(['success' => true, 'message' => 'Leave request updated']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function destroy($id) {
        $user = \App\Middleware\AuthMiddleware::handle();
        header('Content-Type: application/json');
        try {
            LeaveRequest::delete($id);
            echo json_encode(['success' => true, 'message' => 'Leave request deleted']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}