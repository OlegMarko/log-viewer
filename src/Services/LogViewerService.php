<?php

namespace Fixik\LogViewer\Services;

use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;
use SplFileObject;
use SplFileInfo;

class LogViewerService
{
    protected const LOG_ENTRY_PATTERN = '/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]\s+([\w.-]+)\.(\w+):(.*)/s';

    private Cache $cache;

    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    public function getDirectoryStructure(string $directory): array
    {
        $structure = [];

        foreach (File::directories($directory) as $folder) {
            $folderInfo = new SplFileInfo($folder);
            $structure[basename($folder)] = [
                'type'             => 'folder',
                'folder_structure' => $this->getDirectoryStructure($folder),
                'folder_perms'     => substr(sprintf('%o', $folderInfo->getPerms()), -4),
                'path'             => Str::after($folderInfo->getRealPath(), realpath(config('log-viewer.log_directory', storage_path('logs')))),
            ];
        }

        foreach (File::files($directory) as $file) {
            /** @var \SplFileInfo $file */
            if ($file->isReadable() && in_array($file->getExtension(), ['log', 'json', 'xml'])) {
                $structure[] = [
                    'type'  => 'file',
                    'name'  => $file->getFilename(),
                    'path'  => Str::after(dirname($file->getRealPath()), realpath(config('log-viewer.log_directory', storage_path('logs')))),
                    'size'  => $this->formatBytes($file->getSize()),
                    'perms' => substr(sprintf('%o', $file->getPerms()), -4)
                ];
            }
        }

        return $structure;
    }

    public function getLogContents(string $filePath, int $perPage = 50, int $page = 1): array
    {
        $entries = $this->lazyReadLogEntries($filePath)
            ->map(fn (string $entry) => $this->processLogEntry($entry))
            ->filter()->values()->reverse()->toArray();

        return [
            'entries' => $entries,
        ];
    }

    public function getLogSummary(string $filePath): array
    {
        $cacheKey = 'log-viewer:summary:' . md5($filePath);
        $lastModified = File::lastModified($filePath);

        return $this->cache->remember($cacheKey . ':' . $lastModified, now()->addMinutes(1), function () use ($filePath) {
            $counts = [
                'info' => 0, 'error' => 0, 'warning' => 0, 'critical' => 0,
                'debug' => 0, 'notice' => 0, 'alert' => 0, 'emergency' => 0,
                'other' => 0, 'total' => 0
            ];

            foreach ($this->lazyReadLogEntries($filePath) as $entry) {
                $counts['total']++;
                if (preg_match(self::LOG_ENTRY_PATTERN, $entry, $matches)) {
                    $level = strtolower($matches[3]);
                    if (isset($counts[$level])) {
                        $counts[$level]++;
                    } else {
                        $counts['other']++;
                    }
                } else {
                    $counts['other']++;
                }
            }

            return $counts;
        });
    }

    private function lazyReadLogEntries(string $filePath): LazyCollection
    {
        return LazyCollection::make(function () use ($filePath) {
            if (!File::exists($filePath) || !File::isReadable($filePath)) {
                return;
            }

            $file = new SplFileObject($filePath, 'r');
            $file->setFlags(SplFileObject::DROP_NEW_LINE | SplFileObject::READ_AHEAD | SplFileObject::SKIP_EMPTY);

            $currentEntry = '';
            foreach ($file as $line) {
                if (preg_match('/^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}]/', $line)) {
                    if ($currentEntry !== '') {
                        yield $currentEntry;
                    }
                    $currentEntry = $line;
                } else {
                    $currentEntry .= PHP_EOL . $line;
                }
            }

            if ($currentEntry !== '') {
                yield $currentEntry;
            }
        });
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

        return [
            'time'                => null,
            'env'                 => 'default',
            'type'                => 'other',
            'description'         => $entry,
            'short_description'   => Str::limit($entry, 120),
            'json'                => null,
            'payload_description' => $entry,
            'only_json'           => false,
        ];
    }

    private function isJson(?string $string): bool
    {
        json_decode($string);

        return json_last_error() === JSON_ERROR_NONE;
    }

    public function formatBytes(int $bytes, int $precision = 2): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf("%.{$precision}f %s", $bytes / (1024 ** $factor), $units[$factor]);
    }
}