<?php
require_once("Model.php");

class User extends Model {
    private ?int $id;
    private string $name;
    private string $password;
    private string $email;

    protected static string $table = "users";

    public function __construct(array $data) {
        $this->id       = $data['id'] ?? null;
        $this->name     = $data['name'] ?? null;
        $this->password = $data['password'] ?? null;
        $this->email=$data['email']??null;
    }

    // Getters
    public function getId(): int {
        return $this->id;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getPassword(): string {
        return $this->password;
    }

    public function getEmail():string{
        return $this->email;
    }

    // Setters
    public function setName(string $name): void {
        $this->name = $name;
    }

    public function setPassword(string $password): void {
        $this->password = $password;
    }
    public function setEmail(string $email):void{
        $this->email=$email;
    }

    // Convert object to array
    public function toArray(): array {
        return [
            "id"       => $this->id,
            "name"     => $this->name,
            "password" => $this->password,
            "email"    =>$this->email,
        ];
    }
}

