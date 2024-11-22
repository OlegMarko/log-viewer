# Laravel Log Viewer Package

[![Latest Stable Version](https://poser.pugx.org/fixik/log-viewer/v/stable)](https://packagist.org/packages/fixik/log-viewer)
[![Total Downloads](https://poser.pugx.org/fixik/log-viewer/downloads)](https://packagist.org/packages/fixik/log-viewer)
[![License](https://poser.pugx.org/fixik/log-viewer/license)](https://packagist.org/packages/fixik/log-viewer)

A Laravel package for viewing and analyzing application log files with an easy-to-navigate interface. This package provides insights into log contents, including log type breakdown, and offers a convenient way to browse through the log directory.

## Features
- **Log Directory Navigation**: Browse through folders and view individual log files.
- **Log Analysis**: Summarize log entries by type (info, error, warning, etc.).
- **Detailed Log Viewer**: Display log contents in a structured table format, with modal support for full entry viewing.

## Requirements

- **PHP**: >= 7.4
- **Laravel**: 7.x or 11.x

## Installation

1. **Install via Composer**:

   ```bash
   composer require fixik/log-viewer
   ```

2. **Publish Configuration and Views** (if required):

   ```bash
   php artisan vendor:publish --tag=log-viewer-config
   php artisan vendor:publish --tag=log-viewer-views
   ```

## Configuration

The default configuration allows you to specify:
- The log directory path (default is `storage/logs`).
- Customization for pagination and log display limits.

To customize, open the published configuration file located in `config/log-viewer.php`.

## Usage

### Route Setup

Add the following route to access the Log Viewer UI:

```php
use Fixik\LogViewer\Controllers\LogViewerController;

Route::prefix('logs')->group(function () {
    Route::get('/', [LogViewerController::class, 'index'])->name('logs.index');
    Route::get('/view/{filename}', [LogViewerController::class, 'show'])->name('logs.show');
});
```

### Display Logs

1. **Log File List**: Navigate to `/logs` to view a structured list of available log files.
2. **View Log Details**: Click on a log file to see its contents, summaries, and detailed entries.

### Example of `LogViewerController`

This package provides a pre-built controller for viewing logs. Here’s how it processes log files:
- **`index()`**: Retrieves the directory structure of the log directory.
- **`show()`**: Displays details for a specific log file, including a breakdown of log types.

## Views

The package includes customizable views:
- **Log List View**: Displays folders and log files.
- **Log Details View**: Displays log file contents and includes a modal for individual log entry details.

To customize the view files, edit the files in `resources/views/vendor/log-viewer`.

### Example Blade Usage for XML Logs

To display XML logs, use the following snippet in your Blade templates:

```blade
<pre>{{ $xmlContent }}</pre>
```

## Customization

### Changing Log Directory

Specify a different log directory, route or middleware in the config file:

```php
'log_directory' => storage_path('custom-logs'),
'routes' => [
     'prefix' => env('LOG_VIEWER_ROUTE_PREFIX', 'log-viewer'),
     'middleware' => env('LOG_VIEWER_ROUTE_MIDDLEWARE', 'web'),
 ]
```

## Support

If you encounter issues, please file an issue in the GitHub repository or submit a pull request.

## License

This package is open-sourced software licensed under the [MIT license](https://opensource.org/license/MIT).

---