<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class TestTranslations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translations:test {--fix : Automatically add missing translations with placeholder text}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test all translation keys and check for missing translations';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Testing translations...');
        $this->newLine();

        $locales = ['en', 'ar'];
        $translationFiles = ['navigation', 'system', 'api', 'permissions', 'validation'];

        $allIssues = [];
        $totalKeys = 0;
        $missingKeys = 0;

        foreach ($translationFiles as $file) {
            $this->info("Checking: {$file}.php");
            
            $enPath = lang_path("en/{$file}.php");
            $arPath = lang_path("ar/{$file}.php");

            if (!File::exists($enPath)) {
                $this->error("  ❌ English file not found: {$file}.php");
                continue;
            }

            if (!File::exists($arPath)) {
                $this->error("  ❌ Arabic file not found: {$file}.php");
                continue;
            }

            $enKeys = $this->getAllKeys(require $enPath);
            $arKeys = $this->getAllKeys(require $arPath);

            $totalKeys += count($enKeys);

            // Check for keys in English but not in Arabic
            $missingInAr = array_diff(array_keys($enKeys), array_keys($arKeys));
            
            // Check for keys in Arabic but not in English
            $missingInEn = array_diff(array_keys($arKeys), array_keys($enKeys));

            if (!empty($missingInAr)) {
                $this->warn("  ⚠️  Missing in Arabic ({$file}.php):");
                foreach ($missingInAr as $key) {
                    $fullKey = "{$file}.{$key}";
                    $allIssues[] = [
                        'file' => $file,
                        'key' => $key,
                        'locale' => 'ar',
                        'value' => $enKeys[$key] ?? null,
                    ];
                    $missingKeys++;
                    $this->line("     - {$fullKey}");
                }
            }

            if (!empty($missingInEn)) {
                $this->warn("  ⚠️  Missing in English ({$file}.php):");
                foreach ($missingInEn as $key) {
                    $fullKey = "{$file}.{$key}";
                    $allIssues[] = [
                        'file' => $file,
                        'key' => $key,
                        'locale' => 'en',
                        'value' => $arKeys[$key] ?? null,
                    ];
                    $missingKeys++;
                    $this->line("     - {$fullKey}");
                }
            }

            if (empty($missingInAr) && empty($missingInEn)) {
                $this->info("  ✅ All keys translated in {$file}.php");
            }

            $this->newLine();
        }

        // Summary
        $this->info('=' . str_repeat('=', 60));
        $this->info('Summary:');
        $this->info("  Total keys: {$totalKeys}");
        $this->info("  Missing translations: {$missingKeys}");
        $this->info("  Translated: " . ($totalKeys - $missingKeys));
        $this->info("  Completion: " . round((($totalKeys - $missingKeys) / max($totalKeys, 1)) * 100, 2) . "%");
        $this->info('=' . str_repeat('=', 60));

        // Auto-fix if requested
        if ($this->option('fix') && !empty($allIssues)) {
            $this->newLine();
            if ($this->confirm('Do you want to add missing translations with placeholder text?', true)) {
                $this->fixTranslations($allIssues);
            }
        }

        return $missingKeys > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    /**
     * Get all keys from a translation array recursively.
     */
    private function getAllKeys(array $array, string $prefix = ''): array
    {
        $keys = [];

        foreach ($array as $key => $value) {
            $fullKey = $prefix ? "{$prefix}.{$key}" : $key;

            if (is_array($value)) {
                $keys = array_merge($keys, $this->getAllKeys($value, $fullKey));
            } else {
                $keys[$fullKey] = $value;
            }
        }

        return $keys;
    }

    /**
     * Fix missing translations by adding placeholder text.
     */
    private function fixTranslations(array $issues): void
    {
        $grouped = [];
        foreach ($issues as $issue) {
            $grouped[$issue['file']][$issue['locale']][] = $issue;
        }

        foreach ($grouped as $file => $locales) {
            foreach ($locales as $locale => $localeIssues) {
                $filePath = lang_path("{$locale}/{$file}.php");
                $content = File::get($filePath);
                
                // Parse existing translations
                $translations = require $filePath;
                
                foreach ($localeIssues as $issue) {
                    $keys = explode('.', $issue['key']);
                    $this->setNestedKey($translations, $keys, $this->getPlaceholderText($issue));
                }
                
                // Write back to file
                $this->writeTranslationFile($filePath, $translations);
                $this->info("  ✅ Updated: {$locale}/{$file}.php");
            }
        }
    }

    /**
     * Set a nested key in an array.
     */
    private function setNestedKey(array &$array, array $keys, $value): void
    {
        $key = array_shift($keys);
        
        if (empty($keys)) {
            $array[$key] = $value;
        } else {
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }
            $this->setNestedKey($array[$key], $keys, $value);
        }
    }

    /**
     * Get placeholder text for missing translation.
     */
    private function getPlaceholderText(array $issue): string
    {
        if ($issue['locale'] === 'ar' && $issue['value']) {
            // Return placeholder with original value
            return "[TODO: Translate] {$issue['value']}";
        }
        
        return "[TODO: Translate] {$issue['key']}";
    }

    /**
     * Write translation array to PHP file.
     */
    private function writeTranslationFile(string $filePath, array $translations): void
    {
        $content = "<?php\n\nreturn " . $this->arrayToString($translations) . ";\n";
        File::put($filePath, $content);
    }

    /**
     * Convert array to PHP code string.
     */
    private function arrayToString(array $array, int $indent = 0): string
    {
        $indentStr = str_repeat('    ', $indent);
        $lines = ['['];
        
        foreach ($array as $key => $value) {
            $keyStr = is_string($key) && preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $key) 
                ? $key 
                : var_export($key, true);
            
            if (is_array($value)) {
                $valueStr = $this->arrayToString($value, $indent + 1);
                $lines[] = "{$indentStr}    {$keyStr} => {$valueStr},";
            } else {
                $valueStr = var_export($value, true);
                $lines[] = "{$indentStr}    {$keyStr} => {$valueStr},";
            }
        }
        
        $lines[] = "{$indentStr}]";
        return implode("\n", $lines);
    }
}

