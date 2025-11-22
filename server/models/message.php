<?php
require_once("Model.php");

class Message extends Model {
    private int $id;
    private int $receiver_id;
    private int $sender_id;
    private string $content;
    private string $status;

    private ?string $delivered_at;  
    private string $send_at;        
    private ?string $read_at;       

    protected static string $table = "message";

    public function __construct(array $data) {
        $this->id           = $data['id'] ?? 0;
        $this->receiver_id  = $data['receiver_id'] ?? 0;
        $this->sender_id    = $data['sender_id'] ?? 0;
        $this->content      = $data['content'] ?? '';
        $this->status       = $data['status'] ?? 'sent';

        // DATETIME fields
        $this->delivered_at = $data['delivered_at'] ?? null;
        $this->send_at      = $data['send_at'] ?? date('Y-m-d H:i:s');
        $this->read_at      = $data['read_at'] ?? null;
    }

    // --- Getters ---
    public function getId(): int {
        return $this->id;
    }

    public function getReceiverId(): int {
        return $this->receiver_id;
    }

    public function getSenderId(): int {
        return $this->sender_id;
    }

    public function getContent(): string {
        return $this->content;
    }

    public function getStatus(): string {
        return $this->status;
    }

    public function getDeliveredAt(): ?string {
        return $this->delivered_at;
    }

    public function getSendAt(): string {
        return $this->send_at;
    }

    public function getReadAt(): ?string {
        return $this->read_at;
    }

    // --- Setters ---
    public function setContent(string $content): void {
        $this->content = $content;
    }

    public function setStatus(string $status): void {
        $this->status = $status;
    }

    public function setDeliveredAt(?string $datetime): void {
        $this->delivered_at = $datetime;
    }

    public function setSendAt(string $datetime): void {
        $this->send_at = $datetime;
    }

    public function setReadAt(?string $datetime): void {
        $this->read_at = $datetime;
    }

    // --- Convert to Array ---
    public function toArray(): array {
        return [
            "id"           => $this->id,
            "receiver_id"  => $this->receiver_id,
            "sender_id"    => $this->sender_id,
            "content"      => $this->content,
            "status"       => $this->status,
            "delivered_at" => $this->delivered_at,
            "send_at"      => $this->send_at,
            "read_at"      => $this->read_at
        ];
    }
}
?>