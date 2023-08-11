# Integrations

Adding new integration steps:
1. Add new folder in the Integrations.
2. Add new class `Integrations/<Integration_Name>.php`. This holds mappings for API to our components.
3. Add new class `Integrations/<Integration_Name>Client.php`. This holds all data connections to the API.
4. Add new class `Integrations/Settings<Integration_Name>.php`. This holds all settings, global/local.
5. Populate new key with the filters in the `Hooks/Filters.php::ALL`.
6. Add new class `Rest/Routes/FormSubmit<Integration_Name>Route.php`.
7. Add new block to for the specific integration in the `Blocks/custom` folder.
8. Add new allowed block in the manifest.json of the `Blocks/custom/form-selector/manifest.json` block.
9. Add new API key global variable in the `Hooks/Variables.php`.
10. Add labels for new integration `Labels/Labels.php`.
