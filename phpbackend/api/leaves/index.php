<?php

// Apache-friendly entry point for requests to /api/leaves.
// This forwards the request into the real front controller in src/public.
require_once __DIR__ . '/../../src/public/LeaveIndex.php';
