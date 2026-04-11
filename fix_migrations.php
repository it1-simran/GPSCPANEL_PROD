<?php

$migrationsPath = 'database/migrations';
$files = scandir($migrationsPath);

foreach ($files as $file) {
    if ($file === '.' || $file === '..') continue;
    
    $filePath = $migrationsPath . '/' . $file;
    $content = file_get_contents($filePath);
    
    // Pattern to find Schema::create('tableName', function (...) {
    // and replace with if (!Schema::hasTable('tableName')) { Schema::create(...) }
    
    $newContent = preg_replace_callback(
        '/Schema::create\(\s*\'([^\']+)\'\s*,\s*function/s',
        function ($matches) {
            $tableName = $matches[1];
            return "if (!Schema::hasTable('$tableName')) {\n            Schema::create('$tableName', function";
        },
        $content
    );
    
    // Also need to close the brace. 
    // Usually Schema::create ends with });
    // We want to replace }); with });\n        }
    
    if ($newContent !== $content) {
        $newContent = preg_replace('/\s*\}\s*\);\s*\}\s*$/s', "\n        });\n    }\n}\n", $newContent); // This is risky.
        
        // Safer way: replace });\n    }\n} with });\n        }\n    }\n}
        // Actually, most migrations end with }); then a newline, then a brace.
        
        $newContent = preg_replace('/\}\);(\s+)\}/s', "});\n        }$1}", $newContent);
        
        file_put_contents($filePath, $newContent);
        echo "Updated $file\n";
    }
}
