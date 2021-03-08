<?php namespace App\Payment;

class FormData
{
    const METHOD_POST = 'POST';
    const METHOD_GET = 'GET';

    private $url;
    private $method;
    private $fields = [];
    private $customFormName;

    public function __construct(string $url, string $method, array $fields, string $customFormName = null)
    {
        $this->url = $url;
        $this->method = $method;
        $this->fields = $fields;
        $this->customFormName = $customFormName;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function getCustomFormName(): ?string
    {
        return $this->customFormName;
    }

    public function toArray()
    {
        return [
            'url' => $this->url,
            'method' => $this->method,
            'fields' => $this->fields,
        ];
    }
}
