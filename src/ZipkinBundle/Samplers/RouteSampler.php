<?php

namespace ZipkinBundle\Samplers;

use InvalidArgumentException;
use Symfony\Component\HttpFoundation\RequestStack;
use Zipkin\Sampler;

final class RouteSampler implements Sampler
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var array
     */
    private $includedRoutes;

    /**
     * @var array
     */
    private $excludedRoutes;

    public function __construct(RequestStack $requestStack, array $includedRoutes = [], array $excludedRoutes = [])
    {
        $this->requestStack = $requestStack;
        $this->includedRoutes = $includedRoutes;
        $this->excludedRoutes = $excludedRoutes;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function isSampled(string $traceId): bool
    {
        $masterRequest = $this->requestStack->getMasterRequest();
        if ($masterRequest === null) {
            return false;
        }

        $route = $masterRequest->get('_route');

        if ([] !== $this->includedRoutes) {
            if (in_array($route, $this->includedRoutes)) {
                return true;
            }

            return false;
        }

        if ([] !== $this->excludedRoutes) {
            if (in_array($route, $this->excludedRoutes)) {
                return false;
            }

            return true;
        }

        throw new InvalidArgumentException('Either includedRoutes or excludedRoutes should be not empty.');
    }
}
