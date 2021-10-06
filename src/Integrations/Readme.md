# Integrations

Adding new integration steps:
1. Add new folder in the Integrations
2. Add new top-level class in integration used for mapping field in the integration and our components.
3. Add client class where you mak function to connect to the integration.
4. Add Setting class where you setup global and form specific options.
5. If there is a global option that needs global variable add it to the variables namespace.
6. Add new integration internal keys to the Integrations.php file.
7. Add new switch and callback to the FormSubmitRoute.php class.
8. Add new block to for the specific integration.
9. Add new Allowed block in the manifest.json in the form-selector block.
10. Add transient cache to the cache busting list in the SettingsCache.php.
