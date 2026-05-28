    public function clearCache($request, $response)
    {
        try {
            $user = $request->getAttribute('user');
            $employeeId = $user['employee_id'] ?? null;

            if (!$employeeId) {
                return $response->withStatus(401)->withJson([
                    'success' => false,
                    'error' => 'User must be logged in to clear cache'
                ]);
            }

            $result = $this->profileDb->clearCache($employeeId);

            if (!$result['success']) {
                return $response->withStatus(400)->withJson($result);
            }

            return $response->withStatus(200)->withJson($result);
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }