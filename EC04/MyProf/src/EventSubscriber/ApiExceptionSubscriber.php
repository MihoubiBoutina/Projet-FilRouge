<?php

namespace App\EventSubscriber;

use App\Exception\AtelierCompletException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

final class ApiExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        if (!str_starts_with($request->getPathInfo(), '/api/')) {
            return;
        }

        $exception = $event->getThrowable();
        $status = match (true) {
            $exception instanceof HttpExceptionInterface => $exception->getStatusCode(),
            $exception instanceof AtelierCompletException => 409,
            default => 500,
        };

        $this->logger->error('API request failed', [
            'path' => $request->getPathInfo(),
            'method' => $request->getMethod(),
            'exception' => $exception,
        ]);

        $event->setResponse(new JsonResponse([
            'error' => $status >= 500 ? 'Internal server error' : $exception->getMessage(),
        ], $status));
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => 'onKernelException'];
    }
}