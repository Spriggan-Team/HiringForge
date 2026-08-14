<?php


namespace App\Infrastructure\Services\AI\Resume;

use App\Domain\Candidate\Application\ResumeAiParserInterface;
use App\Domain\Shared\Document\ExtractedDocument;
use App\Domain\Candidate\Application\StructuredResume;

use Override;
use RuntimeException;
use Symfony\Contracts\HttpClient\HttpClientInterface;


readonly final class ResumeAiParser implements ResumeAiParserInterface
{
    public function __construct(
        private string $ollamaChatModel,
        private string $ollamaRootUrl,
        private HttpClientInterface $httpClient,
        private ResumeResponseSchema $resumeResponseSchema,
        private ResumeResponseValidator $responseValidator,
    ){}

    
    #[Override]
    public function parse(ExtractedDocument $document): StructuredResume
    {
        $response = $this->httpClient->request(
            'POST',
            rtrim($this->ollamaRootUrl, '/') . '/api/chat',
            [
                'json' => [
                    'model' => $this->ollamaChatModel,
                    'stream' => false,
                    'format' => $this->resumeResponseSchema->schema(),
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $this->systemPrompt()
                        ],
                        [
                            'role' => 'user',
                            'content' => $document->text()
                        ]
                    ]
                ]
            ]
        );

        $data = $response->toArray();
        $content = $data['message']['content'] ?? null;

        if(!is_string($content) || $content = ''){
            throw new RuntimeException(
                'Ollama returned an empty response.'
            );
        }

        $parsed = json_decode(
            $content, true, 512, JSON_THROW_ON_ERROR
        );

        $this->responseValidator->validate($data);

        return StructuredResume::fromArray($parsed);
    }

    
    
    
    private function systemPrompt(): string
    {
        return <<<'PROMPT'
You are an expert resume parser.

Your task is to extract structured information from a resume.

Rules:
- Do not invent information.
- If information is missing, return null or an empty array.
- Preserve the information from the resume faithfully.
- Do not add explanations.
- Return only valid JSON.
PROMPT;
    }

   

}