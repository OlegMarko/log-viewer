<?php

namespace Fixik\LogViewer\Controllers;

use Fixik\LogViewer\Services\LogViewerService;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
        $logStructure = $this->logViewer->getDirectoryStructure($this->logDir);

        return view('log-viewer::log-viewer.index', compact('logStructure'));
    }

    /**
     * @throws FileNotFoundException
     */
    public function show(Request $request): View
    {
        $fileName = $request->query('file');
        $filePath = $request->query('path');
        $fullPath = config('log-viewer.log_directory', storage_path('logs')) . $filePath . $fileName;

        if (!File::exists($fullPath)) {
            abort(404, 'Log file not found.');
        }

        if (pathinfo($fullPath, PATHINFO_EXTENSION) === 'xml') {
            $xmlContent = File::get($fullPath);
            return view('log-viewer::log-viewer.show-data', [
                'filePath' => $filePath,
                'fileName' => $fileName,
                'xmlContent' => $xmlContent
            ]);
        }

        if (pathinfo($fullPath, PATHINFO_EXTENSION) === 'json') {
            $jsonContent = File::get($fullPath);
            $jsonContentDecoded = json_decode($jsonContent, true);
            $jsonContentEncoded = json_encode($jsonContentDecoded, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);

            return view('log-viewer::log-viewer.show-data', [
                'filePath' => $filePath,
                'fileName' => $fileName,
                'xmlContent' => $jsonContentEncoded
            ]);
        }

        if (pathinfo($fullPath, PATHINFO_EXTENSION) === 'log') {
            $logData = $this->logViewer->getLogContents($fullPath);

            return view('log-viewer::log-viewer.show', [
                'filePath'    => $filePath,
                'fileName'    => $fileName,
                'logContents' => $logData['entries'],
                'counts'      => $logData['counts']
            ]);
        }

        return view('log-viewer::log-viewer.show', [
            'filePath'    => $filePath,
            'fileName'    => $fileName,
            'logContents' => [],
            'counts'      => []
        ]);
    }

    public function downloadFile(Request $request): BinaryFileResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|string',
            'path' => 'required|string',
        ]);
        if ($validator->fails()) {
            return redirect()->back();
        }

        $fileName = $request->input('file');
        $filePath = $request->input('path');
        $fullPath = config('log-viewer.log_directory', storage_path('logs')) . $filePath . $fileName;

        if (!File::exists($fullPath) && in_array(File::extension($fullPath), ['log', 'xml', 'json'])) {
            abort(404, 'Log file not found.');
        }

        return response()->download($fullPath);
    }

    public function deleteFile(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|string',
            'path' => 'required|string',
        ]);
        if ($validator->fails()) {
            return redirect()->back();
        }

        $fileName = $request->input('file');
        $filePath = $request->input('path');
        $fullPath = config('log-viewer.log_directory', storage_path('logs')) . $filePath . $fileName;

        if (!File::exists($fullPath) && in_array(File::extension($fullPath), ['log', 'xml', 'json'])) {
            abort(404, 'Log file not found.');
        }

        File::delete($fullPath);

        return redirect()->route('log-viewer.index');
    }

    public function downloadFullDirectory(): BinaryFileResponse
    {
        $directory = request()->input('directory');
        $directoryPath = $this->logDir . '/' . $directory;
        if (!is_dir($directoryPath)) {
            abort(404, 'Directory not found.');
        }

        $zipFileName = $directory . '.zip';
        $zipFilePath = storage_path($zipFileName);

        $zip = new \ZipArchive();

        if ($zip->open($zipFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($directoryPath),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $name => $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($directoryPath) + 1);

                    $zip->addFile($filePath, $relativePath);
                }
            }

            $zip->close();
        } else {
            abort(500, 'Could not create ZIP file.');
        }

        return response()->download($zipFilePath)->deleteFileAfterSend(true);
    }

}
