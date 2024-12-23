<?php


namespace Marvel\Database\Repositories;

use Carbon\Carbon;
use Exception;
use Marvel\Database\Models\Settings;

class SettingsRepository extends BaseRepository
{
    /**
     * Configure the Model
     **/
    public function model()
    {
        return Settings::class;
    }

    public function getApplicationSettings(): array
    {
        $appData = $this->getAppSettingsData();

        return [
            'app_settings' => $appData,
        ];
    }

    private function getAppSettingsData(): array
    {
        try {
            $config = getConfig();
            if (!is_array($config)) {
                throw new Exception('Invalid configuration data');
            }

            $apiData = $config;
            $last_checking_time = $config['last_checking_time'] ?? Carbon::now();
            $lastCheckingTimeDifferenceFromNow = Carbon::parse($last_checking_time)->diffInMinutes(Carbon::now());

            if ($lastCheckingTimeDifferenceFromNow > 20) {
                $apiData = getConfigFromApi();
                if (!is_array($apiData)) {
                    throw new Exception('Invalid API data');
                }
            };

            $isValidated = $apiData['trust'] ?? true;

            $appData = array_merge($apiData, [
                'last_checking_time' => Carbon::now(),
                'trust' => $apiData['trust'] ?? true,
            ]);

            setConfig($appData);

            return [
                'last_checking_time' => Carbon::now(),
                'trust' => $isValidated,
            ];
        } catch (Exception $e) {
            // Log the exception or handle it as needed
            return [
                'last_checking_time' => Carbon::now(),
                'trust' => false,
            ];
        }
    }
}

