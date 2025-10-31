<?php

namespace App\Messenger;

use App\Entity\Query;
use App\Entity\Rule;
use App\ValueObject\QueryStatus;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\NoReturn;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

#[AsMessageHandler]
class TaskHandler
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected HttpClientInterface $httpClient,
        protected ?LoggerInterface $logger = null,
    ) {
    }

    #[NoReturn]
    public function __invoke(TaskMessage $message): void
    {
        $ctx = [
            'id' => $message->queryId,
        ];

        $query = $this->entityManager
            ->getRepository(Query::class)
            ->find($message->queryId);

        if (\is_null($query)) {
            $this->logger->warning('Query not found. Skip processing.', $ctx);
            return;
        }

        try {
            $this->logger->info('Start processing', $ctx);
            $this->process($query);

            $this->logger->info('Processing was successful', $ctx);
        } catch (\Throwable $e) {
            $query->setError($e->getMessage());
            $query->setStatus(QueryStatus::ERROR);

            $this->logger->info('Exception', $ctx);
        }

        $this->entityManager->flush();
    }

    protected function process(Query $query): void
    {
        $rule = $query->getRule();
        if (\strlen($rule->getCallbackUrl()) === 0) {
            throw new \LogicException('Uri rule must be filled.');
        }
        $query->incAttempts();
        $options = [
            'headers' => $rule->getHeaders(),
        ];

        if ($rule->getContentType() === 'application/json') {
            $ruleQueryString = $rule->getQuery();

            $language = new ExpressionLanguage();
            foreach ($rule->getVariables() as $variableName => $variablePath) {
                $replaceValue = $language->evaluate(
                    $this->transformObjectPathToArray($variablePath),
                    \json_decode($query->getBody(), true, flags: JSON_THROW_ON_ERROR)
                );

                $ruleQueryString = \str_replace(
                    $variableName,
                    $replaceValue,
                    $ruleQueryString
                );
            }

            $json = \json_decode($ruleQueryString, true);
            $options['json'] = $json;
        }

        $options = \array_filter($options);

        $time = \microtime(true);
        $response = $this->httpClient->request(
            $rule->getMethod(),
            $rule->getCallbackUrl(),
            $options,
        );
        $response->getContent();

        $query->setExecuteTime(\microtime(true) - $time);
        $query->setStatus(QueryStatus::DONE);
        $query->setError(null);
    }

    protected function transformObjectPathToArray(string $path): string
    {
        $parts = \preg_split('/\./', $path);
        $result = \array_shift($parts);

        foreach ($parts as $part) {
            if (\preg_match('/\[\d+\]/', $part)) {
                $result .= "['" . \preg_replace('/\[(\d+)\]/', "'][$1", $part) . "']";
            } else {
                $result .= \sprintf("['%s']", $part);
            }
        }

        return $result;
    }
}

