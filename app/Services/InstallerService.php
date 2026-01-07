<?php

namespace App\Services;


use App\Libraries\AppLibrary;
use Dipokhalder\EnvEditor\EnvEditor;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class InstallerService
{
    public function siteSetup(Request $request): void
    {
        $envService = new EnvEditor();
        $envService->addData([
            'APP_NAME' => $request->app_name,
            'APP_URL'  => rtrim($request->app_url, '/')
        ]);
        Artisan::call('config:clear');
    }

    public function databaseSetup(Request $request): bool
    {
        $connection = $this->checkDatabaseConnection($request);
        if ($connection) {
            $envService = new EnvEditor();
            $envService->addData([
                'DB_CONNECTION' => 'mysql',
                'DB_HOST'     => $request->database_host,
                'DB_PORT'     => $request->database_port,
                'DB_DATABASE' => $request->database_name,
                'DB_USERNAME' => $request->database_username,
                'DB_PASSWORD' => $request->database_password,
            ]);

            DB::purge();
            Artisan::call('config:clear');
            
            // Manually drop all tables and create migrations table
            $tables = DB::select('SHOW TABLES');
            Schema::disableForeignKeyConstraints();
            foreach ($tables as $table) {
                $tableName = array_values((array)$table)[0];
                Schema::dropIfExists($tableName);
            }
            Schema::enableForeignKeyConstraints();
            
            // Create migrations table manually
            Schema::create('migrations', function (Blueprint $table) {
                $table->increments('id');
                $table->string('migration');
                $table->integer('batch');
            });
            
            // Run migrations - migrations table now exists
            Artisan::call('migrate', ['--force' => true]);
            if(Artisan::call('db:seed', ['--force' => true])) {
                Artisan::call('config:clear');
            }
            return true;
        }
        return false;
    }

    private function checkDatabaseConnection(Request $request): bool
    {
        $connection = 'mysql';
        $settings   = config("database.connections.$connection");
        config([
            'database' => [
                'default'     => $connection,
                'connections' => [
                    $connection => array_merge($settings, [
                        'driver'   => $connection,
                        'host'     => $request->input('database_host'),
                        'port'     => $request->input('database_port'),
                        'database' => $request->input('database_name'),
                        'username' => $request->input('database_username'),
                        'password' => $request->input('database_password'),
                    ]),
                ],
            ],
        ]);

        DB::purge();

        try {
            DB::connection()->getPdo();
            return true;
        } catch (Exception $e) {
            throw new Exception('Database connection failed: ' . $e->getMessage());
        }
    }

    public function licenseCodeChecker($array)
    {
        try {
            $payload = [
                'license_code' => $array['license_key'],
                'product_id'   => config('product.itemId'),
                'domain'       => AppLibrary::domain(url('')),
                'purpose'      => 'install',
                'version'      => config('product.version')
            ];
            if (isset($array['purpose'])) {
                $payload['purpose'] = $array['purpose'];
            }
            $apiUrl   = config('product.licenseCodeCheckerUrl');
            $response = Http::post($apiUrl . '/api/check-installer-license', $payload);
            return AppLibrary::licenseApiResponse($response);
        } catch (\Exception $exception) {
            return (object)[
                'status'  => false,
                'message' => $exception->getMessage()
            ];
        }
    }

    public function finalSetup(): void
    {
        $installedLogFile = storage_path('installed');
        $dateStamp        = date('Y-m-d h:i:s A');
        if (!file_exists($installedLogFile)) {
            $message = trans('installer.installed.success_log_message') . $dateStamp . "\n";
            file_put_contents($installedLogFile, $message);
        } else {
            $message = trans('installer.installed.update_log_message') . $dateStamp;
            file_put_contents($installedLogFile, $message . PHP_EOL, FILE_APPEND | LOCK_EX);
        }

        try {
        Artisan::call('storage:link', ['--force' => true]);
        } catch (Exception $e) {
            // Storage link might already exist, ignore
        }
        
        $envService = new EnvEditor();
        $envService->addData([
            'APP_ENV'   => 'local',
            'APP_DEBUG' => 'true'
        ]);
        Artisan::call('config:clear');
    }
}

