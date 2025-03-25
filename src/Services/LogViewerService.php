<?php

namespace Fixik\LogViewer\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;
use SplFileObject;
use SplFileInfo;

class LogViewerService
{
    public function getDirectoryStructure(string $directory): array
    {
        $structure = [];

        foreach (File::directories($directory) as $folder) {
            $folderInfo = new SplFileInfo($folder);
            $structure[basename($folder)] = [
                'folder_structure' => $this->getDirectoryStructure($folder),
                'folder_perms'     => substr(sprintf('%o', $folderInfo->getPerms()), -4),
            ];
        }

        foreach (File::files($directory) as $file) {
            if ($file->isReadable() && in_array($file->getExtension(), ['log', 'json', 'xml'])) {
                $structure[] = [
                    'name'  => $file->getFilename(),
                    'path'  => str_replace($file->getFilename(), '', str_replace(config('log-viewer.log_directory', storage_path('logs')), '', $file->getRealPath())),
                    'size'  => $this->formatBytes($file->getSize()),
                    'perms' => substr(sprintf('%o', $file->getPerms()), -4)
                ];
            }
        }

        return $structure;
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $factor = floor(log($bytes, 1024));
        return sprintf("%.2f %s", $bytes / pow(1024, $factor), $units[$factor]);
    }

    public function getLogContents(string $filePath): array
    {
        $counts = [
            'info' => 0,
            'error' => 0,
            'warning' => 0,
            'critical' => 0,
            'debug' => 0,
            'other' => 0
        ];

        $file = new SplFileObject($filePath, "r");

        // Collect entries and update counts
        $entries = LazyCollection::make(function () use ($file, &$counts) {
            $currentEntry = '';
            foreach ($file as $line) {
                if (preg_match('/^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}]/', $line)) {
                    if (!empty($currentEntry)) {
                        $entryData = $this->processLogEntry($currentEntry);
                        if (!empty($entryData)) {
                            $counts[$entryData['type']] = ($counts[$entryData['type']] ?? 0) + 1;
                            yield $entryData;
                        }
                    }
                    $currentEntry = $line;
                } else {
                    $currentEntry .= $line;
                }
            }
            if (!empty($currentEntry)) {
                $entryData = $this->processLogEntry($currentEntry);
                if (!empty($entryData)) {
                    $counts[$entryData['type']] = ($counts[$entryData['type']] ?? 0) + 1;
                    yield $entryData;
                }
            }
        })->toArray(); // <--- This forces collection execution before returning

        return [
            'entries' => $entries,  // Now an array
            'counts' => $counts     // Correct counts
        ];
    }

    private function processLogEntry(string $entry): array
    {
        if (preg_match('/^\[(.*?)]\s(\w+)\.(\w+):\s(.*)/s', $entry, $matches)) {
            $type = strtolower($matches[3]);
            $description = trim($matches[4]);
            $jsonPart = null;
            $payloadDescription = null;
            $onlyJson = false;

            if (preg_match('/\{.*}/', $description, $descMatches)) {
                $jsonPart = trim($descMatches[0]);
                $payloadDescription = trim(str_replace($jsonPart, '', $description));
            } elseif ($this->isJson($description)) {
                $onlyJson = true;
                $jsonPart = $description;
            }

            return [
                'time'                => $matches[1] ?? null,
                'env'                 => $matches[2] ?? null,
                'type'                => $type,
                'description'         => $description,
                'short_description'   => Str::limit($description, 80),
                'payload_description' => $payloadDescription,
                'json'                => $jsonPart ? json_encode(json_decode($jsonPart, true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
                'only_json'           => $onlyJson,
            ];
        }

        return [];
    }

    private function isJson(?string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}