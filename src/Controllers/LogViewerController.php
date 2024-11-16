<?php

namespace Fixik\LogViewer\Controllers;

use Fixik\LogViewer\Services\LogViewerService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;

class LogViewerController extends Controller
{
    private string $logDir;
    private LogViewerService $logViewer;

    public function __construct(LogViewerService $logViewer)
    {
        $this->logDir = config('log-viewer.log_directory', storage_path('logs'));
        $this->logViewer = $logViewer;
    }

    public function index()
    {
        $logStructure = $this->logViewer->getDirectoryStructure($this->logDir);

        return view('log-viewer::log-viewer.index', compact('logStructure'));
    }

    public function show($filename, Request $request)
    {
        $filePath = $request->query('path');

        if (!File::exists($filePath)) {
            abort(404, 'Log file not found.');
        }

        if (pathinfo($filePath, PATHINFO_EXTENSION) === 'xml') {
            $xmlContent = File::get($filePath);
            return view('log-viewer::log-viewer.show-data', [
                'filePath' => $filePath,
                'filename' => $filename,
                'xmlContent' => $xmlContent
            ]);
        }

        if (pathinfo($filePath, PATHINFO_EXTENSION) === 'json') {
            $jsonContent = File::get($filePath);
            $jsonContentDecoded = json_decode($jsonContent, true);
            $jsonContentEncoded = json_encode($jsonContentDecoded, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);

            return view('log-viewer::log-viewer.show-data', [
                'filePath' => $filePath,
                'filename' => $filename,
                'xmlContent' => $jsonContentEncoded
            ]);
        }

        if (pathinfo($filePath, PATHINFO_EXTENSION) === 'log') {
            $logData = $this->logViewer->getLogContents($filePath);

            return view('log-viewer::log-viewer.show', [
                'filename' => $filename,
                'logContents' => $logData['entries'],
                'counts' => $logData['counts'],
                'filePath' => $filePath
            ]);
        }

        return view('errors.404', []);
    }
}
