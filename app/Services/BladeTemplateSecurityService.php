<?php

namespace App\Services;

use App\Exceptions\BladeSecurityException;
use App\Exceptions\BladeSyntaxException;
use Illuminate\Support\Facades\Blade;

class BladeTemplateSecurityService
{
    protected array $blockedDirectives = [
        'php', 'endphp',           // Raw PHP execution
        'include', 'includeIf',    // File inclusion
        'extends', 'section',      // Layout inheritance
        'component', 'slot',       // Component system
    ];

    protected array $blockedPatterns = [
        '/<\?php/i',                        // PHP tags
        '/\bsystem\s*\(/i',                // System calls
        '/\bexec\s*\(/i',                  // Exec
        '/\beval\s*\(/i',                  // Eval
        '/\bshell_exec\s*\(/i',            // Shell exec
        '/\bpassthru\s*\(/i',              // Passthru
        '/\bfile_get_contents\s*\(/i',     // File read
        '/\bfile_put_contents\s*\(/i',     // File write
        '/\bfopen\s*\(/i',                 // File open
        '/\bunlink\s*\(/i',                // File delete
        '/\brmdir\s*\(/i',                 // Directory delete
    ];

    public function validate(string $content): void
    {
        // Check for blocked directives
        foreach ($this->blockedDirectives as $directive) {
            if (preg_match("/@{$directive}\b/i", $content)) {
                throw new BladeSecurityException(
                    "Blocked directive @{$directive} found in template"
                );
            }
        }

        // Check for blocked patterns
        foreach ($this->blockedPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                throw new BladeSecurityException(
                    "Blocked pattern detected in template: {$pattern}"
                );
            }
        }

        // Validate Blade syntax
        try {
            Blade::compileString($content);
        } catch (\Throwable $e) {
            throw new BladeSyntaxException(
                'Invalid Blade syntax: '.$e->getMessage(),
                previous: $e
            );
        }
    }

    public function validateAndGetErrors(string $content): array
    {
        $errors = [];

        foreach ($this->blockedDirectives as $directive) {
            if (preg_match_all("/@{$directive}\b/i", $content, $matches, PREG_OFFSET_CAPTURE)) {
                foreach ($matches[0] as $match) {
                    $errors[] = [
                        'message' => "Blocked directive @{$directive}",
                        'position' => $match[1],
                    ];
                }
            }
        }

        return $errors;
    }
}
