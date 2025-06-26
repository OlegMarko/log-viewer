<?php

namespace Fixik\LogViewer\Controllers;

use Fixik\LogViewer\Services\LogViewerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class LogViewerController extends Controller
{
    private string $logDir;

    private LogViewerService $logViewer;

    public function __construct(LogViewerService $logViewer)
    {
        $this->logDir = config('log-viewer.log_directory', storage_path('logs'));
        $this->logViewer = $logViewer;
    }

    public function index(): View
    {
        if (!File::exists($this->logDir) || !File::isDirectory($this->logDir)) {
            $logStructure = [];
        } else {
            $logStructure = $this->logViewer->getDirectoryStructure($this->logDir);
        }

        return view('log-viewer::log-viewer.index', compact('logStructure'));
    }

    public function show(Request $request): View
    {
        $fullPath = $this->getValidatedAndSecurePath(
            $request->query('path', ''),
            $request->query('file')
        );

        if (!$fullPath) {
            abort(404, 'Log file not found or access denied. Ensure the path and file are valid and within the configured log directory.');
        }

        $fileName = basename($fullPath);
        $relativePath = Str::after(dirname($fullPath), realpath($this->logDir));
        $filePath = empty($relativePath) ? '/' : $relativePath;


        $fileSize = File::size($fullPath);
        $extension = pathinfo($fullPath, PATHINFO_EXTENSION);

        switch ($extension) {
            case 'xml':
                return view('log-viewer::log-viewer.show-data', [
                    'filePath' => $filePath,
                    'fileName' => $fileName,
                    'content' => File::get($fullPath),
                    'title' => "Viewing XML: $fileName"
                ]);

            case 'json':
                $jsonContent = File::get($fullPath);
                $jsonDecoded = json_decode($jsonContent, true);
                $prettyJson = json_encode($jsonDecoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                return view('log-viewer::log-viewer.show-data', [
                    'filePath' => $filePath,
                    'fileName' => $fileName,
                    'content' => $prettyJson,
                    'title' => "Viewing JSON: $fileName"
                ]);

            case 'log':
                return $this->renderLogFile($request, $filePath, $fileName, $fullPath);

            default:
                return $this->renderEmptyLog($filePath, $fileName);
        }
    }

    public function downloadFile(Request $request): BinaryFileResponse
    {
        $fullPath = $this->getValidatedAndSecurePath(
            $request->input('path'),
            $request->input('file')
        );
        if (!$fullPath) {
            abort(403, 'Access Denied: Invalid file path or access is not allowed.');
        }

        return response()->download($fullPath);
    }

    public function deleteFile(Request $request): RedirectResponse
    {
        $fullPath = $this->getValidatedAndSecurePath(
            $request->input('path'),
            $request->input('file')
        );
        if (!$fullPath) {
            abort(403, 'Access Denied: Invalid file path or access is not allowed.');
        }

        if (!File::exists($fullPath)) {
            return redirect()->route('log-viewer.index')->with('error', 'File not found: ' . basename($fullPath));
        }

        File::delete($fullPath);

        return redirect()->route('log-viewer.index')->with('success', 'File "' . basename($fullPath) . '" deleted successfully.');
    }

    public function downloadFullDirectory(Request $request): BinaryFileResponse
    {
        $directoryPath = $this->getValidatedAndSecurePath($request->input('directory'), null);
        if (!$directoryPath || !is_dir($directoryPath)) {
            abort(404, 'Directory not found or access denied. Ensure the directory is valid and within the configured log directory.');
        }

        $zipFileName = basename($directoryPath) . '.zip';
        $zipFilePath = storage_path('app/' . $zipFileName);

        $zip = new ZipArchive();
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Could not create ZIP file.');
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directoryPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            /** @var \SplFileInfo $file */
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = Str::after($filePath, $directoryPath . DIRECTORY_SEPARATOR);
                $zip->addFile($filePath, $relativePath);
            }
        }
        $zip->close();

        return response()->download($zipFilePath)->deleteFileAfterSend(true);
    }

    public function deleteDirectory(Request $request): RedirectResponse
    {
        $directoryPath = $this->getValidatedAndSecurePath($request->input('directory'), null);
        if (!$directoryPath || !is_dir($directoryPath)) {
            abort(404, 'Directory not found or access denied. Ensure the directory is valid and within the configured log directory.');
        }

        if (realpath($directoryPath) === realpath($this->logDir)) {
            return redirect()->route('log-viewer.index')->with('error', 'Cannot delete the root log directory.');
        }

        if (!File::exists($directoryPath)) {
            return redirect()->route('log-viewer.index')->with('error', 'Directory not found: ' . basename($directoryPath));
        }

        File::deleteDirectory($directoryPath);

        return redirect()->route('log-viewer.index')->with('success', 'Directory "' . basename($directoryPath) . '" deleted successfully.');
    }

    private function getValidatedAndSecurePath(?string $path, ?string $file = null): ?string
    {
        $rules = [
            'path' => ['nullable', 'string', 'max:500', 'regex:/^[a-zA-Z0-9\/_\-.:]*$/'],
        ];

        $validatorData = ['path' => $path];

        if ($file !== null) {
            $rules['file'] = ['required', 'string', 'max:255', 'regex:/^[\w\d\-_\.]+\.(log|json|xml)$/i'];
            $validatorData['file'] = $file;
        } else {
            $rules['path'][] = 'required';
        }

        $validator = Validator::make($validatorData, $rules);
        if ($validator->fails()) {
            return null;
        }

        $realLogDir = realpath($this->logDir) ?: $this->logDir;

        $fullUnsafePath = rtrim($realLogDir, DIRECTORY_SEPARATOR);
        if (!empty($path)) {
            $normalizedPathSegment = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, trim($path, '/\\'));
            $fullUnsafePath .= DIRECTORY_SEPARATOR . $normalizedPathSegment;
        }

        if ($file !== null) {
            $fullUnsafePath .= DIRECTORY_SEPARATOR . $file;
        }

        $realPath = realpath($fullUnsafePath);

        if ($realPath === false || !Str::startsWith($realPath, $realLogDir)) {
            return null;
        }

        if ($file !== null && !is_file($realPath)) {
            return null;
        }

        if ($file === null && !is_dir($realPath)) {
            return null;
        }

        return $realPath;
    }

    private function renderLogFile(Request $request, string $filePath, string $fileName, string $fullPath): View
    {
        $summary = $this->logViewer->getLogSummary($fullPath);
        $entries = $this->logViewer->getLogContents($fullPath);

        return view('log-viewer::log-viewer.show', [
            'filePath'    => $filePath,
            'fileName'    => $fileName,
            'entries'     => $entries['entries'],
            'summary'     => $summary,
            'title'       => "Viewing Log: $fileName",
        ]);
    }

    private function renderTooLargeFile(string $filePath, string $fileName, int $fileSize): View
    {
        return view('log-viewer::log-viewer.show-too-large', [
            'filePath' => $filePath,
            'fileName' => $fileName,
            'fileSize' => $this->logViewer->formatBytes($fileSize),
            'title'    => "File Too Large: $fileName"
        ]);
    }

    private function renderEmptyLog(string $filePath, string $fileName): View
    {
        return view('log-viewer::log-viewer.show-empty', [
            'filePath' => $filePath,
            'fileName' => $fileName,
            'title'    => "Empty/Unreadable Log: $fileName"
        ]);
    }
}