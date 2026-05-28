    public function testClearCacheReturns200ForLoggedInUser(): void
    {
        $mockResponse = [
            'success' => true,
            'message' => 'Cache cleared successfully for S-005'
        ];

        $this->profileDbMock->expects($this->once())
            ->method('clearCache')
            ->with('S-005')
            ->willReturn($mockResponse);

        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->expects($this->once())
            ->method('getAttribute')
            ->with('user')
            ->willReturn(['employee_id' => 'S-005']);

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->expects($this->once())
            ->method('withStatus')
            ->with(200)
            ->willReturnSelf();

        $responseMock->expects($this->once())
            ->method('withJson')
            ->with($mockResponse)
            ->willReturn($responseMock);

        $result = $this->controller->clearCache($requestMock, $responseMock);

        $this->assertNotNull($result);
    }

    public function testClearCacheReturns401WhenUserNotLoggedIn(): void
    {
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->expects($this->once())
            ->method('getAttribute')
            ->with('user')
            ->willReturn(null);

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->expects($this->once())
            ->method('withStatus')
            ->with(401)
            ->willReturnSelf();

        $responseMock->expects($this->once())
            ->method('withJson')
            ->with([
                'success' => false,
                'error' => 'User must be logged in to clear cache'
            ])
            ->willReturn($responseMock);

        $result = $this->controller->clearCache($requestMock, $responseMock);

        $this->assertNotNull($result);
    }