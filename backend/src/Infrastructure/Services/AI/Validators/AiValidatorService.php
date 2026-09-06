<?php

namespace App\Infrastructure\Services\AI\Validators;

use Override;

use App\Domain\Shared\Services\AiValidatorServiceInterface;

use Symfony\Contracts\HttpClient\HttpClientInterface;


class AiValidatorService implements AiValidatorServiceInterface
{

    private string $ollamaRootUrl;
    private string $ollamaChatModel;

    public function __construct(
        private HttpClientInterface $httpClient,
        string $ollamaChatModel = "mistral",
        string $ollamaRootUrl = 'http://localhost:11434/api',
    ){
        $this->ollamaChatModel = $ollamaChatModel;
        $this->ollamaRootUrl = $ollamaRootUrl;
    }

    /**
     * @throws \Exception
     */
    #[Override]
    public function isSameSkillConcept(string $name, string $canonicalName): bool
    {
        if(mb_strtolower(trim($name)) === mb_strtolower($canonicalName)){
            return true;
        }

    $prompt = <<<PROMPT
Tu es un expert en normalisation de compétences professionnelles.

Détermine si les deux libellés suivants désignent la même compétence professionnelle.

Libellé utilisateur :
"$name"

Compétence de référence :
"$canonicalName"

Considère comme identiques :
- synonymes ;
- traductions ;
- acronymes ;
- formulations différentes décrivant la même compétence.

Considère comme différents :
- technologies différentes ;
- métiers différents ;
- compétences différentes ;
- une compétence générale vs une spécialisation.

Ne te base pas uniquement sur les mots, mais sur le sens métier.

Réponds EXCLUSIVEMENT avec un JSON valide :

{"is_same": true}

ou

{"is_same": false}
PROMPT;
        try{
            $response = $this->httpClient->request("POST", $this->ollamaRootUrl . "/api/generate", [
                'json' => [
                    "model" => $this->ollamaChatModel,
                    "prompt" => $prompt,
                    "stream" => false,
                    "format" => 'json',
                    "timeout" => 120,
                    "options" => [
                        "temperature" => 0.0
                    ]
                ]
            ]);

            $data = $response->toArray();
            $content = json_decode($data["response"] ?? "{}", true);

            return (bool) ($content['is_same'] ?? false);
        }
        catch(\Exception $exception){
            throw $exception;
        }
    }
}