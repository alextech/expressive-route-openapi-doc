<?php

namespace RouteOpenApiDoc;

use RouteOpenApiDoc\RouterStrategy\RouterStrategyInterface;
use Zend\Expressive\Router\RouteCollector;

class OpenApiWriter
{
    /**
     * @var SpecBuilder
     */
    private $specBuilder;

    private $directory;

    private $docFileName = 'api_doc.json';

    public function __construct(RouterStrategyInterface $routerStrategy)
    {
        $this->specBuilder = new SpecBuilder($routerStrategy);
    }


    public function addApplication(\Zend\Expressive\Application $app, $basePath = '') : void
    {
        $this->specBuilder->addApplication($app, $basePath);
    }

    public function addRouteCollector(RouteCollector $routeCollector, $basePath = '') : void
    {
        $this->specBuilder->addRouteCollector($routeCollector, $basePath);
    }

    /**
     * @param bool $singleFile
     * @throws \Exception
     */
    public function writeSpec(
        bool $singleFile = false
    ) : void
    {
        if (! isset($this->directory) || $this->directory === null) {
            throw new \Exception('Cannot create OpenApi spec: output directory is not set.');
        }

        $spec = $this->specBuilder->generateSpec();

        if ($singleFile) {
            $this->writeDoc($this->directory . $this->docFileName, $spec);
        } else {
            $this->writeDocAndSchemas($this->directory, $spec);
        }
    }

    public function setOutputDirectory(string $directory) : void
    {
        if (substr_compare($directory, '/', strlen($directory) - 1, 1) !== 0) {
            $directory .= '/';
        }

        $this->directory = $directory;
    }

    private function writeDoc(string $file, array $spec) : void
    {

        if (file_exists($file)) {
            $existingSpec = json_decode(file_get_contents($file), true);
            $existingSpec['paths']
                = array_merge($spec['paths'], $existingSpec['paths']);
            $existingSpec['components']['schemas']
                = array_merge($spec['components']['schemas'], $existingSpec['components']['schemas']);

            $spec = $existingSpec;
        }

        $this->writeSpecToFile($file, $spec);
    }

    private function writeDocAndSchemas(string $directory, array $spec) : void
    {
        $schemas = $spec['components']['schemas'];

        foreach ($schemas as $schemaName => $schema) {
            $fileName = strtolower($schemaName) . '.json';

            $spec['components']['schemas'][$schemaName] = [
                '$ref' => $fileName . '#/' . $schemaName,
            ];

            if (file_exists($directory . '/' . $fileName)) {
                continue;
            }

            $this->writeSpecToFile($directory . '/' . $fileName, [$schemaName => $schema]);
        }

        $this->writeDoc($directory . $this->docFileName, $spec);
    }

    private function writeSpecToFile(string $file, array $spec) : void
    {
        $result = file_put_contents($file, json_encode($spec, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));


        if ($result === false) {
            throw new \RuntimeException('Problem writing route spec to file "'.$file.'" ');
        }
    }
}
