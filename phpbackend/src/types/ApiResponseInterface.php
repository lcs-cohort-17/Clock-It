<?php

namespace Types;

/**
 * INTERFACE: This is a CONTRACT that says "any class implementing me MUST have these methods"
 * 
 * Think of it like a job contract:
 * - You promise to do certain work
 * - If you sign, you MUST do it
 */
interface ApiResponseInterface {
    /**
     * Get the response as an array
     * @return array MUST return an array with 'success', 'data', etc.
     */
    public function toArray(): array;
    
    /**
     * Check if the operation was successful
     * @return bool MUST return true or false
     */
    public function isSuccess(): bool;
    
    /**
     * Get the error message if any
     * @return string|null MUST return string or null
     */
    public function getError(): ?string;
}