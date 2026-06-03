<?php

namespace Types;

/**
 * This class PROMISES to follow the ProfileInterface rules
 */
class Profile implements ProfileInterface {
    private ?string $user_id;
    private string $first_name;
    private string $last_name;
    private string $employee_id;
    private string $role;
    private bool $is_active;
    private string $email;
    private string $password;
    private string $img;
    
    public function __construct(array $data = []) {
        $this->user_id = $data['user_id'] ?? null;
        $this->first_name = $data['first_name'] ?? '';
        $this->last_name = $data['last_name'] ?? '';
        $this->employee_id = $data['employee_id'] ?? '';
        $this->role = $data['role'] ?? 'staff';
        $this->is_active = $data['is_active'] ?? true;
        $this->email = $data['email'] ?? '';
        $this->password = $data['password'] ?? '';
        $this->img = $data['img'] ?? '';
    }
    
    // GETTERS - I PROMISE to have these
    public function getUserId(): ?string {
        return $this->user_id;
    }
    
    public function getFirstName(): string {
        return $this->first_name;
    }
    
    public function getLastName(): string {
        return $this->last_name;
    }
    
    public function getEmployeeId(): string {
        return $this->employee_id;
    }
    
    public function getRole(): string {
        return $this->role;
    }
    
    public function isActive(): bool {
        return $this->is_active;
    }
    
    public function getEmail(): string {
        return $this->email;
    }
    
    public function getPassword(): string {
        return $this->password;
    }
    
    public function getImg(): string {
    return $this->img;
    }
    
    // SETTERS - I PROMISE to have these
    public function setFirstName(string $first_name): void {
        $this->first_name = $first_name;
    }
    
    public function setLastName(string $last_name): void {
        $this->last_name = $last_name;
    }

    public function setEmployeeId(string $employee_id): void {
        $this->employee_id = $employee_id;
    }
    
    public function setRole(string $role): void {
        $this->role = $role;
    }
    
    public function setEmail(string $email): void {
        $this->email = $email;
    }

    public function setImg(string $img): void {
        $this->img = $img;
    }

    // I PROMISE to have this method
    public function toArray(): array {
        return [
            'user_id' => $this->user_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'employee_id' => $this->employee_id,
            'role' => $this->role,
            'is_active' => $this->is_active,
            'email' => $this->email,
            'img' => $this->img,
            // password intentionally omitted for security
        ];
    }
    
    // I PROMISE to have this method (static means called on the class, not object)
    public static function fromArray(array $data): self {
        return new self($data);
    }
}