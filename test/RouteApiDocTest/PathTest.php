<?php

namespace RouteApiDocTest;

use PHPUnit\Framework\TestCase;
use Psr\Http\Server\MiddlewareInterface;
use RouteApiDoc\OpenApiPath;
use Zend\Expressive\Router\Route;

class PathTest extends TestCase
{

    /** @dataProvider pathDataProvider
     * @param string $routePath
     * @param string $resourceName
     */
    public function testGetSchema(string $routePath, string $resourceName) : void
    {
        $path = new OpenApiPath($routePath);

        self::assertEquals($resourceName, $path->getSchemaName());
    }

    /**
     * @dataProvider pathDataProvider
     * @param string $routePath
     * @param string $resourceName
     * @param array  $parameters
     */
    public function testExtractRoutesWithParameters(string $routePath, string $resourceName, array $parameters) : void
    {
        $path = new OpenApiPath($routePath);

        self::assertEquals($parameters, $path->getParameters());
    }

    /**
     * @dataProvider pathDataProvider
     *
     * @param string $routePath
     * @param string $resourceName
     * @param array  $parameters
     * @param bool   $isCollection
     */
    public function testIsCollection(string $routePath, string $resourceName, array $parameters, bool $isCollection) : void
    {
        $path = new OpenApiPath($routePath);

        self::assertEquals($isCollection, $path->isCollection());
    }

    public function pathDataProvider() : array
    {
        return [
            ['/pets', 'Pets', [], true],
            ['/pets/{petId}', 'Pet', ['petId'], false],
            ['/pets/{petId}/toys', 'Toys', ['petId'], true],
            ['/pets/{petId}/toys/{toyId}', 'Toy', ['petId', 'toyId'], false],
        ];
    }
}