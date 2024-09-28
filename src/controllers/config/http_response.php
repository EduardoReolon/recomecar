<?php

class User_alert {
    public string $msg;
    public string $type = 'warning';
    public int $time = 30000;

    public function __construct(string $msg, string $type = Helper::ALERT_WARNING, int $time = 30000) {
        $this->msg = $msg;
        $this->type = $type;
        $this->time = $time;
    }
}

class Http_response {
    /**
     * 200 OK: Successful request (with content returned).
     * 201 Created: Indicates successful creation of a resource (Variable content return).
     * 202 Accepted: Indicates acceptance for future processing (No content returned).
     * 203 Non-Authoritative Information: Intermediate response (with content returned).
     * 204 No Content: Successful request (no content returned).
     */
    private int $status = 200;
    private string $content_type = 'application/json';
    private array $headers = [];
    private bool $headers_sent = false;
    private string $location_user;
    private string $location;
    private mixed $body = '';
    /** @var User_alert[] */
    private array $alerts = [];

    public function status(int $status) {
        $this->status = $status;
        return $this;
    }
    public function setContentType(string $content_type) {
        $this->content_type = $content_type;
        return $this;
    }
    public function setHeader(string $header) {
        $this->headers[] = $header;
    }
    public function getStatus() {
        return $this->status;
    }

    public function redirectUser(string $location) {
        $this->location_user = $location;
    }

    public function sendAlert(string $msg, string $type = Helper::ALERT_WARNING, int $time = 30000) {
        $this->alerts[] = new User_alert($msg, $type, $time);
        return $this;
    }

    public function send(mixed $body) {
        $this->body = $body;
        return $this;
    }

    public function sendHeaders() {
        if ($this->headers_sent) return;

        if (isset($this->location)) return header("Location: " . $this->location);
        
        header("Content-Type: {$this->content_type}");
        foreach ($this->headers as $header) {
            header($header);
        }

        $this->headers_sent = true;
    }

    public function __destruct() {
        http_response_code($this->status);

        $this->sendHeaders();

        if ($this->content_type === 'application/json') {
            $response = [
                'alerts' => $this->alerts,
                'data' => $this->body,
            ];
            
            if (isset($this->location_user)) {
                $response['redirect'] = true;
                $response['location'] = $this->location_user;
            }
    
            echo json_encode($response);
        }
    }
}