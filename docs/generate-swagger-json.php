<?php

/**
 * Convert Swagger XML to JSON
 * 
 * This script converts the swagger.xml file to swagger.json format
 * which can be used with Swagger UI for API documentation.
 */

// Load the XML file
$xmlFile = __DIR__ . '/swagger.xml';
$xmlContent = file_get_contents($xmlFile);

// Create a SimpleXMLElement object
$xml = new SimpleXMLElement($xmlContent);

// Initialize the JSON structure
$swagger = [
    'swagger' => '2.0',
    'info' => [
        'title' => (string)$xml->info->title,
        'description' => (string)$xml->info->description,
        'version' => (string)$xml->info->version,
        'contact' => [
            'email' => (string)$xml->info->contact->email
        ]
    ],
    'host' => (string)$xml->host,
    'basePath' => (string)$xml->basePath,
    'schemes' => [],
    'consumes' => [],
    'produces' => [],
    'securityDefinitions' => [],
    'paths' => [],
    'definitions' => []
];

// Add schemes
foreach ($xml->schemes->scheme as $scheme) {
    $swagger['schemes'][] = (string)$scheme;
}

// Add consumes
foreach ($xml->consumes->consume as $consume) {
    $swagger['consumes'][] = (string)$consume;
}

// Add produces
foreach ($xml->produces->produce as $produce) {
    $swagger['produces'][] = (string)$produce;
}

// Add security definitions
foreach ($xml->securityDefinitions->securityDefinition as $secDef) {
    $id = (string)$secDef['id'];
    $swagger['securityDefinitions'][$id] = [
        'type' => (string)$secDef['type'],
        'name' => (string)$secDef->name,
        'in' => (string)$secDef->in,
        'description' => (string)$secDef->description
    ];
}

// Add paths
foreach ($xml->paths->path as $path) {
    $url = (string)$path['url'];
    $swagger['paths'][$url] = [];
    
    foreach ($path->operation as $operation) {
        $method = (string)$operation['method'];
        $swagger['paths'][$url][$method] = [
            'summary' => (string)$operation->summary,
            'description' => (string)$operation->description,
            'responses' => []
        ];
        
        // Add security if present
        if (isset($operation->security)) {
            $swagger['paths'][$url][$method]['security'] = [];
            foreach ($operation->security->securityRequirement as $secReq) {
                $swagger['paths'][$url][$method]['security'][] = [
                    (string)$secReq['name'] => []
                ];
            }
        }
        
        // Add parameters if present
        if (isset($operation->parameters)) {
            $swagger['paths'][$url][$method]['parameters'] = [];
            foreach ($operation->parameters->parameter as $param) {
                $parameter = [
                    'name' => (string)$param['name'],
                    'in' => (string)$param['in'],
                    'description' => (string)$param->description
                ];
                
                // Add type for non-body parameters
                if ((string)$param['in'] !== 'body') {
                    $parameter['type'] = (string)$param['type'];
                    
                    if (isset($param['required'])) {
                        $parameter['required'] = (string)$param['required'] === 'true';
                    }
                    
                    if (isset($param['default'])) {
                        $parameter['default'] = (string)$param['default'];
                    }
                    
                    if (isset($param['format'])) {
                        $parameter['format'] = (string)$param['format'];
                    }
                } else {
                    $parameter['required'] = (string)$param['required'] === 'true';
                    if (isset($param->schema)) {
                        $parameter['schema'] = [
                            '$ref' => (string)$param->schema['ref']
                        ];
                    }
                }
                
                $swagger['paths'][$url][$method]['parameters'][] = $parameter;
            }
        }
        
        // Add responses
        foreach ($operation->responses->response as $response) {
            $code = (string)$response['code'];
            $swagger['paths'][$url][$method]['responses'][$code] = [
                'description' => (string)$response->description
            ];
            
            if (isset($response->schema)) {
                $swagger['paths'][$url][$method]['responses'][$code]['schema'] = [
                    '$ref' => (string)$response->schema['ref']
                ];
            }
        }
    }
}

// Add definitions
foreach ($xml->definitions->definition as $definition) {
    $id = (string)$definition['id'];
    $swagger['definitions'][$id] = [
        'type' => 'object',
        'properties' => []
    ];
    
    foreach ($definition->properties->property as $property) {
        $name = (string)$property['name'];
        $swagger['definitions'][$id]['properties'][$name] = [
            'type' => (string)$property['type'],
            'description' => (string)$property->description
        ];
        
        // Add format if present
        if (isset($property['format'])) {
            $swagger['definitions'][$id]['properties'][$name]['format'] = (string)$property['format'];
        }
        
        // Add reference if present
        if (isset($property['ref'])) {
            $swagger['definitions'][$id]['properties'][$name]['$ref'] = (string)$property['ref'];
        }
        
        // Add items for arrays
        if ((string)$property['type'] === 'array' && isset($property->items)) {
            $swagger['definitions'][$id]['properties'][$name]['items'] = [
                '$ref' => (string)$property->items['ref']
            ];
        }
        
        // Add nested properties
        if (isset($property->properties)) {
            $swagger['definitions'][$id]['properties'][$name]['properties'] = [];
            foreach ($property->properties->property as $nestedProp) {
                $nestedName = (string)$nestedProp['name'];
                $swagger['definitions'][$id]['properties'][$name]['properties'][$nestedName] = [
                    'type' => (string)$nestedProp['type'],
                    'description' => (string)$nestedProp->description
                ];
                
                if (isset($nestedProp['ref'])) {
                    $swagger['definitions'][$id]['properties'][$name]['properties'][$nestedName]['$ref'] = (string)$nestedProp['ref'];
                }
            }
        }
        
        // Add required flag
        if (isset($property['required']) && (string)$property['required'] === 'true') {
            if (!isset($swagger['definitions'][$id]['required'])) {
                $swagger['definitions'][$id]['required'] = [];
            }
            $swagger['definitions'][$id]['required'][] = $name;
        }
    }
}

// Convert to JSON
$jsonOutput = json_encode($swagger, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

// Save to file
file_put_contents(__DIR__ . '/swagger.json', $jsonOutput);

echo "Swagger JSON file generated successfully!\n";
