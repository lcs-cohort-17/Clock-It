<?php

namespace Types;

/**
 * INTERFACE for Profile Model - says what ALL Profile models MUST be able to do
 * 
 * This is the most important interface! It defines the CONTRACT that our model must follow.
 * Every function in our TypeScript model becomes a method in this interface.
 */
interface ProfileModelInterface {
    /**
     * Get all profiles
     * @return ApiResponse MUST return an ApiResponse object
     */
    public function adminGettingAllUsersDb(): ApiResponse;
    
    /**
     * Get profile by employee ID
     * @param string $employeeId The employee ID (like S-005 or A-010)
     * @return ApiResponse MUST return an ApiResponse object
     */
    public function getProfileByIdDb(string $employee_id): ApiResponse;
    
    /**
     * Create a new profile
     * @param string $firstName First name
     * @param string $lastName Last name
     * @param string $employeeId Employee ID (must start with S- or A-)
     * @param string $role Either 'staff' or 'admin'
     * @param string $Email Email address
     * @return ApiResponse MUST return ApiResponse with plain text password
     */
    public function adminCreatingUserDb(
        string $first_name,
        string $last_name,
        string $employee_id,
        string $role,
        string $email,
        string $img
    ): ApiResponse;
    
    /**
     * Login - find user by email
     * @param string $email Email address
     * @return ApiResponse MUST return ApiResponse with user data
     */
    public function loginProfileDb(string $email): ApiResponse;
    
    /**
     * Update a profile
     * @param string $employeeId Employee ID to update
     * @param array $updates Array of fields to update (like ['first_name' => 'New Name'])
     * @return ApiResponse MUST return ApiResponse with updated data
     */
    public function adminUpdatingUserDb(string $employee_id, array $updates): ApiResponse;
    
    /**
     * Soft delete - set is_active to false
     * @param string $employeeId Employee ID to delete
     * @return ApiResponse MUST return ApiResponse with success message
     */
    public function adminDeletingUserDb(string $employee_id): ApiResponse;
    
    /**
     * Reset password - generate new 8-char password
     * @param string $employeeId Employee ID
     * @return ApiResponse MUST return ApiResponse with new plain text password
     */
    public function resetPasswordDb(string $employee_id): ApiResponse;
    
    /**
     * Update password (when user changes their own password)
     * @param string $employeeId Employee ID
     * @param string $hashedPassword The already-hashed password
     * @return ApiResponse MUST return ApiResponse with success
     */
    public function updatePasswordDb(string $employee_id, string $hashedPassword): ApiResponse;
}