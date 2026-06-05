<?php

namespace Types;

/**
 * INTERFACE for Profile - like a blueprint saying what a Profile MUST have
 */
interface ProfileInterface {
    // Getters - methods that MUST exist to GET values
    public function getUserId(): ?string;
    public function getFirstName(): string;
    public function getLastName(): string;
    public function getEmployeeId(): string;
    public function getRole(): string;
    public function isActive(): bool;
    public function getEmail(): string;
    public function getPassword(): string;
    public function getImg(): string;
    
    // Setters - methods that MUST exist to SET values
    public function setFirstName(string $first_name): void;
    public function setLastName(string $last_name): void;
    public function setEmployeeId(string $employee_id): void;
    public function setRole(string $role): void;
    public function setEmail(string $email): void;
    public function setImg(string $img): void;
    
    // Convert to array - MUST have this
    public function toArray(): array;
    
    // Convert from array - MUST have this (static method)
    public static function fromArray(array $data): self;
}
