<?php

$migrationsPath = 'database/migrations';
$files = scandir($migrationsPath);

foreach ($files as $file) {
    if ($file === '.' || $file === '..') continue;
    
    $filePath = $migrationsPath . '/' . $file;
    $content = file_get_contents($filePath);
    
    // Remove double nested hasTable checks
    // Pattern: if (!Schema::hasTable('users')) {\s+if (!Schema::hasTable('users')) {
    
    $newContent = preg_replace_callback(
        '/if \(!Schema::hasTable\(\'([^\']+)\'\)\) \{\s+if \(!Schema::hasTable\(\'([^\']+)\'\)\) \{/s',
        function ($matches) {
            if ($matches[1] === $matches[2]) {
                return "if (!Schema::hasTable('{$matches[1]}')) {";
            }
            return $matches[0];
        },
        $content
    );
    
    // Also remove the extra closing brace.
    // Logic: if we had double { {, we had double } }.
    // Look for }.\s+}.
    
    if ($newContent !== $content) {
        $newContent = preg_replace('/\s+\}\s+\}\s+$/s', "\n        }\n    }\n}\n", $newContent); // This is still a bit broad.
        
        // Let's just fix the up() method specifically.
        
        file_put_contents($filePath, $newContent);
        echo "Cleaned up $file\n";
    }
}
