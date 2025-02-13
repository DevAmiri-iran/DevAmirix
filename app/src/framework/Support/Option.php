<?php

namespace App\Support;

class Option {
    private mixed $settingsFile;
    private ?array $settings;
    private mixed $cacheEnabled;

    /**
     * Initializes the Option instance.
     *
     * @param string $filePath The path to the settings file.
     * @param bool $cacheEnabled Whether to enable caching of the settings.
     */
    public function __construct(string $filePath = 'settings.json', bool $cacheEnabled = true) {
        $this->settingsFile = storage_path($filePath);
        $this->cacheEnabled = $cacheEnabled;
        $this->settings = null;

        $this->loadSettings();
    }

    /**
     * Loads the settings from the settings file.
     */
    private function loadSettings(): void
    {
        if ($this->settings === null || !$this->cacheEnabled) {
            if (!file_exists($this->settingsFile)) {
                file_put_contents($this->settingsFile, json_encode([]));
            }
            $json = file_get_contents($this->settingsFile);
            $this->settings = json_decode($json, true);
        }
    }

    /**
     * Saves the current settings to the settings file.
     */
    private function saveSettings(): void
    {
        if ($this->settings !== null) {
            $json = json_encode($this->settings, JSON_PRETTY_PRINT);
            file_put_contents($this->settingsFile, $json);
        }
    }

    /**
     * Retrieves a setting value by its name.
     *
     * @param string $optionName The name of the setting.
     * @param mixed|null $defaultValue The default value to return if the setting is not found.
     * @return mixed The value of the setting or the default value if not found.
     */
    public function get(string $optionName, mixed $defaultValue = null): mixed
    {
        return $this->settings[$optionName] ?? $defaultValue;
    }

    /**
     * Sets a setting value.
     *
     * @param string $optionName The name of the setting.
     * @param mixed $value The value to set.
     */
    public function set(string $optionName, mixed $value): void
    {
        $this->settings[$optionName] = $value;
        $this->saveSettings();
    }

    /**
     * Deletes a setting by its name.
     *
     * @param string $optionName The name of the setting to delete.
     */
    public function delete(string $optionName): void
    {
        if (isset($this->settings[$optionName])) {
            unset($this->settings[$optionName]);
            $this->saveSettings();
        }
    }

    /**
     * Retrieves all settings.
     *
     * @return array|null An array of all settings or null if none.
     */
    public function getAll(): ?array
    {
        return $this->settings;
    }

    /**
     * Clears all settings.
     */
    public function clear(): void
    {
        $this->settings = [];
        $this->saveSettings();
    }

    /**
     * Checks if a setting exists by its name.
     *
     * @param string $optionName The name of the setting.
     * @return bool True if the setting exists, otherwise false.
     */
    public function exists(string $optionName): bool
    {
        return isset($this->settings[$optionName]);
    }

    /**
     * Sets default values for settings if they do not already exist.
     *
     * @param array $defaults An associative array of default settings.
     */
    public function setDefaults(array $defaults): void
    {
        foreach ($defaults as $key => $value) {
            if (!isset($this->settings[$key])) {
                $this->settings[$key] = $value;
            }
        }
        $this->saveSettings();
    }

    /**
     * Sets a new settings file and reloads the settings.
     *
     * @param string $filePath The path to the new settings file.
     */
    public function setSettingsFile(string $filePath): void
    {
        $this->settingsFile = $filePath;
        $this->settings = null;
        $this->loadSettings();
    }
}