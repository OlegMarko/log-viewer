<?php

namespace Fixik\LogViewer\Services;

use Illuminate\Support\Facades\File;

class LogViewerService
{
    public function getDirectoryStructure($directory): array
    {
        $structure = [];
        foreach (File::directories($directory) as $folder) {
            $structure[basename($folder)] = $this->getDirectoryStructure($folder);
        }
        foreach (File::files($directory) as $file) {
            if ($file->isReadable() && in_array($file->getExtension(), ['log', 'json', 'xml'])) {
                $structure[] = [
                    'name' => $file->getFilename(),
                    'path' => $file->getRealPath(),
                    'size' => $this->formatBytes($file->getSize())
                ];
            }
        }

        return $structure;
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        // For 1-100 bytes, return as "B".
        if ($bytes <= 100) {
            return $bytes . ' B';
        }

        // For 101-1024 bytes, format as "0.* KB".
        if ($bytes <= 1024) {
            return round($bytes / 1024, $precision) . ' KB';
        }

        // Beyond 1024, use standard KB, MB, GB, etc.
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $pow = min(floor(log($bytes, 1024)), count($units) - 1);
        $size = $bytes / pow(1024, $pow);

        return round($size, $precision) . ' ' . $units[$pow];
    }

    public function getLogContents($filePath): array
    {
        $entries = [];
        $counts = [
            'info' => 0,
            'error' => 0,
            'warning' => 0,
            'critical' => 0,
            'debug' => 0,
            'other' => 0
        ];

        $fp = fopen($filePath, "r");
        $currentEntry = '';

        if ($fp) {
            while (!feof($fp)) {
                $line = fgets($fp);

                if (preg_match('/^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}]/', $line)) {
                    if (!empty($currentEntry)) {
                        $this->processLogEntry($currentEntry, $entries, $counts);
                    }
                    $currentEntry = $line;
                } else {
                    $currentEntry .= $line;
                }
            }

            if (!empty($currentEntry)) {
                $this->processLogEntry($currentEntry, $entries, $counts);
            }

            fclose($fp);
        }

        return [
            'entries' => $entries,
            'counts' => $counts
        ];
    }

    private function processLogEntry($entry, &$entries, &$counts): void
    {
        if (preg_match('/^\[(.*?)]\s(\w+)\.(\w+):\s(.*)/s', $entry, $matches)) {
            $type = strtolower($matches[3]);

            if (isset($counts[$type])) {
                $counts[$type]++;
            } else {
                $counts['other']++;
            }

            $description = $matches[4];
            $jsonPart = null;
            $payloadDescription = null;
            $onlyJson = false;

            if (preg_match('/^(.*?)\s*?\s(\{.*\})$/', $description, $descMatches)) {
                $jsonPart = $descMatches[2];
                $payloadDescription = $descMatches[1];
            } elseif ($this->isJson($description)) {
                $onlyJson = true;
                $jsonPart = $description;
            }

            $jsonDecoded = $jsonPart ? json_decode($jsonPart, true) : null;
            $prettyJson = $jsonDecoded ?
                json_encode($jsonDecoded, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) :
                null;

            $entries[] = [
                'time' => $matches[1] ?? null,
                'env' => $matches[2] ?? null,
                'type' => $type,
                'description' => $description,
                'payload_description' => $payloadDescription,
                'json' => $prettyJson,
                'only_json' => $onlyJson
            ];
        }
    }

    private function isJson(?string $string): bool
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}